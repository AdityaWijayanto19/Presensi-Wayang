<?php

namespace Tests\Feature\ActivityLog;

use App\Enums\IzinStatus;
use App\Enums\JenisIzin;
use App\Models\AdminActivityLog;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(RolePermissionSeeder::class);

        $unit = Unitperusahaan::create([
            'unit' => 'Unit Test',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->superAdmin = User::factory()->create(['unit' => 'Unit Test', 'unit_id' => $unit->id]);
        $this->superAdmin->assignRole('super_admin');

        $this->admin = User::factory()->create(['unit' => 'Unit Test', 'unit_id' => $unit->id]);
        $this->admin->assignRole('admin');

        $this->owner = User::factory()->create(['unit' => 'Unit Test', 'unit_id' => $unit->id]);
        $this->owner->assignRole('owner');
    }

    public function test_mutating_request_is_logged(): void
    {
        $response = $this->actingAs($this->superAdmin, 'user')->post('/unitperusahaan/store', [
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
            'radius_meter' => 100,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseCount('admin_activity_logs', 1);
        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $this->superAdmin->id,
            'user_name' => $this->superAdmin->name,
            'role' => 'super_admin',
            'module' => 'unit',
            'action' => 'create',
            'status' => 'success',
            'path' => '/unitperusahaan/store',
        ]);
    }

    public function test_failed_request_is_logged_as_failed(): void
    {
        $this->actingAs($this->superAdmin, 'user')->post('/unitperusahaan/store', []);

        $this->assertDatabaseCount('admin_activity_logs', 1);
        $this->assertDatabaseHas('admin_activity_logs', [
            'module' => 'unit',
            'action' => 'create',
            'status' => 'failed',
        ]);
    }

    public function test_read_only_post_request_is_not_logged(): void
    {
        $this->actingAs($this->superAdmin, 'user')->post('/getpresensi', [
            'tanggal' => now('Asia/Jakarta')->format('Y-m-d'),
        ]);

        $this->assertDatabaseCount('admin_activity_logs', 0);
    }

    public function test_activity_log_page_is_visible_to_super_admin(): void
    {
        $this->actingAs($this->superAdmin, 'user')
            ->get('/panel/activity-log')
            ->assertOk()
            ->assertSee('Log Aktivitas');
    }

    public function test_activity_log_page_is_forbidden_for_admin_and_owner(): void
    {
        $this->actingAs($this->admin, 'user')
            ->get('/panel/activity-log')
            ->assertForbidden();

        $this->actingAs($this->owner, 'user')
            ->get('/panel/activity-log')
            ->assertForbidden();
    }

    public function test_log_page_only_shows_filtered_results(): void
    {
        $this->actingAs($this->superAdmin, 'user')->post('/unitperusahaan/store', [
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
            'radius_meter' => 100,
        ]);

        $this->actingAs($this->superAdmin, 'user')
            ->get('/panel/activity-log?module=unit&action=create')
            ->assertOk()
            ->assertSee('Membuat data unit perusahaan baru');

        $this->actingAs($this->superAdmin, 'user')
            ->get('/panel/activity-log?module=karyawan')
            ->assertOk()
            ->assertDontSee('Membuat data unit perusahaan baru')
            ->assertSee('Belum ada aktivitas yang tercatat');
    }

    public function test_izin_approval_log_includes_karyawan_name_and_date(): void
    {
        $karyawan = Karyawan::factory()->create();

        $izin = Izin::create([
            'nik' => $karyawan->nik,
            'tgl_izin' => '2026-10-01',
            'jenis_izin' => JenisIzin::TidakMasuk->value,
            'status' => IzinStatus::PendingAdmin->value,
            'admin_status' => 'pending',
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->superAdmin, 'user')
            ->post('/presensi/dataizin/'.$izin->id.'/approve')
            ->assertStatus(302);

        $this->assertDatabaseHas('admin_activity_logs', [
            'module' => 'izin',
            'action' => 'approve',
            'entity_id' => (string) $izin->id,
            'description' => 'Menyetujui pengajuan izin #'.$izin->id.' — '.$karyawan->nama_lengkap.' (tanggal 01 Oktober 2026)',
            'status' => 'success',
        ]);
    }

    public function test_karyawan_delete_log_includes_name_even_after_record_removed(): void
    {
        $karyawan = Karyawan::factory()->create();

        $this->actingAs($this->superAdmin, 'user')
            ->post('/karyawan/'.$karyawan->nik.'/delete')
            ->assertStatus(302);

        $this->assertDatabaseMissing('karyawans', ['nik' => $karyawan->nik]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'module' => 'karyawan',
            'action' => 'delete',
            'entity_id' => $karyawan->nik,
            'description' => 'Menghapus data karyawan '.$karyawan->nik.' ('.$karyawan->nama_lengkap.')',
            'status' => 'success',
        ]);
    }

    public function test_karyawan_store_log_includes_submitted_name(): void
    {
        $this->actingAs($this->superAdmin, 'user')->post('/karyawan/store', [
            'nik' => '9001',
            'nama_lengkap' => 'Budi Santoso',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'role_approved' => 'Staff',
            'unit' => 'Unit Test',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
            'password' => 'rahasia123',
        ])->assertStatus(302);

        $this->assertDatabaseHas('admin_activity_logs', [
            'module' => 'karyawan',
            'action' => 'create',
            'description' => 'Membuat data karyawan baru Budi Santoso',
            'status' => 'success',
        ]);
    }

    public function test_cleanup_command_removes_expired_logs(): void
    {
        $log = AdminActivityLog::create([
            'user_id' => $this->superAdmin->id,
            'user_name' => $this->superAdmin->name,
            'role' => 'super_admin',
            'module' => 'unit',
            'action' => 'create',
            'description' => 'Membuat data unit perusahaan baru',
            'status' => AdminActivityLog::STATUS_SUCCESS,
            'method' => 'POST',
            'path' => '/unitperusahaan/store',
        ]);

        DB::table('admin_activity_logs')->where('id', $log->id)
            ->update(['created_at' => now()->subDays(100)]);

        $this->artisan('activitylog:cleanup', ['--days' => 90])->assertSuccessful();

        $this->assertDatabaseCount('admin_activity_logs', 0);
    }
}
