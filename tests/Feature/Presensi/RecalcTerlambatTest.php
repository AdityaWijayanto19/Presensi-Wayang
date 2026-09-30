<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecalcTerlambatTest extends TestCase
{
    use RefreshDatabase;

    private const UPDATE_LAMA = '2026-01-01 00:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        $unit = Unitperusahaan::create([
            'unit' => 'Arthama',
            'perusahaan' => 'PT Arthama Global Indonesia',
            'jam_masuk' => '10:05:00',
            'radius_meter' => 100,
        ]);

        Karyawan::create([
            'nik' => 'RAI001',
            'nama_lengkap' => 'MUHAMMAD RAIHAN',
            'jabatan' => 'Staff',
            'posisi' => 'Sales Agent',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => $unit->unit,
            'unit_id' => $unit->id,
            'no_hp' => '081234000003',
            'password' => bcrypt('password'),
        ]);

        // Baris legacy: sudah telat tapi kolom terlambat masih 0.
        $this->presensi('RAI001', '2026-09-30', '10:28:23', 0);
        // Baris yang sudah benar: tidak boleh berubah.
        $this->presensi('RAI001', '2026-09-29', '09:50:00', 0);
    }

    public function test_dry_run_menghitung_tanpa_mengubah_data(): void
    {
        Artisan::call('presensi:recalc-terlambat', ['--dry-run' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('Dicek: 2, Diubah: 1, Dilewati: 0', $output);
        $this->assertSame(0, $this->terlambat('2026-09-30'));
    }

    public function test_run_menghitung_ulang_kolom_terlambat(): void
    {
        Artisan::call('presensi:recalc-terlambat');

        $this->assertSame(23, $this->terlambat('2026-09-30'));
        $this->assertSame(0, $this->terlambat('2026-09-29'));
    }

    public function test_run_kedua_idempoten(): void
    {
        Artisan::call('presensi:recalc-terlambat');
        Artisan::call('presensi:recalc-terlambat');

        $this->assertStringContainsString('Dicek: 2, Diubah: 0, Dilewati: 0', Artisan::output());
        $this->assertSame(23, $this->terlambat('2026-09-30'));
    }

    private function terlambat(string $tanggal): int
    {
        return (int) DB::table('presensis')
            ->where('nik', 'RAI001')
            ->where('tgl_presensi', 'like', $tanggal.'%')
            ->value('terlambat');
    }

    private function presensi(string $nik, string $tanggal, string $jamIn, int $terlambat): void
    {
        DB::table('presensis')->insert([
            'nik' => $nik,
            'tgl_presensi' => $tanggal,
            'jam_in' => $jamIn,
            'jam_out' => null,
            'foto_in' => 'foto-in.webp',
            'foto_out' => null,
            'lokasi_in' => '-6.2001,106.8000',
            'lokasi_out' => null,
            'terlambat' => $terlambat,
            'created_at' => self::UPDATE_LAMA,
            'updated_at' => self::UPDATE_LAMA,
        ]);
    }
}
