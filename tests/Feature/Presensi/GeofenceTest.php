<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitlokasi;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeofenceTest extends TestCase
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
            'radius_meter' => 100,
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'GEO001',
            'nama_lengkap' => 'Karyawan Geofence',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234000001',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ======================================================================
    // Presensi masuk
    // ======================================================================

    public function test_masuk_dalam_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame('in', $result['type']);
        $this->assertTrue($this->adaPresensi());
    }

    public function test_masuk_luar_radius_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('-6.2020,106.8000');

        $this->assertFalse($result['success']);
        $this->assertSame('in', $result['type']);
        $this->assertStringContainsString('berada', $result['message']);
        $this->assertStringContainsString('radius 100 m', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_di_antara_dua_lokasi_ditolak(): void
    {
        $this->titikLokasi('Kantor A', -6.2000, 106.8000);
        $this->titikLokasi('Kantor B', -6.2100, 106.8100);
        $this->setTime('08:00:00');

        // Titik tengah antara A dan B, jauh dari radius keduanya.
        $result = $this->prosesPresensi('-6.2050,106.8050');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_di_lokasi_kedua_berhasil(): void
    {
        $this->titikLokasi('Kantor A', -6.2000, 106.8000);
        $this->titikLokasi('Kantor B', -6.2100, 106.8100);
        $this->setTime('08:00:00');
        $this->mockFoto();

        // Di dekat lokasi B (bukan A).
        $result = $this->prosesPresensi('-6.2101,106.8100');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    public function test_masuk_unit_tanpa_lokasi_tetap_berhasil(): void
    {
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    // ======================================================================
    // Cabang WFH — bebas lokasi
    // ======================================================================

    public function test_masuk_wfh_approved_luar_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->wfhApprovedHariIni();
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    // ======================================================================
    // Presensi pulang
    // ======================================================================

    public function test_pulang_luar_radius_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->presensiHariIni();
        $this->setTime('17:30:00');

        $result = $this->prosesPresensi('-6.2020,106.8000');

        $this->assertFalse($result['success']);
        $this->assertSame('out', $result['type']);
        $this->assertStringContainsString('radius 100 m', $result['message']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertNull($presensi->jam_out);
    }

    public function test_pulang_dalam_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->presensiHariIni();
        $this->setTime('17:30:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success']);
        $this->assertSame('out', $result['type']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertSame('17:30:00', $presensi->jam_out);
    }

    // ======================================================================
    // Lokasi tidak valid
    // ======================================================================

    public function test_lokasi_kosong_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Lokasi tidak terdeteksi', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_lokasi_tidak_valid_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('bukan-koordinat');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->adaPresensi());
    }

    // ======================================================================
    // Helper
    // ======================================================================

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function titikLokasi(string $nama, float $lat, float $lng): void
    {
        Unitlokasi::create([
            'unit_id' => $this->unit->id,
            'nama_lokasi' => $nama,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    private function presensiHariIni(): void
    {
        DB::table('presensis')->insert([
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => '08:00:00',
            'jam_out' => null,
            'foto_in' => 'foto-in.webp',
            'foto_out' => null,
            'lokasi_in' => '-6.2001,106.8000',
            'lokasi_out' => null,
            'terlambat' => 0,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }

    private function wfhApprovedHariIni(): void
    {
        DB::table('wfhs')->insert([
            'nik' => $this->karyawan->nik,
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'tgl_wfh' => self::HARI_INI,
            'deskripsi_pekerjaan' => 'Kerja remote',
            'status' => 'approved',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }

    private function mockFoto(): void
    {
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });
    }

    private function prosesPresensi(string $lokasi): array
    {
        $this->actingAs($this->karyawan, 'karyawan');

        $request = new Request([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => $lokasi,
        ]);

        return app(PresensiService::class)->processPresensi($request);
    }

    private function adaPresensi(): bool
    {
        // Query mentah: cast 'date' pada model menyimpan 'Y-m-d 00:00:00' di SQLite,
        // sehingga pencocokan persis dengan 'Y-m-d' tidak akan match.
        return DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->exists();
    }
}
