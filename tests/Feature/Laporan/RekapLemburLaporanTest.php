<?php

namespace Tests\Feature\Laporan;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\LaporanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class RekapLemburLaporanTest extends TestCase
{
    use RefreshDatabase;

    private const BULAN = 10;
    private const TAHUN = 2026;
    private const UNIT = 'Teknologi';

    // Cut-off bulan 10/2026 = 21 Sep 2026 s/d 20 Okt 2026
    private const TGL_DALAM_PERIODE = '2026-09-22';
    private const TGL_DI_LUAR_PERIODE = '2026-09-20';

    private Unitperusahaan $unit;
    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => self::UNIT,
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'RKP001',
            'nama_lengkap' => 'Karyawan Rekap',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => self::UNIT,
            'unit_id' => $this->unit->id,
            'no_hp' => '081234567999',
            'password' => bcrypt('password'),
        ]);
    }

    private function lembur(array $atribut = []): void
    {
        DB::table('lemburs')->insert(array_merge([
            'nik' => $this->karyawan->nik,
            'tgl_lembur' => self::TGL_DALAM_PERIODE,
            'keterangan' => 'Lembur rekap test',
            'durasi_jam' => 2,
            'status' => 'approved',
            'atasan_nik' => null,
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'laporan_deskripsi' => null,
            'laporan_status' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ], $atribut));
    }

    private function buildRekap(array $params)
    {
        $request = Request::create('/presensi/cetaklaporan', 'POST', $params);

        $method = new ReflectionMethod(LaporanService::class, 'buildUnitLaporanData');
        $method->setAccessible(true);
        $data = $method->invoke(null, $request);

        $this->assertIsArray($data, 'buildUnitLaporanData harus mengembalikan array, bukan redirect');

        return $data;
    }

    private function buildDetail(array $params)
    {
        $request = Request::create('/presensi/cetaklaporan', 'POST', $params);

        $method = new ReflectionMethod(LaporanService::class, 'buildLaporanData');
        $method->setAccessible(true);
        $data = $method->invoke(null, $request);

        $this->assertIsArray($data, 'buildLaporanData harus mengembalikan array, bukan redirect');

        return $data;
    }

    private function rekapParams(): array
    {
        return ['bulan' => self::BULAN, 'tahun' => self::TAHUN, 'unit' => self::UNIT];
    }

    private function detailParams(): array
    {
        return $this->rekapParams() + ['nik' => $this->karyawan->nik];
    }

    // ======================================================================
    // Rekap per karyawan (export "Per Perusahaan")
    // ======================================================================

    public function test_rekap_unit_menghitung_lembur_dengan_laporan_disetujui(): void
    {
        $this->lembur(['laporan_status' => 'approved']);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(2.0, (float) $data['dataKaryawan'][0]['totalLembur']);
        $this->assertSame(2.0, (float) $data['grandTotal']['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_laporan_menunggu_persetujuan(): void
    {
        $this->lembur(['laporan_status' => 'pending_atasan']);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_laporan_pending_admin(): void
    {
        $this->lembur(['laporan_status' => 'pending_admin']);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_laporan_belum_dikirim(): void
    {
        $this->lembur(['laporan_status' => null]);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_laporan_ditolak(): void
    {
        $this->lembur(['laporan_status' => 'rejected']);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_lembur_unpaid(): void
    {
        $this->lembur(['status' => 'unpaid', 'laporan_status' => 'approved']);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_pengajuan_ditolak(): void
    {
        $this->lembur(['status' => 'rejected', 'laporan_status' => null]);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_tidak_menghitung_lembur_di_luar_periode_cut_off(): void
    {
        $this->lembur([
            'tgl_lembur' => self::TGL_DI_LUAR_PERIODE,
            'laporan_status' => 'approved',
        ]);

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(0.0, (float) $data['dataKaryawan'][0]['totalLembur']);
    }

    public function test_rekap_unit_hanya_menjumlahkan_lembur_lolos_laporan(): void
    {
        $this->lembur(['laporan_status' => 'approved']);                                                    // 2 jam, dihitung
        $this->lembur(['tgl_lembur' => '2026-09-23', 'laporan_status' => 'pending_admin']);                 // tidak dihitung
        $this->lembur(['tgl_lembur' => '2026-09-24', 'laporan_status' => null]);                            // tidak dihitung
        $this->lembur(['tgl_lembur' => '2026-09-25', 'status' => 'unpaid', 'laporan_status' => 'approved']); // tidak dihitung

        $data = $this->buildRekap($this->rekapParams());

        $this->assertSame(2.0, (float) $data['dataKaryawan'][0]['totalLembur']);
        $this->assertSame(2.0, (float) $data['grandTotal']['totalLembur']);
    }

    // ======================================================================
    // Laporan detail 1 karyawan (export "Per Karyawan")
    // ======================================================================

    public function test_detail_karyawan_menghitung_lembur_dengan_laporan_disetujui(): void
    {
        $this->lembur(['laporan_status' => 'approved']);

        $data = $this->buildDetail($this->detailParams());

        $this->assertSame(2.0, (float) $data['totalLembur']);
    }

    public function test_detail_karyawan_tidak_menghitung_laporan_menunggu_persetujuan(): void
    {
        $this->lembur(['laporan_status' => 'pending_atasan']);

        $data = $this->buildDetail($this->detailParams());

        $this->assertSame(0.0, (float) $data['totalLembur']);
    }

    public function test_detail_karyawan_tidak_menghitung_lembur_unpaid(): void
    {
        $this->lembur(['status' => 'unpaid', 'laporan_status' => 'approved']);

        $data = $this->buildDetail($this->detailParams());

        $this->assertSame(0.0, (float) $data['totalLembur']);
    }

    public function test_detail_karyawan_laporan_disetujui_muncul_di_harian(): void
    {
        $this->lembur(['laporan_status' => 'approved']);

        $data = $this->buildDetail($this->detailParams());

        $hari = $this->cariHari($data, self::TGL_DALAM_PERIODE);
        $this->assertNotNull($hari);
        $this->assertSame('2', $hari['lembur_jam']);
    }

    public function test_detail_karyawan_laporan_menunggu_tidak_muncul_di_harian(): void
    {
        $this->lembur(['laporan_status' => 'pending_admin']);

        $data = $this->buildDetail($this->detailParams());

        $hari = $this->cariHari($data, self::TGL_DALAM_PERIODE);
        $this->assertNotNull($hari);
        $this->assertNull($hari['lembur_jam']);
    }

    private function cariHari(array $data, string $tanggal): ?array
    {
        return collect($data['days'])->first(
            fn (array $day) => $day['tanggal']->format('Y-m-d') === $tanggal
        );
    }
}
