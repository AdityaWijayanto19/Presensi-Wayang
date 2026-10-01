<?php

namespace Tests\Feature\Lembur;

use App\Enums\LemburStatus;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\LemburService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LemburServiceTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-28';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    private Karyawan $atasan;

    private LemburService $lemburService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->atasan = Karyawan::create([
            'nik' => 'LMBATS001',
            'nama_lengkap' => 'Atasan Lembur',
            'jabatan' => 'Manager',
            'posisi' => 'Manager IT',
            'role_approved' => 'Manager',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567801',
            'password' => bcrypt('password'),
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'LMB001',
            'nama_lengkap' => 'Karyawan Lembur',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => 'LMBATS001',
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567802',
            'password' => bcrypt('password'),
        ]);

        $this->lemburService = app(LemburService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function presensiHariIni(?string $jamOut = null, string $jamIn = '08:00:00', ?string $tgl = null): void
    {
        // Insert mentah: cast 'date' pada model membuat nilai tersimpan sebagai
        // 'Y-m-d 00:00:00' di SQLite sehingga tidak cocok dengan query where('tgl_presensi', 'Y-m-d').
        DB::table('presensis')->insert([
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => $tgl ?? self::HARI_INI,
            'jam_in' => $jamIn,
            'jam_out' => $jamOut,
            'foto_in' => 'foto-in.webp',
            'foto_out' => $jamOut ? 'foto-out.webp' : null,
            'lokasi_in' => '-6.200000,106.816666',
            'lokasi_out' => $jamOut ? '-6.200000,106.816666' : null,
            'terlambat' => 0,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }

    private function lemburHariIni(array $atribut = []): void
    {
        DB::table('lemburs')->insert(array_merge([
            'nik' => $this->karyawan->nik,
            'tgl_lembur' => self::HARI_INI,
            'keterangan' => 'Lembur test',
            'status' => 'approved',
            'atasan_nik' => 'LMBATS001',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'laporan_deskripsi' => null,
            'laporan_status' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    private function requestPresensi(): Request
    {
        return new Request([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => '-6.200000,106.816666',
        ]);
    }

    // ======================================================================
    // canSubmit — syarat absen pulang sebelum ajukan lembur
    // ======================================================================

    public function test_can_submit_blocked_when_presensi_belum_pulang(): void
    {
        $this->setTime('18:30:00');
        $this->presensiHariIni(null);

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertFalse($result['can']);
        $this->assertSame('Silakan absen pulang terlebih dahulu sebelum mengajukan lembur.', $result['message']);
    }

    public function test_can_submit_allowed_setelah_presensi_pulang(): void
    {
        $this->setTime('18:30:00');
        $this->presensiHariIni('17:05:00');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertTrue($result['can']);
    }

    public function test_can_submit_allowed_tanpa_baris_presensi(): void
    {
        $this->setTime('18:30:00');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertTrue($result['can']);
    }

    public function test_can_submit_presensi_kemarin_tidak_memblokir(): void
    {
        $this->setTime('18:30:00');
        $this->presensiHariIni(null, '08:00:00', '2026-09-27');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertTrue($result['can']);
    }

    public function test_can_submit_duplikat_dicek_sebelum_cek_presensi_pulang(): void
    {
        $this->setTime('18:30:00');
        $this->presensiHariIni(null);
        $this->lemburHariIni();

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertFalse($result['can']);
        $this->assertSame('Anda sudah mengajukan lembur hari ini.', $result['message']);
    }

    public function test_can_submit_window_pagi_tetap_berlaku(): void
    {
        $this->setTime('10:00:00');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertTrue($result['can']);
    }

    public function test_can_submit_ditolak_di_gap_1651_1759(): void
    {
        $this->setTime('17:30:00');
        $this->presensiHariIni('16:00:00');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertFalse($result['can']);
        $this->assertSame(
            'Pengajuan lembur hanya bisa dilakukan pada pukul 00:01-16:50 atau 18:00-23:59.',
            $result['message']
        );
    }

    public function test_can_submit_ditolak_tepat_pukul_0000(): void
    {
        $this->setTime('00:00:00');

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertFalse($result['can']);
    }

    // ======================================================================
    // processPresensi — laporan lembur TIDAK memblokir absen pulang
    // ======================================================================

    public function test_absen_pulang_tidak_diblokir_laporan_lembur_kosong(): void
    {
        $this->setTime('17:30:00');
        $this->presensiHariIni(null);
        $this->lemburHariIni();

        $this->actingAs($this->karyawan, 'karyawan');
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto-out.webp');
        });

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertTrue($result['success']);
        $this->assertSame('out', $result['type']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertNotNull($presensi->jam_out);
        $this->assertSame('17:30:00', $presensi->jam_out);
    }

    public function test_absen_pulang_masih_diblokir_belum_8_jam(): void
    {
        $this->setTime('16:00:00');
        $this->presensiHariIni(null, '15:00:00');

        $this->actingAs($this->karyawan, 'karyawan');

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertFalse($result['success']);
        $this->assertSame('Belum bisa presensi pulang! Minimal bekerja 8 jam.', $result['message']);
        $this->assertSame('out', $result['type']);
    }

    public function test_absen_pulang_masih_diblokir_laporan_wfh_kosong(): void
    {
        $this->setTime('21:00:00');
        $this->presensiHariIni(null, '12:00:00');

        DB::table('wfhs')->insert([
            'nik' => $this->karyawan->nik,
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'tgl_wfh' => self::HARI_INI,
            'deskripsi_pekerjaan' => 'Kerja remote',
            'status' => 'approved',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'laporan_deskripsi' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->karyawan, 'karyawan');

        $result = app(PresensiService::class)->processPresensi($this->requestPresensi());

        $this->assertFalse($result['success']);
        $this->assertSame(
            'Anda harus mengupload laporan WFH terlebih dahulu sebelum presensi pulang.',
            $result['message']
        );
    }

    // ======================================================================
    // canSubmit — tanggal pengajuan hanya H (hari ini) atau H+1 (besok)
    // ======================================================================

    public function test_can_submit_tanggal_besok_diluar_window_tanpa_syarat_pulang(): void
    {
        $this->setTime('17:30:00');
        $this->presensiHariIni(null);

        $result = $this->lemburService->canSubmit($this->karyawan, '2026-09-29');

        $this->assertTrue($result['can']);
    }

    public function test_can_submit_tanggal_besok_duplikat_ditolak(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['tgl_lembur' => '2026-09-29']);

        $result = $this->lemburService->canSubmit($this->karyawan, '2026-09-29');

        $this->assertFalse($result['can']);
        $this->assertSame('Anda sudah mengajukan lembur pada tanggal tersebut.', $result['message']);
    }

    public function test_can_submit_tanggal_lampau_ditolak(): void
    {
        $this->setTime('10:00:00');

        $result = $this->lemburService->canSubmit($this->karyawan, '2026-09-20');

        $this->assertFalse($result['can']);
        $this->assertSame(
            'Pengajuan lembur hanya bisa untuk hari ini atau besok. Tanggal lampau tidak diperkenankan.',
            $result['message']
        );
    }

    public function test_can_submit_hplus2_ditolak(): void
    {
        $this->setTime('10:00:00');

        $result = $this->lemburService->canSubmit($this->karyawan, '2026-09-30');

        $this->assertFalse($result['can']);
        $this->assertSame(
            'Pengajuan lembur hanya bisa untuk hari ini atau besok. Tanggal lampau tidak diperkenankan.',
            $result['message']
        );
    }

    // ======================================================================
    // Approval diblokir setelah tanggal lembur lewat
    // ======================================================================

    public function test_approve_atasan_diblokir_setelah_tanggal_lembur_lewat(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'tgl_lembur' => '2026-09-27',
            'status' => 'pending_atasan',
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $result = $this->lemburService->approveLemburAtasan($lembur->id, $this->atasan);

        $this->assertFalse($result['success']);
        $this->assertSame('Tanggal lembur sudah lewat. Pengajuan ini hanya bisa ditolak.', $result['message']);
        $this->assertSame(LemburStatus::PendingAtasan, $lembur->fresh()->status);
    }

    public function test_approve_admin_diblokir_setelah_tanggal_lembur_lewat(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'tgl_lembur' => '2026-09-27',
            'status' => 'pending_admin',
            'atasan_status' => 'approved',
            'admin_status' => 'pending',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $result = $this->lemburService->approveLemburAdmin($lembur->id);

        $this->assertFalse($result['success']);
        $this->assertSame('Tanggal lembur sudah lewat. Pengajuan ini hanya bisa ditolak.', $result['message']);
        $this->assertSame(LemburStatus::PendingAdmin, $lembur->fresh()->status);
    }

    // ======================================================================
    // Hard cutoff foto & laporan: wajib hari-H (kecuali recovery HR)
    // ======================================================================

    public function test_get_foto_diblokir_di_luar_hari_h(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['tgl_lembur' => '2026-09-27']);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $data = $this->lemburService->getFotoData($lembur->id, $this->karyawan->nik);

        $this->assertObjectHasProperty('error', $data);
        $this->assertStringContainsString('Foto lembur hanya bisa diambil pada tanggal lembur', $data->error);
    }

    public function test_store_foto_diblokir_di_luar_hari_h(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['tgl_lembur' => '2026-09-27']);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $result = $this->lemburService->storeFoto(new Request(['type' => 'mulai']), $lembur->id, $this->karyawan->nik);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('hanya bisa diambil pada tanggal lembur', $result['message']);
    }

    public function test_foto_dibuka_setelah_recovery_admin(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'tgl_lembur' => '2026-09-27',
            'approved_at' => '2026-09-28 09:00:00',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $data = $this->lemburService->getFotoData($lembur->id, $this->karyawan->nik);

        $this->assertIsObject($data);
        $this->assertObjectNotHasProperty('error', $data);
    }

    public function test_get_laporan_diblokir_di_luar_hari_h(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'tgl_lembur' => '2026-09-27',
            'foto_mulai' => 'mulai.jpg',
            'foto_selesai' => 'selesai.jpg',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $data = $this->lemburService->getLaporanData($lembur->id, $this->karyawan->nik);

        $this->assertObjectHasProperty('error', $data);
        $this->assertStringContainsString('Laporan lembur hanya bisa dikirim pada tanggal lembur', $data->error);
    }

    public function test_store_laporan_diblokir_di_luar_hari_h(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'tgl_lembur' => '2026-09-27',
            'foto_mulai' => 'mulai.jpg',
            'foto_selesai' => 'selesai.jpg',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', '2026-09-27')->firstOrFail();

        $result = $this->lemburService->storeLaporanLembur(new Request, $lembur->id, $this->karyawan->nik);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('hanya bisa dikirim pada tanggal lembur', $result['message']);
    }

    public function test_get_laporan_menerima_status_unpaid(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni([
            'status' => 'unpaid',
            'foto_mulai' => 'mulai.jpg',
            'foto_selesai' => 'selesai.jpg',
        ]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('status', 'unpaid')->firstOrFail();

        $data = $this->lemburService->getLaporanData($lembur->id, $this->karyawan->nik);

        $this->assertIsObject($data);
        $this->assertObjectNotHasProperty('error', $data);
    }

    // ======================================================================
    // Riwayat karyawan: hanya data FINAL
    // ======================================================================

    public function test_get_lembur_history_menampilkan_status_unpaid(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'unpaid']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(1, $history);
        $this->assertSame(LemburStatus::Unpaid, $history->first()->status);
    }

    public function test_get_lembur_history_menampilkan_pengajuan_ditolak(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'rejected', 'rejected_reason' => 'Jadwal bentrok']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(1, $history);
        $this->assertSame(LemburStatus::Rejected, $history->first()->status);
    }

    public function test_get_lembur_history_menampilkan_laporan_disetujui(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'laporan_status' => 'approved']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(1, $history);
        $this->assertSame(LemburStatus::Approved, $history->first()->status);
        $this->assertSame(LemburStatus::Approved, $history->first()->laporan_status);
    }

    public function test_get_lembur_history_menampilkan_laporan_ditolak(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'laporan_status' => 'rejected']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(1, $history);
        $this->assertSame(LemburStatus::Rejected, $history->first()->laporan_status);
    }

    public function test_get_lembur_history_menyembunyikan_pending_pengajuan(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'pending_atasan']);
        $this->lemburHariIni(['status' => 'pending_admin', 'tgl_lembur' => '2026-09-27']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(0, $history);
    }

    public function test_get_lembur_history_menyembunyikan_approved_belum_kirim_laporan(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'laporan_status' => null]);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(0, $history);
    }

    public function test_get_lembur_history_menyembunyikan_laporan_menunggu_approval(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'laporan_status' => 'pending_atasan']);
        $this->lemburHariIni(['status' => 'approved', 'laporan_status' => 'pending_admin', 'tgl_lembur' => '2026-09-27']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(0, $history);
    }
}
