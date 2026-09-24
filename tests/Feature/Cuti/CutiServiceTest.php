<?php

namespace Tests\Feature\Cuti;

use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\CutiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CutiServiceTest extends TestCase
{
    use RefreshDatabase;

    private Unitperusahaan $unit;
    private Karyawan $karyawan;
    private CutiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CutiService::class);

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'KRY001',
            'nama_lengkap' => 'Karyawan Test',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567891',
            'jatah_cuti' => 12,
            'password' => bcrypt('password'),
        ]);
    }

    private function makeRequest(array $overrides = [])
    {
        return request(array_merge([
            'durasi_hari' => 2,
            'tanggal_cuti' => [
                now('Asia/Jakarta')->addDays(5)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(6)->format('Y-m-d'),
            ],
            'keterangan' => 'Cuti keluarga',
            'bukti_file' => UploadedFile::fake()->create('cuti.pdf', 100, 'application/pdf'),
        ], $overrides));
    }

    public function test_store_cuti_reduces_quota(): void
    {
        Storage::fake('public');

        $this->assertEquals(12, $this->karyawan->sisaCuti());

        $result = $this->service->storeCuti($this->makeRequest(), $this->karyawan);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, Cuti::where('nik', 'KRY001')->count());
        $this->assertEquals(2, $this->karyawan->fresh()->totalCutiTerpakai());
        $this->assertEquals(10, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_store_cuti_rejects_when_quota_insufficient(): void
    {
        Storage::fake('public');

        $this->karyawan->update(['jatah_cuti' => 1]);

        $result = $this->service->storeCuti($this->makeRequest(), $this->karyawan);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, Cuti::where('nik', 'KRY001')->count());
        $this->assertEquals(1, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_delete_cuti_restores_quota(): void
    {
        Storage::fake('public');

        $this->service->storeCuti($this->makeRequest(), $this->karyawan);
        $this->assertEquals(10, $this->karyawan->fresh()->sisaCuti());

        $cuti = Cuti::first();
        $result = $this->service->deleteCuti($cuti->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, Cuti::count());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_update_cuti_duration_within_quota(): void
    {
        Storage::fake('public');

        $this->service->storeCuti($this->makeRequest(['durasi_hari' => 2]), $this->karyawan);
        $cuti = Cuti::first();

        $update = $this->makeRequest([
            'durasi_hari' => 3,
            'tanggal_cuti' => [
                now('Asia/Jakarta')->addDays(5)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(6)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(7)->format('Y-m-d'),
            ],
        ]);

        $result = $this->service->updateCuti($cuti->id, $update);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $cuti->fresh()->durasi_hari);
        $this->assertEquals(9, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_update_cuti_rejects_over_quota(): void
    {
        Storage::fake('public');

        $this->karyawan->update(['jatah_cuti' => 2]);
        $this->service->storeCuti($this->makeRequest(['durasi_hari' => 2]), $this->karyawan);
        $cuti = Cuti::first();

        $update = $this->makeRequest([
            'durasi_hari' => 3,
            'tanggal_cuti' => [
                now('Asia/Jakarta')->addDays(5)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(6)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(7)->format('Y-m-d'),
            ],
        ]);

        $result = $this->service->updateCuti($cuti->id, $update);

        $this->assertFalse($result['success']);
        $this->assertEquals(2, $cuti->fresh()->durasi_hari);
    }

    public function test_sisa_cuti_max_zero_when_jatah_zero(): void
    {
        $this->karyawan->update(['jatah_cuti' => 0]);
        $this->assertEquals(0, $this->karyawan->fresh()->sisaCuti());
    }
}
