<?php

namespace Tests\Feature\Laporan;

use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Unitperusahaan;
use App\Models\User;
use App\Services\LemburService;
use App\Services\LaporanLemburService;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaporanLemburGabungTest extends TestCase
{
    use RefreshDatabase;

    private const BULAN = 10;
    private const TAHUN = 2026;
    private const UNIT = 'Teknologi';
    private const UNIT_LAIN = 'Keuangan';

    // Cut-off bulan 10/2026 = 21 Sep 2026 s/d 20 Okt 2026
    private const TGL_DALAM_PERIODE = '2026-09-22';
    private const TGL_DALAM_PERIODE_2 = '2026-09-25';
    private const TGL_DI_LUAR_PERIODE = '2026-09-20';

    private Unitperusahaan $unit;
    private Unitperusahaan $unitLain;
    private Karyawan $karyawanA;
    private Karyawan $karyawanB;
    private Karyawan $karyawanLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => self::UNIT,
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->unitLain = Unitperusahaan::create([
            'unit' => self::UNIT_LAIN,
            'perusahaan' => 'PT Lain',
            'jam_masuk' => '08:00:00',
        ]);

        $this->karyawanA = $this->buatKaryawan('RKP001', 'Aditya Putra', self::UNIT);
        $this->karyawanB = $this->buatKaryawan('RKP002', 'Budi Santoso', self::UNIT);
        $this->karyawanLain = $this->buatKaryawan('RKP900', 'Citra Dewi', self::UNIT_LAIN);
    }

    private function buatKaryawan(string $nik, string $nama, string $unit): Karyawan
    {
        return Karyawan::create([
            'nik' => $nik,
            'nama_lengkap' => $nama,
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => $unit,
            'unit_id' => Unitperusahaan::where('unit', $unit)->value('id'),
            'no_hp' => '081234567999',
            'password' => bcrypt('password'),
        ]);
    }

    private function lembur(array $atribut = []): void
    {
        DB::table('lemburs')->insert(array_merge([
            'nik' => $this->karyawanA->nik,
            'tgl_lembur' => self::TGL_DALAM_PERIODE,
            'keterangan' => 'Lembur gabungan test',
            'durasi_jam' => 2,
            'status' => 'approved',
            'atasan_nik' => null,
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'laporan_deskripsi' => 'Mengerjakan fitur laporan',
            'laporan_status' => 'approved',
            'laporan_atasan_status' => 'approved',
            'laporan_admin_status' => 'approved',
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    private function build(array $params)
    {
        $request = Request::create('/presensi/lembur/cetaklaporan', 'POST', $params);

        return (new LaporanLemburService())->buildGabunganData($request);
    }

    private function params(array $extra = []): array
    {
        return array_merge([
            'bulan' => self::BULAN,
            'tahun' => self::TAHUN,
            'unit' => self::UNIT,
            'niks' => [$this->karyawanA->nik],
        ], $extra);
    }

    private function item(array $data, string $nik): ?array
    {
        return collect($data['daftar'])->first(fn (array $item) => $item['nik'] === $nik);
    }

    // ======================================================================
    // Filter periode cut-off & status
    // ======================================================================

    public function test_laporan_disetujui_dalam_periode_masuk_ke_gabungan(): void
    {
        $this->lembur(['tgl_lembur' => self::TGL_DALAM_PERIODE, 'durasi_jam' => 2]);
        $this->lembur(['tgl_lembur' => self::TGL_DALAM_PERIODE_2, 'durasi_jam' => 3]);

        $data = $this->build($this->params());

        $this->assertIsArray($data);
        $this->assertCount(1, $data['daftar']);
        $this->assertSame(2, $data['totalLaporan']);
        $this->assertSame(5.0, $data['totalDurasi']);
        $this->assertSame(2, $this->item($data, $this->karyawanA->nik)['jumlah']);
    }

    public function test_laporan_menunggu_persetujuan_tidak_masuk(): void
    {
        $this->lembur(['laporan_status' => 'pending_admin']);

        $data = $this->build($this->params());

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_laporan_ditolak_tidak_masuk(): void
    {
        $this->lembur(['laporan_status' => 'rejected']);

        $data = $this->build($this->params());

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_lembur_unpaid_tidak_masuk(): void
    {
        $this->lembur(['status' => 'unpaid', 'laporan_status' => 'approved']);

        $data = $this->build($this->params());

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_lembur_di_luar_periode_cut_off_tidak_masuk(): void
    {
        $this->lembur(['tgl_lembur' => self::TGL_DI_LUAR_PERIODE, 'laporan_status' => 'approved']);

        $data = $this->build($this->params());

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_periode_cut_off_sama_dengan_rekap_laporan_presensi(): void
    {
        $this->lembur();

        $data = $this->build($this->params());

        $this->assertSame('2026-09-21', $data['startDate']->format('Y-m-d'));
        $this->assertSame('2026-10-20', $data['endDate']->format('Y-m-d'));
    }

    // ======================================================================
    // Scoping unit & karyawan terpilih
    // ======================================================================

    public function test_hanya_karyawan_terpilih_yang_masuk(): void
    {
        $this->lembur(['nik' => $this->karyawanA->nik, 'tgl_lembur' => self::TGL_DALAM_PERIODE]);
        $this->lembur(['nik' => $this->karyawanB->nik, 'tgl_lembur' => self::TGL_DALAM_PERIODE]);

        $data = $this->build($this->params());

        $this->assertCount(1, $data['daftar']);
        $this->assertSame($this->karyawanA->nik, $data['daftar'][0]['nik']);
    }

    public function test_karyawan_dari_unit_lain_tidak_masuk(): void
    {
        $this->lembur(['nik' => $this->karyawanA->nik, 'tgl_lembur' => self::TGL_DALAM_PERIODE]);
        $this->lembur(['nik' => $this->karyawanLain->nik, 'tgl_lembur' => self::TGL_DALAM_PERIODE]);

        $data = $this->build($this->params([
            'niks' => [$this->karyawanA->nik, $this->karyawanLain->nik],
        ]));

        $this->assertCount(1, $data['daftar']);
        $this->assertSame($this->karyawanA->nik, $data['daftar'][0]['nik']);
    }

    public function test_karyawan_tanpa_laporan_tetap_tercantum_dengan_jumlah_nol(): void
    {
        $this->lembur(['nik' => $this->karyawanA->nik, 'tgl_lembur' => self::TGL_DALAM_PERIODE]);

        $data = $this->build($this->params([
            'niks' => [$this->karyawanA->nik, $this->karyawanB->nik],
        ]));

        $this->assertCount(2, $data['daftar']);
        $this->assertSame(0, $this->item($data, $this->karyawanB->nik)['jumlah']);
        $this->assertSame(1, $data['totalLaporan']);
    }

    // ======================================================================
    // Validasi & urutan
    // ======================================================================

    public function test_redirect_ketika_filter_belum_lengkap(): void
    {
        $data = $this->build(['bulan' => self::BULAN, 'tahun' => self::TAHUN]);

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_redirect_ketika_tidak_ada_laporan_disetujui(): void
    {
        $data = $this->build($this->params());

        $this->assertInstanceOf(RedirectResponse::class, $data);
    }

    public function test_laporan_diurutkan_berdasarkan_tanggal(): void
    {
        $this->lembur(['tgl_lembur' => self::TGL_DALAM_PERIODE_2, 'durasi_jam' => 3]);
        $this->lembur(['tgl_lembur' => self::TGL_DALAM_PERIODE, 'durasi_jam' => 2]);

        $data = $this->build($this->params());
        $reports = $this->item($data, $this->karyawanA->nik)['reports'];

        $this->assertCount(2, $reports);
        $this->assertSame(
            self::TGL_DALAM_PERIODE,
            \Carbon\Carbon::parse($reports[0]['tgl_lembur'])->format('Y-m-d')
        );
    }

    // ======================================================================
    // Render view
    // ======================================================================

    public function test_view_gabungan_bisa_dirender(): void
    {
        $this->lembur();
        $data = $this->build($this->params());

        $html = view('admin.presensi.cetaklaporan-lembur-gabung', $data)->render();

        $this->assertStringContainsString('Laporan Lembur', $html);
        $this->assertStringContainsString($this->karyawanA->nama_lengkap, $html);
        $this->assertStringContainsString('Periode Cut-off', $html);
    }

    public function test_pdf_gabungan_terdiri_dari_lebih_dari_satu_halaman(): void
    {
        $this->lembur();
        $data = $this->build($this->params());

        $content = Pdf::loadView('admin.presensi.cetaklaporan-lembur-gabung', $data)->output();

        $this->assertStringStartsWith('%PDF', $content);
        $this->assertGreaterThanOrEqual(
            2,
            preg_match_all('/\/Type\s*\/Page[^s]/', $content),
            'PDF gabungan harus berisi halaman cover + halaman laporan'
        );
    }

    public function test_view_laporan_tunggal_masih_bisa_dirender_setelah_refactor(): void
    {
        $this->lembur();
        $lembur = Lembur::where('nik', $this->karyawanA->nik)->firstOrFail();

        $pdfData = LemburService::buildLaporanPdfData($lembur);
        $pdfData['stempelPath'] = null;

        $html = view('admin.presensi.laporan-lembur-pdf', $pdfData)->render();

        $this->assertStringContainsString('Laporan Hasil Pekerjaan', $html);
        $this->assertStringContainsString($this->karyawanA->nama_lengkap, $html);
        $this->assertStringContainsString('Mengerjakan fitur laporan', $html);
    }

    public function test_build_laporan_pdf_data_menggunakan_atasan_laporan(): void
    {
        $this->lembur(['laporan_atasan_nik' => $this->karyawanB->nik]);
        $lembur = Lembur::where('nik', $this->karyawanA->nik)->firstOrFail();

        $pdfData = LemburService::buildLaporanPdfData($lembur);

        $this->assertSame($this->karyawanB->nik, $lembur->laporan_atasan_nik);
        $this->assertSame($this->karyawanB->nama_lengkap, $pdfData['nama_atasan']);
        $this->assertSame('PT Test', $pdfData['perusahaan']);
        $this->assertSame('Mengerjakan fitur laporan', $pdfData['deskripsi_pekerjaan']);
    }

    // ======================================================================
    // Route / permission
    // ======================================================================

    public function test_route_cetak_mengembalikan_pdf(): void
    {
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $user = $this->buatUserSuperAdmin();
        $this->lembur();

        $response = $this->actingAs($user, 'user')->post('/presensi/lembur/cetaklaporan', $this->params());

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Laporan_Lembur_', (string) $response->headers->get('content-disposition'));
    }

    public function test_route_preview_mengembalikan_pdf(): void
    {
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $user = $this->buatUserSuperAdmin();
        $this->lembur();

        $response = $this->actingAs($user, 'user')->post('/presensi/lembur/previewlaporan', $this->params());

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
    }

    public function test_route_ditolak_tanpa_permission_lembur_view(): void
    {
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $unit = Unitperusahaan::first();
        $user = User::factory()->create(['unit' => $unit->unit, 'unit_id' => $unit->id]);
        $this->lembur();

        $this->actingAs($user, 'user')
            ->post('/presensi/lembur/cetaklaporan', $this->params())
            ->assertForbidden();
    }

    public function test_route_getkaryawanbyunit_bisa_dipakai_dari_halaman_lembur(): void
    {
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $user = $this->buatUserSuperAdmin();

        $response = $this->actingAs($user, 'user')->post('/getkaryawanbyunit', ['unit' => self::UNIT]);

        $response->assertOk();
        $this->assertCount(2, $response->json());
    }

    public function test_halaman_panel_lembur_dengan_form_cetak_bisa_dirender(): void
    {
        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $user = $this->buatUserSuperAdmin();

        $response = $this->actingAs($user, 'user')->get('/panel/lembur');

        $response->assertOk();
        $response->assertSee('Cetak Laporan Lembur (Periode Cut-off)');
        $response->assertSee('/presensi/lembur/previewlaporan', false);
        $response->assertSee('/presensi/lembur/cetaklaporan', false);
    }

    private function buatUserSuperAdmin(): User
    {
        $unit = Unitperusahaan::first();
        $user = User::factory()->create(['unit' => $unit->unit, 'unit_id' => $unit->id]);
        $user->assignRole('super_admin');

        return $user;
    }
}
