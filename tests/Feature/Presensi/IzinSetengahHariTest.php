<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IzinSetengahHariTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-28';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        // Unit tanpa titik lokasi → validasiRadius fail-open (lolos).
        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
            'radius_meter' => 100,
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'SH001',
            'nama_lengkap' => 'Karyawan Setengah Hari',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ======================================================================
    // TERLAMBAT DITIADAKAN
    // ======================================================================

    public function test_izin_setengah_hari_approved_meniadakan_terlambat(): void
    {
        $this->izinSetengahHari('approved');
        $this->setTime('10:28:23');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));

        // Cast 'date' pada SQLite menyimpan 'Y-m-d 00:00:00', jadi query pakai like.
        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertNotNull($presensi);
        $this->assertSame('10:28:23', $presensi->jam_in);
        $this->assertSame(0, (int) $presensi->terlambat);
    }

    /** Baseline: masuk 10:28:23 vs jam_masuk 08:00:00 → 148 menit (detik dipotong). */
    public function test_tanpa_izin_terlambat_tetap_dihitung(): void
    {
        $this->setTime('10:28:23');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(148, (int) $presensi->terlambat);
    }

    public function test_izin_setengah_hari_pending_tidak_mengubah_terlambat(): void
    {
        $this->izinSetengahHari('pending_atasan');
        $this->setTime('10:28:23');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(148, (int) $presensi->terlambat);
    }

    public function test_izin_jenis_lain_tidak_meniadakan_terlambat(): void
    {
        DB::table('izins')->insert($this->izinRow('approved', 'pulang_cepat'));
        $this->setTime('10:28:23');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(148, (int) $presensi->terlambat);
    }

    // ======================================================================
    // ABSEN PULANG 4 JAM
    // ======================================================================

    public function test_absen_pulang_setelah_4_jam_berhasil(): void
    {
        $this->izinSetengahHari('approved');
        $this->setTime('12:00:00');
        $this->presensiHariIni(null, '08:00:00');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto-out.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame('out', $result['type']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertNotNull($presensi->jam_out);
    }

    public function test_absen_pulang_di_bawah_4_jam_diblokir_dengan_pesan_informatif(): void
    {
        $this->izinSetengahHari('approved');
        $this->setTime('10:00:00');
        $this->presensiHariIni(null, '08:00:00');

        $this->actingAs($this->karyawan, 'karyawan');

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertFalse($result['success']);
        $this->assertSame('out', $result['type']);

        $pesan = $result['message'];
        $this->assertStringContainsString('Izin Setengah Hari', $pesan);
        $this->assertStringContainsString('4 jam', $pesan);
        $this->assertStringContainsString('12:00', $pesan);
        $this->assertStringContainsString('kurang', $pesan);
    }

    public function test_tanpa_izin_pesan_8_jam_tetap_sama(): void
    {
        $this->setTime('10:00:00');
        $this->presensiHariIni(null, '08:00:00');

        $this->actingAs($this->karyawan, 'karyawan');

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertFalse($result['success']);
        $this->assertSame('Belum bisa presensi pulang! Minimal bekerja 8 jam.', $result['message']);
        $this->assertSame('out', $result['type']);
    }

    public function test_izin_setengah_hari_tetap_dihitung_normal_setelah_jam_ke_4(): void
    {
        // Lewat 4 jam tapi masih di bawah 8 jam → dengan izin tetap boleh pulang.
        $this->izinSetengahHari('approved');
        $this->setTime('13:00:00');
        $this->presensiHariIni(null, '08:00:00');

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto-out.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame('out', $result['type']);
    }

    // ======================================================================
    // RECALC
    // ======================================================================

    public function test_recalc_terlambat_menghormati_izin_setengah_hari(): void
    {
        $this->izinSetengahHari('approved');

        // Baris legacy: sudah telat tapi kolom terlambat masih terisi salah.
        DB::table('presensis')->insert([
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => '09:30:00',
            'jam_out' => '17:00:00',
            'foto_in' => 'in.webp',
            'foto_out' => 'out.webp',
            'lokasi_in' => '-6.200000,106.816666',
            'lokasi_out' => '-6.200000,106.816666',
            'terlambat' => 90,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        Artisan::call('presensi:recalc-terlambat');

        $this->assertStringContainsString('Diubah: 1', Artisan::output());
        $this->assertSame(0, (int) Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->value('terlambat'));
    }

    // ======================================================================
    // HELPERS
    // ======================================================================

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function requestPresensi(): Request
    {
        return new Request([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => '-6.200000,106.816666',
        ]);
    }

    /**
     * Insert mentah: cast 'date' pada model membuat nilai tersimpan sebagai
     * 'Y-m-d 00:00:00' di SQLite sehingga tidak cocok dengan query where('tgl_izin', 'Y-m-d').
     */
    private function izinRow(string $status, string $jenis = 'setengah_hari'): array
    {
        return [
            'nik' => $this->karyawan->nik,
            'tgl_izin' => self::HARI_INI,
            'jenis_izin' => $jenis,
            'status' => $status,
            'atasan_nik' => null,
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ];
    }

    private function izinSetengahHari(string $status): void
    {
        DB::table('izins')->insert($this->izinRow($status));
    }

    private function presensiHariIni(?string $jamOut, string $jamIn): void
    {
        DB::table('presensis')->insert([
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => $jamIn,
            'jam_out' => $jamOut,
            'foto_in' => 'in.webp',
            'foto_out' => $jamOut ? 'out.webp' : null,
            'lokasi_in' => '-6.200000,106.816666',
            'lokasi_out' => $jamOut ? '-6.200000,106.816666' : null,
            'terlambat' => 0,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }
}
