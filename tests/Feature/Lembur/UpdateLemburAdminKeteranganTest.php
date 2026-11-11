<?php

namespace Tests\Feature\Lembur;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Keterangan pengajuan lembur wajib terisi saat diedit admin.
 *
 * Bug lama: modal Edit Lembur tidak punya field keterangan, tapi
 * updateLemburAdmin() tetap menulis 'keterangan' => $request->keterangan
 * (null) — sehingga setiap edit admin menghapus keterangan pengajuan.
 *
 * Cakupan:
 *  - update tanpa keterangan ditolak validasi, data tidak berubah
 *  - keterangan < 5 karakter ditolak validasi
 *  - keterangan valid tersimpan utuh saat status/diubah admin
 */
class UpdateLemburAdminKeteranganTest extends TestCase
{
    use RefreshDatabase;

    private const TGL_LEMBUR = '2026-11-11';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'KRYLEMB01',
            'nama_lengkap' => 'Karyawan Lembur',
            'jabatan' => 'Staff',
            'posisi' => 'Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567890',
            'password' => bcrypt('password'),
        ]);

        $this->superAdmin = User::factory()->create([
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
        ]);
        $this->superAdmin->assignRole('super_admin');
    }

    private function buatLembur(array $atribut = []): int
    {
        return DB::table('lemburs')->insertGetId(array_merge([
            'nik' => $this->karyawan->nik,
            'tgl_lembur' => self::TGL_LEMBUR,
            'keterangan' => 'Lembur test',
            'status' => 'approved',
            'atasan_nik' => null,
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'approved_at' => self::TGL_LEMBUR . ' 17:00:00',
            'durasi_jam' => 2,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    private function updateLembur(int $id, array $payload)
    {
        return $this->actingAs($this->superAdmin, 'user')
            ->post('/presensi/lembur/' . $id . '/update', $payload);
    }

    private function barisLembur(int $id): object
    {
        return DB::table('lemburs')->where('id', $id)->firstOrFail();
    }

    public function test_update_tanpa_keterangan_ditolak_dan_data_tidak_berubah(): void
    {
        $id = $this->buatLembur();
        $sebelum = $this->barisLembur($id);

        $this->updateLembur($id, [
            'tgl_lembur' => self::TGL_LEMBUR,
            'status' => 'pending_admin',
            'durasi_jam' => 2,
        ])->assertSessionHasErrors('keterangan');

        $sesudah = $this->barisLembur($id);
        $this->assertSame($sebelum->keterangan, $sesudah->keterangan);
        $this->assertSame('approved', $sesudah->status, 'Data tidak boleh berubah saat validasi gagal.');
    }

    public function test_update_dengan_keterangan_kurang_dari_5_karakter_ditolak(): void
    {
        $id = $this->buatLembur();

        $this->updateLembur($id, [
            'tgl_lembur' => self::TGL_LEMBUR,
            'keterangan' => 'abc',
            'status' => 'pending_admin',
            'durasi_jam' => 2,
        ])->assertSessionHasErrors('keterangan');

        $this->assertSame('Lembur test', $this->barisLembur($id)->keterangan);
        $this->assertSame('approved', $this->barisLembur($id)->status);
    }

    public function test_update_dengan_keterangan_valid_mempertahankan_keterangan_saat_status_diubah(): void
    {
        $id = $this->buatLembur();

        $this->updateLembur($id, [
            'tgl_lembur' => self::TGL_LEMBUR,
            'keterangan' => 'Lembur test',
            'status' => 'pending_admin',
            'durasi_jam' => 2,
        ])->assertSessionHasNoErrors();

        $baris = $this->barisLembur($id);
        $this->assertSame('Lembur test', $baris->keterangan, 'Edit admin tidak boleh menghapus keterangan.');
        $this->assertSame('pending_admin', $baris->status);
    }

    public function test_update_mengubah_keterangan_baru_tersimpan(): void
    {
        $id = $this->buatLembur();

        $this->updateLembur($id, [
            'tgl_lembur' => self::TGL_LEMBUR,
            'keterangan' => 'Keterangan lembur yang diperbarui oleh admin',
            'status' => 'approved',
            'durasi_jam' => 3,
        ])->assertSessionHasNoErrors();

        $baris = $this->barisLembur($id);
        $this->assertSame('Keterangan lembur yang diperbarui oleh admin', $baris->keterangan);
        $this->assertEquals(3, (float) $baris->durasi_jam);
    }

    public function test_data_legacy_tanpa_keterangan_harus_mengisi_saat_edit(): void
    {
        $id = $this->buatLembur(['keterangan' => null]);

        $this->updateLembur($id, [
            'tgl_lembur' => self::TGL_LEMBUR,
            'status' => 'pending_admin',
            'durasi_jam' => 2,
        ])->assertSessionHasErrors('keterangan');

        $this->assertNull($this->barisLembur($id)->keterangan);
    }
}
