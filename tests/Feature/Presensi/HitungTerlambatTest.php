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
use Tests\TestCase;

class HitungTerlambatTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-30';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_unit_arthama_tidak_lagi_bebas_terlambat(): void
    {
        $this->unit('Arthama', '10:05:00');

        $terlambat = app(PresensiService::class)->hitungTerlambatPresensi('Arthama', '10:28:23');

        $this->assertSame(23, $terlambat);
    }

    public function test_masuk_tepat_atau_sebelum_jam_masuk_bukan_terlambat(): void
    {
        $this->unit('Arthama', '10:05:00');

        $service = app(PresensiService::class);

        $this->assertSame(0, $service->hitungTerlambatPresensi('Arthama', '10:05:00'));
        $this->assertSame(0, $service->hitungTerlambatPresensi('Arthama', '10:04:59'));
    }

    public function test_keterlambatan_lebih_dari_sejam_memakai_menit_exact(): void
    {
        $this->unit('Wayang', '08:00:00');

        $terlambat = app(PresensiService::class)->hitungTerlambatPresensi('Wayang', '09:05:00');

        $this->assertSame(65, $terlambat);
    }

    public function test_keterlambatan_enam_satu_menit_bukan_enam_puluh(): void
    {
        $this->unit('Wayang', '08:05:00');

        $terlambat = app(PresensiService::class)->hitungTerlambatPresensi('Wayang', '09:06:00');

        $this->assertSame(61, $terlambat);
    }

    public function test_unit_tanpa_data_jam_masuk_memakai_default_0800(): void
    {
        $terlambat = app(PresensiService::class)->hitungTerlambatPresensi('UnitBelumTerdaftar', '08:30:00');

        $this->assertSame(30, $terlambat);
    }

    public function test_proses_presensi_arthama_menyimpan_terlambat(): void
    {
        $unit = $this->unit('Arthama', '10:05:00');
        $karyawan = $this->karyawan($unit);

        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' 10:28:23', 'Asia/Jakarta'));
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });
        $this->actingAs($karyawan, 'karyawan');

        $result = app(PresensiService::class)->processPresensi(new Request([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => '-6.2001,106.8000',
        ]));

        $this->assertTrue($result['success'], json_encode($result));

        $presensi = Presensi::where('nik', $karyawan->nik)->first();
        $this->assertNotNull($presensi);
        $this->assertSame('10:28:23', $presensi->jam_in);
        $this->assertSame(23, (int) $presensi->terlambat);
    }

    private function unit(string $nama, string $jamMasuk): Unitperusahaan
    {
        return Unitperusahaan::create([
            'unit' => $nama,
            'perusahaan' => 'PT Test',
            'jam_masuk' => $jamMasuk,
            'radius_meter' => 100,
        ]);
    }

    private function karyawan(Unitperusahaan $unit): Karyawan
    {
        return Karyawan::create([
            'nik' => 'TER001',
            'nama_lengkap' => 'Karyawan Terlambat',
            'jabatan' => 'Staff',
            'posisi' => 'Sales Agent',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => $unit->unit,
            'unit_id' => $unit->id,
            'no_hp' => '081234000002',
            'password' => bcrypt('password'),
        ]);
    }
}
