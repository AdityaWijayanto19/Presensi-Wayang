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
    // canSubmit — kuota per hari, window jam, dan cek presensi
    // Catatan: syarat "harus absen pulang dulu sebelum ajukan lembur" sudah
    // DINONAKTIFKAN sejak commit 7986513, jadi presensi dengan jam_out kosong
    // tidak lagi memblokir pengajuan lembur.
    // ======================================================================

    public function test_can_submit_tidak_diblokir_walau_presensi_belum_pulang(): void
    {
        $this->setTime('18:30:00');
        $this->presensiHariIni(null);

        $result = $this->lemburService->canSubmit($this->karyawan);

        // Presensi hari ini ada tapi belum absen pulang: tetap boleh mengajukan
        // karena validasi absen pulang sudah dinonaktifkan.
        $this->assertTrue($result['can']);
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
        $this->lemburHariIni(['rencana_mulai' => self::HARI_INI.' 18:00:00', 'rencana_selesai' => self::HARI_INI.' 19:00:00', 'durasi_jam' => 1]);
        $this->lemburHariIni(['rencana_mulai' => self::HARI_INI.' 19:30:00', 'rencana_selesai' => self::HARI_INI.' 20:30:00', 'durasi_jam' => 1]);

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertFalse($result['can']);
        $this->assertSame('Anda sudah mengajukan lembur 2x hari ini.', $result['message']);
    }

    public function test_can_submit_masih_boleh_dengan_satu_lembur_sudah_ada(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni(['rencana_mulai' => self::HARI_INI.' 18:00:00', 'rencana_selesai' => self::HARI_INI.' 20:00:00', 'durasi_jam' => 2]);

        $result = $this->lemburService->canSubmit($this->karyawan);

        $this->assertTrue($result['can']);
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
        $this->lemburHariIni(['tgl_lembur' => '2026-09-29', 'rencana_mulai' => '2026-09-29 18:00:00', 'rencana_selesai' => '2026-09-29 19:00:00', 'durasi_jam' => 1]);
        $this->lemburHariIni(['tgl_lembur' => '2026-09-29', 'rencana_mulai' => '2026-09-29 19:30:00', 'rencana_selesai' => '2026-09-29 20:30:00', 'durasi_jam' => 1]);

        $result = $this->lemburService->canSubmit($this->karyawan, '2026-09-29');

        $this->assertFalse($result['can']);
        $this->assertSame('Anda sudah mengajukan lembur 2x pada tanggal tersebut.', $result['message']);
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

    // ======================================================================
    // 2x lembur per hari: kuota & validasi tumpang tindih
    // ======================================================================

    private function requestPengajuan(array $extra = []): Request
    {
        return new Request(array_merge([
            'tgl_lembur' => self::HARI_INI,
            'durasi_jam' => '2',
            'jam_mulai' => '18:00',
            'keterangan' => 'Lembur malam',
        ], $extra));
    }

    public function test_store_pengajuan_ke_2_dalam_satu_hari_diperbolehkan(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni([
            'rencana_mulai' => self::HARI_INI . ' 09:00:00',
            'rencana_selesai' => self::HARI_INI . ' 11:00:00',
            'durasi_jam' => 2,
        ]);

        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '18:30']),
            $this->karyawan
        );

        $this->assertTrue($result['message'] !== '' && $result['success'], $result['message']);
        $this->assertSame(2, Lembur::where('nik', $this->karyawan->nik)->count());
    }

    public function test_store_pengajuan_ke_3_dalam_satu_hari_ditolak(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni(['rencana_mulai' => self::HARI_INI . ' 09:00:00', 'rencana_selesai' => self::HARI_INI . ' 11:00:00', 'durasi_jam' => 2]);
        $this->lemburHariIni(['rencana_mulai' => self::HARI_INI . ' 18:00:00', 'rencana_selesai' => self::HARI_INI . ' 20:00:00', 'durasi_jam' => 2]);

        $result = $this->lemburService->storePengajuan($this->requestPengajuan(['jam_mulai' => '20:30']), $this->karyawan);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('mengajukan lembur 2x', $result['message']);
    }

    public function test_store_pengajuan_tumpang_tindih_ditolak(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni([
            'rencana_mulai' => self::HARI_INI . ' 18:00:00',
            'rencana_selesai' => self::HARI_INI . ' 20:00:00',
            'durasi_jam' => 2,
        ]);

        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '19:30']),
            $this->karyawan
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('bertabrakan', $result['message']);
        $this->assertSame(1, Lembur::where('nik', $this->karyawan->nik)->where('tgl_lembur', self::HARI_INI)->count());
    }

    public function test_store_pengajuan_prorate_dihitung_batas_55_jam_saat_cek_bentrok(): void
    {
        $this->setTime('18:30:00');
        // Sesi 1 prorate (tanpa jam selesai) mulai 18:00 → batas 23:30 (5,5 jam).
        $this->lemburHariIni([
            'rencana_mulai' => self::HARI_INI . ' 18:00:00',
            'rencana_selesai' => null,
            'durasi_jam' => 5.5,
        ]);

        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '22:00']),
            $this->karyawan
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('bertabrakan', $result['message']);
    }

    // ======================================================================
    // Rencana jam mulai bebas menit & sinkron dengan Rencana Jam Selesai
    // ======================================================================

    public function test_store_pengajuan_jam_mulai_bebeas_menit_rencana_selesai_sinkron(): void
    {
        $this->setTime('18:30:00');

        // Menit ganjil :45 diterima (tidak dibatasi :00/:30) — rencana selesai tetap sinkron.
        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '18:45', 'durasi_jam' => '2']),
            $this->karyawan
        );

        $this->assertTrue($result['success'], $result['message']);

        $lembur = Lembur::where('nik', $this->karyawan->nik)->firstOrFail();
        $this->assertSame(self::HARI_INI . ' 18:45:00', $lembur->rencana_mulai->format('Y-m-d H:i:s'));
        $this->assertSame(self::HARI_INI . ' 20:45:00', $lembur->rencana_selesai->format('Y-m-d H:i:s'));
    }

    public function test_store_pengajuan_jam_mulai_menit_ganjil_lewati_tengah_malam_sinkron(): void
    {
        $this->setTime('18:30:00');

        // 23:15 + 2 jam = 01:15 hari berikutnya (sinkron walau lewat tengah malam).
        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '23:15', 'durasi_jam' => '2']),
            $this->karyawan
        );

        $this->assertTrue($result['success'], $result['message']);

        $lembur = Lembur::where('nik', $this->karyawan->nik)->firstOrFail();
        $this->assertSame(self::HARI_INI . ' 23:15:00', $lembur->rencana_mulai->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-29 01:15:00', $lembur->rencana_selesai->format('Y-m-d H:i:s'));
    }

    public function test_store_pengajuan_menit_ganjil_tetap_validasi_tumpang_tindih(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni([
            'rencana_mulai' => self::HARI_INI . ' 18:45:00',
            'rencana_selesai' => self::HARI_INI . ' 20:45:00',
            'durasi_jam' => 2,
        ]);

        // 20:15 - 22:15 menimpa sesi 18:45 - 20:45 → tetap ditolak.
        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '20:15']),
            $this->karyawan
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('bertabrakan', $result['message']);
        $this->assertSame(1, Lembur::where('nik', $this->karyawan->nik)->count());
    }

    public function test_store_pengajuan_menit_ganjil_setelah_sesi_pertama_diperbolehkan(): void
    {
        $this->setTime('18:30:00');
        $this->lemburHariIni([
            'rencana_mulai' => self::HARI_INI . ' 18:45:00',
            'rencana_selesai' => self::HARI_INI . ' 20:45:00',
            'durasi_jam' => 2,
        ]);

        // Mulai tepat setelah sesi 1 selesai (20:45) → boleh.
        $result = $this->lemburService->storePengajuan(
            $this->requestPengajuan(['jam_mulai' => '20:45', 'durasi_jam' => '1.5']),
            $this->karyawan
        );

        $this->assertTrue($result['success'], $result['message']);
        $this->assertSame(2, Lembur::where('nik', $this->karyawan->nik)->count());

        $baru = Lembur::where('nik', $this->karyawan->nik)
            ->where('rencana_mulai', self::HARI_INI . ' 20:45:00')
            ->firstOrFail();
        $this->assertSame(self::HARI_INI . ' 22:15:00', $baru->rencana_selesai->format('Y-m-d H:i:s'));
    }

    // ======================================================================
    // Kalkulasi harian: total menit/jam & penanda Prorate (> 5 jam/hari)
    // ======================================================================

    public function test_summarize_daily_menjumlahkan_dua_sesi(): void
    {
        $summary = LemburService::summarizeDaily([
            (object) ['durasi_menit' => 120, 'durasi_jam' => 2],
            (object) ['durasi_menit' => 90, 'durasi_jam' => 1.5],
        ]);

        $this->assertSame(210, $summary['menit']);
        $this->assertSame(3.5, $summary['jam']);
        $this->assertFalse($summary['is_prorate']);
        $this->assertSame(2, $summary['jumlah']);
        $this->assertSame('3.5 jam', $summary['formatted']);
    }

    public function test_summarize_daily_lebih_dari_5_jam_jadi_prorate(): void
    {
        $summary = LemburService::summarizeDaily([
            (object) ['durasi_menit' => 180, 'durasi_jam' => 3],
            (object) ['durasi_menit' => 150, 'durasi_jam' => 2.5],
        ]);

        $this->assertSame(330, $summary['menit']);
        $this->assertTrue($summary['is_prorate']);
        $this->assertSame('prorate', $summary['formatted']);
    }

    public function test_summarize_daily_tepat_5_jam_bukan_prorate(): void
    {
        $summary = LemburService::summarizeDaily([
            (object) ['durasi_menit' => 150, 'durasi_jam' => 2.5],
            (object) ['durasi_menit' => 150, 'durasi_jam' => 2.5],
        ]);

        $this->assertFalse($summary['is_prorate']);
    }

    public function test_summarize_daily_pakai_durasi_menit_aktual_jika_ada(): void
    {
        // durasi_menit aktual (100 menit) menang atas durasi_jam rencana (2 jam).
        $summary = LemburService::summarizeDaily([
            (object) ['durasi_menit' => 100, 'durasi_jam' => 2],
        ]);

        $this->assertSame(100, $summary['menit']);
    }

    public function test_attach_daily_totals_anotasi_per_tanggal(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'durasi_jam' => 3, 'laporan_status' => 'approved']);
        $this->lemburHariIni(['status' => 'approved', 'durasi_jam' => 3, 'laporan_status' => 'rejected']);
        $this->lemburHariIni(['tgl_lembur' => '2026-09-27', 'status' => 'approved', 'durasi_jam' => 2, 'laporan_status' => 'approved']);

        $history = $this->lemburService->getLemburHistory($this->karyawan->nik);

        $this->assertCount(3, $history);
        foreach ($history as $item) {
            if ($item->tgl_lembur->format('Y-m-d') === self::HARI_INI) {
                $this->assertSame(2, $item->jumlah_sesi_harian);
                $this->assertTrue($item->is_prorate_harian);
                $this->assertSame('prorate', $item->durasi_harian_formatted);
                $this->assertSame(360, $item->durasi_harian_menit);
            } else {
                $this->assertSame(1, $item->jumlah_sesi_harian);
                $this->assertFalse($item->is_prorate_harian);
                $this->assertSame('2 jam', $item->durasi_harian_formatted);
            }
        }
    }

    public function test_daily_summary_for_hanya_menghitung_lembur_disetujui(): void
    {
        $this->setTime('10:00:00');
        $this->lemburHariIni(['status' => 'approved', 'durasi_jam' => 3]);
        $this->lemburHariIni(['status' => 'pending_atasan', 'durasi_jam' => 3]);
        $lembur = Lembur::where('nik', $this->karyawan->nik)->where('status', 'approved')->firstOrFail();

        $summary = LemburService::dailySummaryFor($lembur);

        $this->assertSame(1, $summary['jumlah']);
        $this->assertSame(180, $summary['menit']);
        $this->assertFalse($summary['is_prorate']);
    }
}
