<?php

namespace Tests\Feature\Cuti;

use App\Enums\CutiStatus;
use App\Models\Cuti;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\CutiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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

    private function makeRequest(array $overrides = []): Request
    {
        return Request::create('/cuti/store', 'POST', array_merge([
            'durasi_hari' => 2,
            'tanggal_cuti' => [
                now('Asia/Jakarta')->addDays(5)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(6)->format('Y-m-d'),
            ],
            'keterangan' => 'Cuti keluarga',
        ], $overrides));
    }

    private function makeUploadRequest(): Request
    {
        return Request::create('/cuti/upload', 'POST', [], [], [
            'bukti_file' => UploadedFile::fake()->create('cuti.pdf', 100, 'application/pdf'),
        ]);
    }

    /**
     * Helper: buat cuti draft + upload file → status pending_admin.
     */
    private function createPendingCuti(array $draftOverrides = []): Cuti
    {
        $result = $this->service->storeDraftCuti($this->makeRequest($draftOverrides), $this->karyawan);
        $this->assertTrue($result['success'], 'Draft creation failed: '.($result['message'] ?? ''));

        $cuti = Cuti::find($result['cuti_id']);
        $upload = $this->service->uploadBuktiCuti($cuti->id, $this->makeUploadRequest(), $this->karyawan);
        $this->assertTrue($upload['success'], 'Upload failed: '.($upload['message'] ?? ''));

        return $cuti->fresh();
    }

    // ==================================================================
    // STEP 1 — DRAFT
    // ==================================================================

    public function test_store_draft_creates_draft_with_pdf_and_keeps_quota(): void
    {
        Storage::fake('public');

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);

        $this->assertTrue($result['success']);
        $cuti = Cuti::where('nik', 'KRY001')->first();
        $this->assertNotNull($cuti);
        $this->assertEquals(CutiStatus::Draft, $cuti->status);
        $this->assertNotEmpty($cuti->pdf_form_path);

        // Kuota TIDAK berkurang untuk draft (hanya approved yang mengurangi).
        $this->assertEquals(0, $this->karyawan->fresh()->totalCutiTerpakai());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_store_draft_rejects_when_quota_insufficient(): void
    {
        Storage::fake('public');

        $this->karyawan->update(['jatah_cuti' => 1]);

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);

        $this->assertFalse($result['success']);
        $this->assertEquals(0, Cuti::where('nik', 'KRY001')->count());
        $this->assertEquals(1, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_store_draft_rejects_date_used_by_approved_cuti(): void
    {
        Storage::fake('public');

        $tanggal = now('Asia/Jakarta')->addDays(5)->format('Y-m-d');
        Cuti::create([
            'nik' => 'KRY001',
            'status' => CutiStatus::Approved,
            'admin_status' => 'approved',
            'durasi_hari' => 1,
            'tanggal_cuti' => [$tanggal],
            'approved_at' => now('Asia/Jakarta'),
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);

        $this->assertFalse($result['success']);
    }

    public function test_rejected_cuti_dates_can_be_reused(): void
    {
        Storage::fake('public');

        $tanggal = now('Asia/Jakarta')->addDays(5)->format('Y-m-d');
        Cuti::create([
            'nik' => 'KRY001',
            'status' => CutiStatus::Rejected,
            'admin_status' => 'rejected',
            'durasi_hari' => 1,
            'tanggal_cuti' => [$tanggal],
            'rejected_reason' => 'File tidak jelas',
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);

        $this->assertTrue($result['success']);
    }

    public function test_draft_blocks_date_for_new_request(): void
    {
        Storage::fake('public');

        // Draft pertama memblokir tanggal untuk draft berikutnya.
        $r1 = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $this->assertTrue($r1['success']);

        $r2 = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $this->assertFalse($r2['success']);
    }

    // ==================================================================
    // STEP 2 — UPLOAD FILE
    // ==================================================================

    public function test_upload_moves_draft_to_pending_and_quota_still_intact(): void
    {
        Storage::fake('public');

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $cuti = Cuti::find($result['cuti_id']);

        $upload = $this->service->uploadBuktiCuti($cuti->id, $this->makeUploadRequest(), $this->karyawan);

        $this->assertTrue($upload['success']);
        $this->assertEquals(CutiStatus::PendingAdmin, $cuti->fresh()->status);
        $this->assertNotEmpty($cuti->fresh()->bukti_file);

        // Masih belum mengurangi kuota.
        $this->assertEquals(0, $this->karyawan->fresh()->totalCutiTerpakai());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_reupload_allowed_after_rejected(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();

        $reject = $this->service->rejectCuti($cuti->id, 'File tidak terbaca, mohon upload ulang');
        $this->assertTrue($reject['success']);
        $this->assertEquals(CutiStatus::Rejected, $cuti->fresh()->status);

        // Re-upload file → kembali pending_admin, alasan ditolak dibersihkan.
        $upload = $this->service->uploadBuktiCuti($cuti->id, $this->makeUploadRequest(), $this->karyawan);
        $this->assertTrue($upload['success']);

        $fresh = $cuti->fresh();
        $this->assertEquals(CutiStatus::PendingAdmin, $fresh->status);
        $this->assertNull($fresh->rejected_reason);
    }

    public function test_upload_fails_when_not_draft_or_rejected(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();

        $upload = $this->service->uploadBuktiCuti($cuti->id, $this->makeUploadRequest(), $this->karyawan);

        $this->assertFalse($upload['success']);
    }

    public function test_upload_rejected_for_other_karyawans_cuti(): void
    {
        Storage::fake('public');

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $cuti = Cuti::find($result['cuti_id']);

        $other = Karyawan::create([
            'nik' => 'KRY999',
            'nama_lengkap' => 'Karyawan Lain',
            'jabatan' => 'Staff',
            'posisi' => 'QA',
            'role_approved' => 'Staff',
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567899',
            'jatah_cuti' => 12,
            'password' => bcrypt('password'),
        ]);

        $upload = $this->service->uploadBuktiCuti($cuti->id, $this->makeUploadRequest(), $other);

        $this->assertFalse($upload['success']);
    }

    // ==================================================================
    // APPROVE / REJECT OLEH SUPERADMIN
    // ==================================================================

    public function test_approve_reduces_quota(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();

        $result = $this->service->approveCuti($cuti->id, 1);

        $this->assertTrue($result['success']);
        $this->assertEquals(CutiStatus::Approved, $cuti->fresh()->status);
        $this->assertEquals(2, $this->karyawan->fresh()->totalCutiTerpakai());
        $this->assertEquals(10, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_approve_revalidates_quota_and_prevents_overbooking(): void
    {
        Storage::fake('public');

        // Pending dibuat saat kuota masih cukup (jatah 12) — skenario race.
        $cuti = $this->createPendingCuti();

        // Kuota menyusut: jatah dikurangi & ada approved lain yang menghabiskan kuota.
        $this->karyawan->update(['jatah_cuti' => 2]);
        Cuti::create([
            'nik' => 'KRY001',
            'status' => CutiStatus::Approved,
            'admin_status' => 'approved',
            'durasi_hari' => 2,
            'tanggal_cuti' => [
                now('Asia/Jakarta')->addDays(20)->format('Y-m-d'),
                now('Asia/Jakarta')->addDays(21)->format('Y-m-d'),
            ],
            'approved_at' => now('Asia/Jakarta'),
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);

        $result = $this->service->approveCuti($cuti->id, 1);

        $this->assertFalse($result['success']);
        $this->assertEquals(CutiStatus::PendingAdmin, $cuti->fresh()->status);
    }

    public function test_approve_fails_when_not_pending(): void
    {
        Storage::fake('public');

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $cuti = Cuti::find($result['cuti_id']);

        $approve = $this->service->approveCuti($cuti->id, 1);

        $this->assertFalse($approve['success']);
    }

    public function test_reject_sets_rejected_without_consuming_quota(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();

        $result = $this->service->rejectCuti($cuti->id, 'Tanggal bentrok dengan kegiatan unit');

        $this->assertTrue($result['success']);
        $this->assertEquals(CutiStatus::Rejected, $cuti->fresh()->status);
        $this->assertEquals('Tanggal bentrok dengan kegiatan unit', $cuti->fresh()->rejected_reason);
        $this->assertEquals(0, $this->karyawan->fresh()->totalCutiTerpakai());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_reject_fails_when_not_pending(): void
    {
        Storage::fake('public');

        $result = $this->service->storeDraftCuti($this->makeRequest(), $this->karyawan);
        $cuti = Cuti::find($result['cuti_id']);

        $reject = $this->service->rejectCuti($cuti->id, 'Alasan apapun');

        $this->assertFalse($reject['success']);
    }

    // ==================================================================
    // DELETE & UPDATE (ADMIN)
    // ==================================================================

    public function test_delete_pending_cuti_keeps_quota_unchanged(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());

        $result = $this->service->deleteCuti($cuti->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, Cuti::count());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_delete_approved_cuti_restores_quota(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti();
        $this->service->approveCuti($cuti->id, 1);
        $this->assertEquals(10, $this->karyawan->fresh()->sisaCuti());

        $result = $this->service->deleteCuti($cuti->id);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, Cuti::count());
        $this->assertEquals(12, $this->karyawan->fresh()->sisaCuti());
    }

    public function test_admin_update_approved_cuti_within_quota(): void
    {
        Storage::fake('public');

        $cuti = $this->createPendingCuti(['durasi_hari' => 2]);
        $this->service->approveCuti($cuti->id, 1);
        $this->assertEquals(10, $this->karyawan->fresh()->sisaCuti());

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

    public function test_admin_update_rejects_over_quota(): void
    {
        Storage::fake('public');

        $this->karyawan->update(['jatah_cuti' => 2]);
        $cuti = $this->createPendingCuti(['durasi_hari' => 2]);
        $this->service->approveCuti($cuti->id, 1);

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

    // ==================================================================
    // HELPERS
    // ==================================================================

    public function test_used_dates_excludes_rejected_but_includes_active(): void
    {
        Storage::fake('public');

        $aktif = now('Asia/Jakarta')->addDays(5)->format('Y-m-d');
        $ditolak = now('Asia/Jakarta')->addDays(8)->format('Y-m-d');

        Cuti::create([
            'nik' => 'KRY001',
            'status' => CutiStatus::Approved,
            'admin_status' => 'approved',
            'durasi_hari' => 1,
            'tanggal_cuti' => [$aktif],
            'approved_at' => now('Asia/Jakarta'),
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);
        Cuti::create([
            'nik' => 'KRY001',
            'status' => CutiStatus::Rejected,
            'admin_status' => 'rejected',
            'durasi_hari' => 1,
            'tanggal_cuti' => [$ditolak],
            'rejected_reason' => 'x',
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);

        $used = CutiService::getUsedDates('KRY001');

        $this->assertContains($aktif, $used);
        $this->assertNotContains($ditolak, $used);
    }

    public function test_sisa_cuti_max_zero_when_jatah_zero(): void
    {
        $this->karyawan->update(['jatah_cuti' => 0]);
        $this->assertEquals(0, $this->karyawan->fresh()->sisaCuti());
    }
}
