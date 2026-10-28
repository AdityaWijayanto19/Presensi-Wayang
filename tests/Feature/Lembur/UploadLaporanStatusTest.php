<?php

namespace Tests\Feature\Lembur;

use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Unitperusahaan;
use App\Models\User;
use App\Notifications\LaporanLemburSubmitted;
use App\Notifications\LemburMarkedUnpaid;
use App\Notifications\LemburReminderLaporan;
use App\Notifications\LemburStatusChanged;
use App\Services\LemburService;
use App\Services\Shared\WebPushService;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Status jembatan "upload_laporan".
 *
 * Super_admin meminta karyawan mengunggah laporan lembur tanpa foto
 * (mulai/selesai). Cakupan yang diuji:
 *  - transisi status diizinkan/ditolak + reset isi laporan
 *  - notifikasi DB custom + web push yang mengarah ke halaman laporan
 *  - pengiriman laporan: syarat foto & hari-H dilewati, atasan dilewati
 *  - status kembali "approved" begitu laporan terkirim
 *  - cron 22:00 (reminder) & 00:00 (mark-unpaid) tetap mengawasi
 *  - polling realtime karyawan & halaman foto yang tertutup
 *  - permission update tetap hanya untuk super_admin
 */
class UploadLaporanStatusTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-28';

    private const KEMARIN = '2026-09-27';

    private Unitperusahaan $unit;

    private Karyawan $atasan;

    private Karyawan $karyawan;

    private User $superAdmin;

    private LemburService $lemburService;

    /** @var object Spy WebPushService: mencatat panggilan push tanpa kirim sungguhan. */
    private object $pushSpy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->atasan = Karyawan::create([
            'nik' => 'UPLATS001',
            'nama_lengkap' => 'Atasan Upload Laporan',
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
            'nik' => 'UPL001',
            'nama_lengkap' => 'Karyawan Upload Laporan',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => 'UPLATS001',
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567802',
            'password' => bcrypt('password'),
        ]);

        $this->superAdmin = User::factory()->create([
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
        ]);
        $this->superAdmin->assignRole('super_admin');

        // Spy menangkap URL tujuan push — push asli tidak pernah terkirim di test.
        $this->pushSpy = new class extends WebPushService {
            /** @var array<int, array{nik: string, title: string, body: string, url: ?string, tag: ?string}> */
            public array $calls = [];

            public function send(string $nik, string $title, string $body, ?string $url = null, ?string $tag = null): void
            {
                $this->calls[] = compact('nik', 'title', 'body', 'url', 'tag');
            }
        };
        $this->app->instance(WebPushService::class, $this->pushSpy);

        $this->lemburService = app(LemburService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI . ' ' . $jam, 'Asia/Jakarta'));
    }

    private function buatLembur(array $atribut = []): int
    {
        return DB::table('lemburs')->insertGetId(array_merge([
            'nik' => $this->karyawan->nik,
            'tgl_lembur' => self::HARI_INI,
            'keterangan' => 'Lembur test',
            'status' => 'approved',
            'atasan_nik' => 'UPLATS001',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'approved_at' => self::HARI_INI . ' 17:00:00',
            'durasi_jam' => 2,
            'laporan_deskripsi' => null,
            'laporan_status' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    /**
     * Kirim update status lembur memakai nilai yang sudah ada supaya tidak ada
     * field lain yang berubah (regenerate PDF form tidak ikut terpicu).
     */
    private function updateStatusLembur(int $id, string $status): TestResponse
    {
        $raw = DB::table('lemburs')->where('id', $id)->firstOrFail();

        return $this->actingAs($this->superAdmin, 'user')
            ->post('/presensi/lembur/' . $id . '/update', [
                'tgl_lembur' => substr($raw->tgl_lembur, 0, 10),
                'keterangan' => $raw->keterangan,
                'status' => $status,
                'durasi_jam' => $raw->durasi_jam ?? 2,
            ]);
    }

    private function barisLembur(int $id): object
    {
        return DB::table('lemburs')->where('id', $id)->firstOrFail();
    }

    // ======================================================================
    // 1. Transisi status oleh super_admin
    // ======================================================================

    public function test_update_ke_upload_laporan_dari_approved_mereset_isi_laporan(): void
    {
        $id = $this->buatLembur([
            'laporan_deskripsi' => 'Laporan lama',
            'laporan_status' => 'rejected',
            'laporan_file' => 'lembur/laporan/lama.pdf',
            'laporan_images' => json_encode(['lembur/laporan/lama.jpg']),
            'laporan_approved_at' => self::HARI_INI . ' 20:00:00',
            'laporan_atasan_nik' => 'UPLATS001',
            'laporan_atasan_status' => 'rejected',
            'laporan_admin_status' => 'rejected',
            'laporan_rejected_reason' => 'Kurang detail',
            'foto_mulai' => 'mulai.jpg',
            'foto_selesai' => 'selesai.jpg',
        ]);

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('success');

        $row = $this->barisLembur($id);
        $this->assertSame('upload_laporan', $row->status);
        $this->assertSame('approved', $row->admin_status);

        // Isi laporan lama dibersihkan supaya pengiriman berikutnya jadi laporan baru.
        $this->assertNull($row->laporan_status);
        $this->assertNull($row->laporan_deskripsi);
        $this->assertNull($row->laporan_file);
        $this->assertNull($row->laporan_images);
        $this->assertNull($row->laporan_approved_at);
        $this->assertNull($row->laporan_atasan_nik);
        $this->assertNull($row->laporan_atasan_status);
        $this->assertNull($row->laporan_admin_status);
        $this->assertNull($row->laporan_rejected_reason);

        // Foto TIDAK direset dan approved_at TIDAK disentuh (tetap bukan jalur recovery).
        $this->assertSame('mulai.jpg', $row->foto_mulai);
        $this->assertSame('selesai.jpg', $row->foto_selesai);
        $this->assertSame(self::HARI_INI . ' 17:00:00', $row->approved_at);
    }

    public function test_update_ke_upload_laporan_dari_unpaid_diizinkan(): void
    {
        $id = $this->buatLembur(['status' => 'unpaid']);

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('success');
        $row = $this->barisLembur($id);
        $this->assertSame('upload_laporan', $row->status);
        $this->assertSame('approved', $row->admin_status);
    }

    public function test_update_ke_upload_laporan_ditolak_dari_pending_atasan(): void
    {
        $id = $this->buatLembur([
            'status' => 'pending_atasan',
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
        ]);

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('error');
        $row = $this->barisLembur($id);
        $this->assertSame('pending_atasan', $row->status);
        $this->assertSame('pending', $row->admin_status);
    }

    public function test_update_ke_upload_laporan_ditolak_sa_at_laporan_masih_dalam_approval(): void
    {
        $id = $this->buatLembur([
            'laporan_deskripsi' => 'Laporan sedang direview',
            'laporan_status' => 'pending_atasan',
            'laporan_atasan_nik' => 'UPLATS001',
        ]);

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('error');
        $row = $this->barisLembur($id);
        $this->assertSame('approved', $row->status);
        $this->assertSame('pending_atasan', $row->laporan_status);
        $this->assertSame('Laporan sedang direview', $row->laporan_deskripsi);
    }

    public function test_update_ke_upload_laporan_ditolak_sa_at_laporan_menunggu_hr(): void
    {
        $id = $this->buatLembur([
            'laporan_deskripsi' => 'Laporan menunggu HR',
            'laporan_status' => 'pending_admin',
            'laporan_atasan_nik' => null,
        ]);

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('error');
        $this->assertSame('pending_admin', $this->barisLembur($id)->laporan_status);
    }

    // ======================================================================
    // 2. Notifikasi karyawan + web push
    // ======================================================================

    public function test_status_upload_laporan_memicu_notifikasi_custom_dan_push_ke_halaman_laporan(): void
    {
        Notification::fake();
        $id = $this->buatLembur();

        $response = $this->updateStatusLembur($id, 'upload_laporan');

        $response->assertSessionHas('success');

        Notification::assertSentTo(
            $this->karyawan,
            LemburStatusChanged::class,
            function (LemburStatusChanged $n) use ($id) {
                $data = $n->toDatabase($this->karyawan);

                return $n->newStatus === 'upload_laporan'
                    && $data['lembur_id'] === $id
                    && str_contains($data['message'], 'tanpa perlu foto');
            }
        );

        // Push diarahkan persis ke halaman laporan lembur terkait.
        $this->assertCount(1, $this->pushSpy->calls);
        $push = $this->pushSpy->calls[0];
        $this->assertSame('/lembur/' . $id . '/laporan', $push['url']);
        $this->assertSame('Mohon Upload Laporan Lembur', $push['title']);
    }

    public function test_perubahan_status_lain_memakai_pesan_baku_dan_push_umum(): void
    {
        Notification::fake();
        $id = $this->buatLembur();

        $response = $this->updateStatusLembur($id, 'rejected');

        $response->assertSessionHas('success');

        Notification::assertSentTo(
            $this->karyawan,
            LemburStatusChanged::class,
            fn (LemburStatusChanged $n) => str_contains(
                $n->toDatabase($this->karyawan)['message'],
                'Status lembur berubah dari'
            )
        );

        $this->assertCount(1, $this->pushSpy->calls);
        $this->assertSame('/lembur', $this->pushSpy->calls[0]['url']);
        $this->assertSame('Status Lembur', $this->pushSpy->calls[0]['title']);
    }

    // ======================================================================
    // 3. Permission: approval/ubah status tetap hanya super_admin
    // ======================================================================

    public function test_role_admin_tanpa_presensi_edit_tidak_bisa_update_status(): void
    {
        $adminBiasa = User::factory()->create([
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
        ]);
        $adminBiasa->assignRole('admin');

        $id = $this->buatLembur();
        $raw = DB::table('lemburs')->where('id', $id)->first();

        $this->actingAs($adminBiasa, 'user')
            ->post('/presensi/lembur/' . $id . '/update', [
                'tgl_lembur' => substr($raw->tgl_lembur, 0, 10),
                'keterangan' => $raw->keterangan,
                'status' => 'upload_laporan',
                'durasi_jam' => $raw->durasi_jam,
            ])
            ->assertForbidden();

        $this->assertSame('approved', $this->barisLembur($id)->status);
    }

    // ======================================================================
    // 4. Halaman foto tertutup, halaman laporan terbuka
    // ======================================================================

    public function test_halaman_foto_tertutup_sa_at_status_upload_laporan(): void
    {
        $id = $this->buatLembur(['status' => 'upload_laporan']);

        $this->assertNull($this->lemburService->getFotoData($id, $this->karyawan->nik));
    }

    public function test_get_laporan_data_menerima_upload_laporan_tanpa_foto_dan_di_luar_hari_h(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $data = $this->lemburService->getLaporanData($id, $this->karyawan->nik);

        $this->assertIsObject($data);
        $this->assertObjectNotHasProperty('error', $data);
    }

    // ======================================================================
    // 5. Pengiriman laporan lewat jalur upload_laporan
    // ======================================================================

    public function test_store_laporan_upload_laporan_tanpa_foto_di_luar_hari_h_berhasil(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $result = $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Finalisasi laporan tanpa foto']),
            $id,
            $this->karyawan->nik
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');
    }

    public function test_store_laporan_upload_laporan_mengembalikan_status_ke_approved(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Finalisasi laporan']),
            $id,
            $this->karyawan->nik
        );

        $row = $this->barisLembur($id);
        $this->assertSame('approved', $row->status);
        $this->assertSame('Finalisasi laporan', $row->laporan_deskripsi);
    }

    public function test_store_laporan_upload_laporan_langsung_ke_antrean_super_admin(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $result = $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Finalisasi laporan']),
            $id,
            $this->karyawan->nik
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');

        $row = $this->barisLembur($id);
        // Langsung ke HR: atasan tidak dilibatkan sama sekali.
        $this->assertSame('pending_admin', $row->laporan_status);
        $this->assertNull($row->laporan_atasan_nik);
        $this->assertSame('approved', $row->laporan_atasan_status);
        $this->assertSame('pending', $row->laporan_admin_status);
    }

    public function test_store_laporan_upload_laporan_tidak_memberitahu_atasan(): void
    {
        Notification::fake();
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Finalisasi laporan']),
            $id,
            $this->karyawan->nik
        );

        Notification::assertNotSentTo($this->atasan, LaporanLemburSubmitted::class);
        Notification::assertNotSentTo($this->karyawan, LaporanLemburSubmitted::class);
    }

    public function test_store_laporan_jalur_normal_masih_mewajibkan_foto(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::HARI_INI,
            'status' => 'approved',
        ]);

        $result = $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Tanpa foto']),
            $id,
            $this->karyawan->nik
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Foto mulai dan selesai', $result['message']);
        $this->assertSame('approved', $this->barisLembur($id)->status);
        $this->assertNull($this->barisLembur($id)->laporan_status);
    }

    public function test_approve_laporan_atasan_menolak_laporan_hasil_upload_laporan(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);
        $this->lemburService->storeLaporanLembur(
            new Request(['deskripsi_pekerjaan' => 'Finalisasi laporan']),
            $id,
            $this->karyawan->nik
        );

        $result = $this->lemburService->approveLaporanAtasan($id, $this->atasan);

        $this->assertFalse($result['success']);
        // Laporan tetap di antrean HR — atasan tidak punya akses di jalur ini.
        $this->assertSame('pending_admin', $this->barisLembur($id)->laporan_status);
    }

    // ======================================================================
    // 6. Cron 22:00 (reminder) dan 00:00 (mark-unpaid)
    // ======================================================================

    public function test_reminder_2200_mencakup_status_upload_laporan(): void
    {
        Notification::fake();
        $this->setTime('21:59:00');
        $id = $this->buatLembur([
            'tgl_lembur' => self::HARI_INI,
            'status' => 'upload_laporan',
        ]);

        $this->artisan('lembur:reminder-laporan')->assertExitCode(0);

        Notification::assertSentTo($this->karyawan, LemburReminderLaporan::class);
        $this->assertSame('/lembur/' . $id . '/laporan', $this->pushSpy->calls[0]['url']);
    }

    public function test_mark_unpaid_0000_mencakup_status_upload_laporan(): void
    {
        Notification::fake();
        $this->setTime('00:00:05');
        $id = $this->buatLembur([
            'tgl_lembur' => self::KEMARIN,
            'status' => 'upload_laporan',
        ]);

        $this->artisan('lembur:mark-unpaid')->assertExitCode(0);

        $this->assertSame('unpaid', $this->barisLembur($id)->status);
        Notification::assertSentTo(
            $this->karyawan,
            LemburMarkedUnpaid::class,
            fn (LemburMarkedUnpaid $n) => $n->reason === 'belum upload laporan'
        );
    }

    // ======================================================================
    // 7. Polling dashboard karyawan
    // ======================================================================

    public function test_realtime_dashboard_menampilkan_lembur_status_upload_laporan(): void
    {
        $this->setTime('10:00:00');
        $id = $this->buatLembur(['status' => 'upload_laporan']);

        $response = $this->actingAs($this->karyawan, 'karyawan')
            ->getJson('/api/realtime/dashboard');

        $response->assertOk();

        $lemburSaya = collect($response->json('lemburSaya'));
        $this->assertTrue(
            $lemburSaya->contains(fn ($l) => $l['id'] === $id && $l['status'] === 'upload_laporan')
        );
    }
}
