<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Pengaman duplikasi presensi:
 * - unique index (nik, tgl_presensi) di tabel presensis
 * - lockForUpdate saat membaca baris presensi hari ini
 * - penanganan QueryException duplicate key di prosesMasuk()
 *
 * Skenario aslinya: tombol absen di-tap berulang / retry jaringan → beberapa
 * POST /presensi/store paralel → sebelumnya semuanya INSERT → baris dobel.
 */
class PresensiDuplikatTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-30';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'DUP001',
            'nama_lengkap' => 'Karyawan Duplikat',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234000099',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ======================================================================
    // Idempotensi presensi masuk
    // ======================================================================

    public function test_store_masuk_kedua_tidak_membuat_baris_kedua(): void
    {
        $this->setTime('08:00:00');
        $this->mockFoto();

        $pertama = $this->prosesPresensi();
        $this->assertTrue($pertama['success'], json_encode($pertama));
        $this->assertSame('in', $pertama['type']);
        $this->assertSame(1, $this->jumlahPresensi());

        // Tap ganda / retry: request kedua pada hari & jam yang sama.
        $kedua = $this->prosesPresensi();

        $this->assertFalse($kedua['success'], json_encode($kedua));
        $this->assertSame(1, $this->jumlahPresensi());
    }

    public function test_store_masuk_kedua_setelah_baris_terbaca_ditolak_sebagai_pulang(): void
    {
        // Baris dengan format tanggal persis seperti yang dibaca query MySQL
        // (tanpa cast) sehingga jalur "$cek ketemu" ikut teruji.
        $this->sebarisPresensi('08:00:00');
        $this->setTime('08:10:00');
        $this->mockFoto();

        $kedua = $this->prosesPresensi();

        $this->assertFalse($kedua['success'], json_encode($kedua));
        $this->assertSame('out', $kedua['type']);
        $this->assertSame(1, $this->jumlahPresensi());
    }

    // ======================================================================
    // Unique index (nik, tgl_presensi)
    // ======================================================================

    public function test_unique_index_mencegah_baris_presensi_ganda(): void
    {
        $this->sebarisPresensi('08:00:00');

        $this->expectException(QueryException::class);

        DB::table('presensis')->insert($this->barisUntukInsert('08:05:00'));
    }

    // ======================================================================
    // Pengaman terakhir saat race tetap terjadi (insert kedua diblokir unique)
    // ======================================================================

    public function test_insert_paralel_kedua_ditangani_sebagai_sudah_presensi(): void
    {
        // Simulasikan race: baris sudah terlanjur dibuat request lain, format
        // tanggal sama persis seperti yang ditulis Eloquent (Y-m-d 00:00:00) —
        // di MySQL keduanya dinormalkan jadi DATE sehingga unique langsung aktif.
        DB::table('presensis')->insert(array_merge($this->barisUntukInsert('08:00:00'), [
            'tgl_presensi' => self::HARI_INI.' 00:00:00',
        ]));

        $service = app(PresensiService::class);
        $metode = new ReflectionMethod(PresensiService::class, 'prosesMasuk');
        $metode->setAccessible(true);

        $result = $metode->invoke($service, $this->karyawan->nik, self::HARI_INI, '08:00:00', 'foto-baru.webp', [
            'lokasi' => '-6.2001,106.8000',
            'akurasi' => 10.0,
            'jarak' => 5,
            'fix' => 5,
            'durasi' => 10000,
            'ip' => '127.0.0.1',
        ], 0);

        $this->assertFalse($result['success'], json_encode($result));
        $this->assertSame('Anda sudah melakukan presensi masuk!', $result['message']);
        $this->assertSame('in', $result['type']);
        $this->assertSame(1, $this->jumlahPresensi());
    }

    // ======================================================================
    // Helper
    // ======================================================================

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function mockFoto(): void
    {
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });
    }

    private function prosesPresensi(): array
    {
        $this->actingAs($this->karyawan, 'karyawan');

        $request = Request::create('/presensi/store', 'POST', [
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => '-6.2001,106.8000',
            'akurasi' => 10,
            'fix' => 5,
            'durasi_ms' => 10000,
        ]);

        return app(PresensiService::class)->processPresensi($request);
    }

    /**
     * Insert 1 baris presensi hari ini (format tanggal mentah, tanpa cast model).
     */
    private function sebarisPresensi(string $jamIn): void
    {
        DB::table('presensis')->insert($this->barisUntukInsert($jamIn));
    }

    /**
     * @return array<string, mixed>
     */
    private function barisUntukInsert(string $jamIn): array
    {
        return [
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => $jamIn,
            'foto_in' => 'foto.webp',
            'lokasi_in' => '-6.2001,106.8000',
            'terlambat' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function jumlahPresensi(): int
    {
        // Cast 'date' pada model membuat nilai tersimpan sebagai 'Y-m-d 00:00:00'
        // di SQLite, jadi pencocokan pakai LIKE agar konsisten dengan MySQL.
        return DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->count();
    }
}
