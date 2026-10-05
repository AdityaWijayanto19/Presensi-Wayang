<?php

namespace Tests\Feature\Karyawan;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtasanHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private Unitperusahaan $unit;

    private User $admin;

    private Karyawan $spv;

    private Karyawan $manager;

    private Karyawan $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);

        $this->unit = Unitperusahaan::create([
            'unit' => 'Unit Test',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->admin = User::factory()->create(['unit' => 'Unit Test', 'unit_id' => $this->unit->id]);
        $this->admin->assignRole('super_admin');

        $this->spv = Karyawan::factory()->spv()->create([
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);

        $this->manager = Karyawan::factory()->manager()->create([
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);

        $this->staff = Karyawan::factory()->create([
            'jabatan' => 'Staff',
            'role_approved' => 'Staff',
            'atasan_nik' => $this->spv->nik,
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);
    }

    // ======================================================================
    // PETA HIERARKI: Staff -> SPV -> Manager -> GM -> Direktur
    // ======================================================================

    public function test_get_atasan_for_staff_returns_spv_level(): void
    {
        $response = $this->actingAs($this->admin, 'user')
            ->getJson('/karyawan/get-atasan?role_approved=Staff');

        $response->assertOk();
        $response->assertJsonFragment(['nik' => $this->spv->nik]);
        $this->assertEquals(
            [$this->spv->nik],
            array_column($response->json(), 'nik')
        );
    }

    public function test_get_atasan_for_spv_returns_manager_level(): void
    {
        $response = $this->actingAs($this->admin, 'user')
            ->getJson('/karyawan/get-atasan?role_approved=SPV');

        $response->assertOk();
        $this->assertEquals(
            [$this->manager->nik],
            array_column($response->json(), 'nik')
        );
    }

    public function test_get_atasan_for_direktur_returns_empty(): void
    {
        $response = $this->actingAs($this->admin, 'user')
            ->getJson('/karyawan/get-atasan?role_approved=Direktur');

        $response->assertOk();
        $this->assertSame([], $response->json());
    }

    // ======================================================================
    // VALIDASI role_approved
    // ======================================================================

    public function test_store_karyawan_accepts_spv_role_approved(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/store', [
            'nik' => 'SPV099',
            'nama_lengkap' => 'Supervisor Baru',
            'jabatan' => 'SPV',
            'posisi' => 'SPV Operasional',
            'role_approved' => 'SPV',
            'atasan_nik' => $this->manager->nik,
            'unit' => 'Unit Test',
            'no_hp' => '08123456789',
            'jatah_cuti' => 12,
            'password' => 'secret123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('karyawans', [
            'nik' => 'SPV099',
            'role_approved' => 'SPV',
        ]);
    }

    public function test_update_karyawan_accepts_spv_role_approved(): void
    {
        $karyawan = Karyawan::factory()->create([
            'jabatan' => 'SPV',
            'role_approved' => 'Staff',
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/'.$karyawan->nik.'/update', [
            'nik' => $karyawan->nik,
            'nama_lengkap' => $karyawan->nama_lengkap,
            'jabatan' => 'SPV',
            'posisi' => $karyawan->posisi,
            'role_approved' => 'SPV',
            'atasan_nik' => $this->manager->nik,
            'unit' => 'Unit Test',
            'no_hp' => $karyawan->no_hp,
            'jatah_cuti' => 12,
            'page' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('karyawans', [
            'nik' => $karyawan->nik,
            'role_approved' => 'SPV',
        ]);
    }

    // ======================================================================
    // GUARD: atasan tidak boleh hilang diam-diam
    // ======================================================================

    public function test_update_rejects_clearing_atasan_for_non_direktur_role(): void
    {
        $this->assertNotEmpty($this->staff->atasan_nik);

        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/'.$this->staff->nik.'/update', [
            'nik' => $this->staff->nik,
            'nama_lengkap' => $this->staff->nama_lengkap,
            'jabatan' => 'Staff',
            'posisi' => $this->staff->posisi,
            'role_approved' => 'Staff',
            'atasan_nik' => '',
            'unit' => 'Unit Test',
            'no_hp' => $this->staff->no_hp,
            'jatah_cuti' => 12,
            'page' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('karyawans', [
            'nik' => $this->staff->nik,
            'atasan_nik' => $this->spv->nik,
        ]);
    }

    public function test_update_allows_atasan_for_direktur_role(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/'.$this->staff->nik.'/update', [
            'nik' => $this->staff->nik,
            'nama_lengkap' => $this->staff->nama_lengkap,
            'jabatan' => 'Direktur',
            'posisi' => $this->staff->posisi,
            'role_approved' => 'Direktur',
            'atasan_nik' => '',
            'unit' => 'Unit Test',
            'no_hp' => $this->staff->no_hp,
            'jatah_cuti' => 12,
            'page' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('karyawans', [
            'nik' => $this->staff->nik,
            'role_approved' => 'Direktur',
            'atasan_nik' => null,
        ]);
    }
}
