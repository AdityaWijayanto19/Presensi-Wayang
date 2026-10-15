<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\IzinService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IzinTerlambatPresensiTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-10-13';

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
            'nik' => 'TL001',
            'nama_lengkap' => 'Karyawan Terlambat Izin',
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
    // JAM DATANG SEBAGAI BASELINE KETERLAMBATAN
    // ======================================================================

    /**
     * Kasus inti: izin terlambat jam_datang 10:00, presensi 09:30.
     * Sudah lewat jam_masuk unit (08:00) tapi masih sebelum jam_datang
     * yang disepakati → dianggap TEPAT WAKTU (terlambat = 0), bukan 90 menit.
     */
    public function test_izin_terlambat_approved_sebelum_jam_datang_bukan_terlambat(): void
    {
        $this->izinTerlambat('approved', '10:00');
        $this->setTime('09:30:00');

        $this->presensiHariIni('09:30:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertNotNull($presensi);
        $this->assertSame(0, (int) $presensi->terlambat);
    }

    /** Setelah jam_datang → dihitung dari jam_datang, bukan jam_masuk unit. */
    public function test_izin_terlambat_approved_setelah_jam_datang_dihitung_dari_jam_datang(): void
    {
        $this->izinTerlambat('approved', '10:00');
        $this->setTime('10:15:00');

        $this->presensiHariIni('10:15:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(15, (int) $presensi->terlambat);
    }

    /** Tepat di jam_datang → terlambat 0. */
    public function test_izin_terlambat_approved_tepat_jam_datang_bukan_terlambat(): void
    {
        $this->izinTerlambat('approved', '10:00');
        $this->setTime('10:00:00');

        $this->presensiHariIni('10:00:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(0, (int) $presensi->terlambat);
    }

    /** Baseline: tanpa izin, 09:30 vs jam_masuk 08:00 → 90 menit. */
    public function test_tanpa_izin_terlambat_dihitung_dari_jam_masuk_unit(): void
    {
        $this->setTime('09:30:00');

        $this->presensiHariIni('09:30:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(90, (int) $presensi->terlambat);
    }

    /** Izin terlambat masih pending → tidak mengubah perhitungan. */
    public function test_izin_terlambat_pending_tidak_mengubah_perhitungan(): void
    {
        $this->izinTerlambat('pending_atasan', '10:00');
        $this->setTime('09:30:00');

        $this->presensiHariIni('09:30:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(90, (int) $presensi->terlambat);
    }

    // ======================================================================
    // UNIT DENGAN JAM MASUK LEBIH SIANG (JAM_DATANG BISA LEBIH AWAL)
    // ======================================================================

    /**
     * Unit jam masuk 10:05, izin terlambat jam_datang 09:00 (lebih awal dari
     * jam_masuk unit). Baseline harus tetap jam_masuk unit (10:05) — izin hanya
     * boleh merileksasi, tidak mengetatkan. Presensi 09:30 → tepat waktu (0),
     * bukan telat 30 menit.
     */
    public function test_jam_datang_lebih_awal_dari_jam_masuk_unit_tidak_mengetatkan(): void
    {
        $this->unit->update(['jam_masuk' => '10:05:00']);
        $this->izinTerlambat('approved', '09:00');
        $this->setTime('09:30:00');

        $this->presensiHariIni('09:30:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(0, (int) $presensi->terlambat);
    }

    /**
     * Unit jam masuk 10:05, izin terlambat jam_datang 11:00 (lebih akhir).
     * Baseline pindah ke jam_datang — presensi 11:15 → telat 15 menit (bukan 70).
     */
    public function test_jam_datang_lebih_akhir_dari_jam_masuk_unit_dipakai(): void
    {
        $this->unit->update(['jam_masuk' => '10:05:00']);
        $this->izinTerlambat('approved', '11:00');
        $this->setTime('11:15:00');

        $this->presensiHariIni('11:15:00');

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->first();
        $this->assertSame(15, (int) $presensi->terlambat);
    }

    // ======================================================================
    // RECALC OTOMATIS SAAT APPROVE / UPDATE ADMIN
    // ======================================================================

    public function test_recalc_terlambat_menghormati_izin_terlambat(): void
    {
        $this->izinTerlambat('approved', '10:00');

        // Baris legacy: tercatat telat 90 menit karena belum tahu izin.
        DB::table('presensis')->insert($this->presensiRow('09:30:00', 90));

        Artisan::call('presensi:recalc-terlambat');

        $this->assertStringContainsString('Diubah: 1', Artisan::output());
        $this->assertSame(0, (int) Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->value('terlambat'));
    }

    /** Approval admin SETELAH presensi tercatat → kolom terlambat ikut dihitung ulang. */
    public function test_approve_izin_terlambat_oleh_admin_menghitung_ulang_presensi(): void
    {
        // Presensi sudah tercatat telat 90 menit, izin masih pending_admin.
        DB::table('presensis')->insert($this->presensiRow('09:30:00', 90));
        $izinId = DB::table('izins')->insertGetId($this->izinRow('pending_admin', '10:00'));

        $result = app(IzinService::class)->approveIzinAdmin($izinId);

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame(0, (int) Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->value('terlambat'));
    }

    /** Admin mencabut status approved → perhitungan kembali normal ke jam_masuk unit. */
    public function test_update_izin_admin_ke_rejected_mengembalikan_perhitungan_normal(): void
    {
        DB::table('izins')->insert($this->izinRow('approved', '10:00'));
        DB::table('presensis')->insert($this->presensiRow('09:30:00', 0));

        $izinId = (int) DB::table('izins')->where('nik', $this->karyawan->nik)->value('id');

        $result = app(IzinService::class)->updateIzinAdmin($izinId, new Request([
            'tgl_izin' => self::HARI_INI,
            'jenis_izin' => 'terlambat',
            'jam_datang' => '10:00',
            'status' => 'rejected',
        ]));

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame(90, (int) Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->value('terlambat'));
    }

    /** Admin mengubah tanggal izin → presensi tanggal lama dihitung ulang ke baseline normal. */
    public function test_update_izin_admin_mengubah_tanggal_menghitung_ulang_tanggal_lama(): void
    {
        DB::table('izins')->insert($this->izinRow('approved', '10:00'));
        DB::table('presensis')->insert($this->presensiRow('09:30:00', 0));

        $izinId = (int) DB::table('izins')->where('nik', $this->karyawan->nik)->value('id');

        $result = app(IzinService::class)->updateIzinAdmin($izinId, new Request([
            'tgl_izin' => '2026-10-20',
            'jenis_izin' => 'terlambat',
            'jam_datang' => '10:00',
            'status' => 'approved',
        ]));

        $this->assertTrue($result['success'], json_encode($result));
        // Presensi tanggal lama (13 Okt) tidak lagi punya izin → kembali 90 menit.
        $this->assertSame(90, (int) Presensi::where('nik', $this->karyawan->nik)
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

    /**
     * Presensi via service penuh (meniru alur nyata: prosesMasuk menyimpan
     * kolom terlambat hasil hitungTerlambatPresensi).
     */
    private function presensiHariIni(string $jamIn): void
    {
        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });

        $result = app(PresensiService::class)->processPresensi(new Request([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => '-6.200000,106.816666',
        ]));

        $this->assertTrue($result['success'], json_encode($result));
    }

    /**
     * Insert mentah: cast 'date' pada model membuat nilai tersimpan sebagai
     * 'Y-m-d 00:00:00' di SQLite sehingga tidak cocok dengan query where('tgl_izin', 'Y-m-d').
     */
    private function izinRow(string $status, string $jamDatang): array
    {
        return [
            'nik' => $this->karyawan->nik,
            'tgl_izin' => self::HARI_INI,
            'jenis_izin' => 'terlambat',
            'jam_datang' => $jamDatang,
            'keterangan' => 'Izin terlambat',
            'status' => $status,
            'atasan_nik' => null,
            'atasan_status' => $status === 'approved' ? 'approved' : 'pending',
            'admin_status' => $status === 'approved' ? 'approved' : 'pending',
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ];
    }

    private function izinTerlambat(string $status, string $jamDatang): void
    {
        DB::table('izins')->insert($this->izinRow($status, $jamDatang));
    }

    private function presensiRow(string $jamIn, int $terlambat): array
    {
        return [
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => $jamIn,
            'jam_out' => null,
            'foto_in' => 'in.webp',
            'foto_out' => null,
            'lokasi_in' => '-6.200000,106.816666',
            'lokasi_out' => null,
            'terlambat' => $terlambat,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ];
    }
}