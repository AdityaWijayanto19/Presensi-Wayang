<?php

namespace Tests\Feature\Karyawan;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanValidationTest extends TestCase
{
    use RefreshDatabase;

    private Unitperusahaan $unit;

    private User $admin;

    private Karyawan $karyawan;

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

        $this->karyawan = Karyawan::factory()->create([
            'nik' => 'KRY001',
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);
    }

    // ======================================================================
    // STORE
    // ======================================================================

    public function test_store_menolak_nik_sudah_dipakai_dengan_pesan_indonesia(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/store', [
            'nik' => 'KRY001',
            'nama_lengkap' => 'Karyawan Baru',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Test',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertSame(
            'NIK sudah dipakai karyawan lain. Silakan gunakan NIK lain.',
            $response->getSession()->get('errors')->first('nik')
        );
        $this->assertDatabaseCount('karyawans', 1);
    }

    public function test_store_menolak_input_wajib_dengan_pesan_indonesia(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/store', [
            'nik' => 'KRY999',
            'jabatan' => 'Staff',
        ]);

        $response->assertSessionHasErrors(['nama_lengkap', 'posisi', 'unit', 'no_hp', 'jatah_cuti', 'password']);

        $errors = $response->getSession()->get('errors');
        $this->assertSame('Nama lengkap wajib diisi.', $errors->first('nama_lengkap'));
        $this->assertSame('Posisi wajib diisi.', $errors->first('posisi'));
        $this->assertSame('Unit perusahaan wajib dipilih.', $errors->first('unit'));
        $this->assertSame('Nomor HP wajib diisi.', $errors->first('no_hp'));
        $this->assertSame('Jatah cuti wajib diisi.', $errors->first('jatah_cuti'));
        $this->assertSame('Password wajib diisi.', $errors->first('password'));
    }

    public function test_store_menolak_unit_tidak_terdaftar(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/store', [
            'nik' => 'KRY999',
            'nama_lengkap' => 'Karyawan Baru',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Hilang',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
            'password' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('unit');
        $this->assertSame(
            'Unit perusahaan tidak ditemukan.',
            $response->getSession()->get('errors')->first('unit')
        );
    }

    // ======================================================================
    // UPDATE
    // ======================================================================

    public function test_update_menolak_nik_sudah_dipakai_karyawan_lain(): void
    {
        Karyawan::factory()->create([
            'nik' => 'KRY002',
            'unit' => 'Unit Test',
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/KRY001/update', [
            'nik' => 'KRY002',
            'nama_lengkap' => $this->karyawan->nama_lengkap,
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Test',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertSame(
            'NIK sudah dipakai karyawan lain. Silakan gunakan NIK lain.',
            $response->getSession()->get('errors')->first('nik')
        );
        $this->assertSame('KRY001', $this->karyawan->fresh()->nik);
    }

    public function test_update_mengizinkan_nik_tetap_sama(): void
    {
        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/KRY001/update', [
            'nik' => 'KRY001',
            'nama_lengkap' => 'Nama Baru',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Test',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', $this->karyawan->fresh()->nama_lengkap);
    }

    public function test_update_menolak_unit_wajib_dan_tidak_terdaftar(): void
    {
        $tanpaUnit = $this->actingAs($this->admin, 'user')->post('/karyawan/KRY001/update', [
            'nik' => 'KRY001',
            'nama_lengkap' => 'Nama Baru',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
        ]);

        $tanpaUnit->assertSessionHasErrors('unit');
        $this->assertSame(
            'Unit perusahaan wajib dipilih.',
            $tanpaUnit->getSession()->get('errors')->first('unit')
        );

        $unitHilang = $this->actingAs($this->admin, 'user')->post('/karyawan/KRY001/update', [
            'nik' => 'KRY001',
            'nama_lengkap' => 'Nama Baru',
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Hilang',
            'no_hp' => '081234567890',
            'jatah_cuti' => 12,
        ]);

        $unitHilang->assertSessionHasErrors('unit');
        $this->assertSame(
            'Unit perusahaan tidak ditemukan.',
            $unitHilang->getSession()->get('errors')->first('unit')
        );
    }

    public function test_halaman_karyawan_render_dengan_error_dan_old_input(): void
    {
        $this->actingAs($this->admin, 'user')->post('/karyawan/store', [
            'nik' => 'KRY001',
            'nama_lengkap' => 'Isian Salah',
            'jabatan' => 'Staff',
        ]);

        $response = $this->actingAs($this->admin, 'user')->get('/panel/karyawan');

        $response->assertOk();
        $response->assertSee('NIK sudah dipakai karyawan lain. Silakan gunakan NIK lain.', false);
        $response->assertSee('Isian Salah', false);
    }

    public function test_update_menolak_jatah_cuti_kurang_dari_terpakai(): void
    {
        \App\Models\Cuti::create([
            'nik' => 'KRY001',
            'durasi_hari' => 5,
            'tanggal_cuti' => [now()->format('Y-m-d')],
            'dikirim_tanggal' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'user')->post('/karyawan/KRY001/update', [
            'nik' => 'KRY001',
            'nama_lengkap' => $this->karyawan->nama_lengkap,
            'jabatan' => 'Staff',
            'posisi' => 'Staff Accounting',
            'unit' => 'Unit Test',
            'no_hp' => '081234567890',
            'jatah_cuti' => 3,
        ]);

        $response->assertSessionHasErrors('jatah_cuti');
        $this->assertStringContainsString(
            'Jatah cuti tidak boleh kurang dari cuti yang sudah terpakai',
            $response->getSession()->get('errors')->first('jatah_cuti')
        );
    }
}
