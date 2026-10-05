<?php

namespace Tests\Feature\Presensi;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitlokasi;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\PresensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeofenceTest extends TestCase
{
    use RefreshDatabase;

    private const HARI_INI = '2026-09-30';

    private Unitperusahaan $unit;

    private Karyawan $karyawan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unit = Unitperusahaan::create([
            'unit' => 'Teknologi',
            'perusahaan' => 'PT Test',
            'jam_masuk' => '08:00:00',
            'radius_meter' => 100,
        ]);

        $this->karyawan = Karyawan::create([
            'nik' => 'GEO001',
            'nama_lengkap' => 'Karyawan Geofence',
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234000001',
            'password' => bcrypt('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ======================================================================
    // Presensi masuk
    // ======================================================================

    public function test_masuk_dalam_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertSame('in', $result['type']);
        $this->assertTrue($this->adaPresensi());
    }

    public function test_masuk_luar_radius_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('-6.2020,106.8000');

        $this->assertFalse($result['success']);
        $this->assertSame('in', $result['type']);
        $this->assertStringContainsString('berada', $result['message']);
        $this->assertStringContainsString('radius 100 m', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_di_antara_dua_lokasi_ditolak(): void
    {
        $this->titikLokasi('Kantor A', -6.2000, 106.8000);
        $this->titikLokasi('Kantor B', -6.2100, 106.8100);
        $this->setTime('08:00:00');

        // Titik tengah antara A dan B, jauh dari radius keduanya.
        $result = $this->prosesPresensi('-6.2050,106.8050');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_di_lokasi_kedua_berhasil(): void
    {
        $this->titikLokasi('Kantor A', -6.2000, 106.8000);
        $this->titikLokasi('Kantor B', -6.2100, 106.8100);
        $this->setTime('08:00:00');
        $this->mockFoto();

        // Di dekat lokasi B (bukan A).
        $result = $this->prosesPresensi('-6.2101,106.8100');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    public function test_masuk_unit_tanpa_lokasi_tetap_berhasil(): void
    {
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    // ======================================================================
    // Cabang WFH — bebas lokasi
    // ======================================================================

    public function test_masuk_wfh_approved_luar_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->wfhApprovedHariIni();
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->adaPresensi());
    }

    // ======================================================================
    // Presensi pulang
    // ======================================================================

    public function test_pulang_luar_radius_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->presensiHariIni();
        $this->setTime('17:30:00');

        $result = $this->prosesPresensi('-6.2020,106.8000');

        $this->assertFalse($result['success']);
        $this->assertSame('out', $result['type']);
        $this->assertStringContainsString('radius 100 m', $result['message']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertNull($presensi->jam_out);
    }

    public function test_pulang_dalam_radius_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->presensiHariIni();
        $this->setTime('17:30:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success']);
        $this->assertSame('out', $result['type']);

        $presensi = Presensi::where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', self::HARI_INI)
            ->first();
        $this->assertSame('17:30:00', $presensi->jam_out);
    }

    // ======================================================================
    // Bukti GPS (anti fake-GPS)
    // ======================================================================

    public function test_masuk_tanpa_bukti_gps_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('-6.2001,106.8000', [
            'akurasi' => null,
            'fix' => null,
            'durasi_ms' => null,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Data GPS tidak lengkap', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_akurasi_buruk_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('-6.2001,106.8000', ['akurasi' => 80]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('GPS belum akurat', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_pantauan_pendek_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('-6.2001,106.8000', [
            'fix' => 1,
            'durasi_ms' => 1000,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum stabil', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_masuk_fix_satu_ditolak_meski_durasi_penuh(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        // Durasi lewat tapi fix cuma 1 — HP diam sering cuma menghasilkan 1 fix
        // per event; tetap harus minimal 2 fix kumulatif.
        $result = $this->prosesPresensi('-6.2001,106.8000', [
            'fix' => 1,
            'durasi_ms' => 10000,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum stabil', $result['message']);
    }

    public function test_masuk_dengan_fix_minimal_dan_durasi_minimal_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');
        $this->mockFoto();

        // Tepat di ambang: 2 fix kumulatif + durasi 5 detik.
        $result = $this->prosesPresensi('-6.2001,106.8000', [
            'fix' => 2,
            'durasi_ms' => 5000,
        ]);

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertTrue($this->adaPresensi());
    }

    public function test_bukti_gps_disimpan(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success'], json_encode($result));

        $row = DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->first();
        $this->assertNotNull($row);
        $this->assertEqualsWithDelta(11.12, (float) $row->lokasi_in_jarak, 1.0);
        $this->assertEqualsWithDelta(10.0, (float) $row->lokasi_in_akurasi, 0.01);
        $this->assertSame(5, (int) $row->gps_fix_in);
        $this->assertSame(10000, (int) $row->gps_durasi_in_ms);
        $this->assertNotEmpty($row->ip_in);
        $this->assertNull($row->flag_manipulasi);
    }

    // ======================================================================
    // Fail-open tanpa bukti GPS (WFH & unit tanpa titik)
    // ======================================================================

    public function test_masuk_wfh_tanpa_bukti_gps_berhasil(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->wfhApprovedHariIni();
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999', [
            'akurasi' => null,
            'fix' => null,
            'durasi_ms' => null,
        ]);

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertTrue($this->adaPresensi());
    }

    public function test_unit_tanpa_titik_tanpa_bukti_gps_tetap_berhasil(): void
    {
        $this->setTime('08:00:00');
        $this->mockFoto();

        $result = $this->prosesPresensi('-6.9999,107.9999', [
            'akurasi' => null,
            'fix' => null,
            'durasi_ms' => null,
        ]);

        $this->assertTrue($result['success'], json_encode($result));
        $this->assertTrue($this->adaPresensi());
    }

    // ======================================================================
    // Deteksi anomali
    // ======================================================================

    public function test_flag_koordinat_identik_dipakai_karyawan_lain(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');
        $this->mockFoto();

        Karyawan::create([
            'nik' => 'GEO002',
            'nama_lengkap' => 'Karyawan Lain',
            'jabatan' => 'Staff',
            'posisi' => 'Staff',
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => 'Teknologi',
            'unit_id' => $this->unit->id,
            'no_hp' => '081234000002',
            'password' => bcrypt('password'),
        ]);

        DB::table('presensis')->insert([
            'nik' => 'GEO002',
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => '07:30:00',
            'jam_out' => null,
            'foto_in' => 'lain.webp',
            'foto_out' => null,
            'lokasi_in' => '-6.2001,106.8000',
            'lokasi_out' => null,
            'terlambat' => 0,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);

        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success'], json_encode($result));

        $row = DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->first();
        $this->assertStringContainsString(Presensi::FLAG_KOORDINAT_IDENTIK, (string) $row->flag_manipulasi);
    }

    public function test_tanpa_flag_koordinat_identik_jika_record_sendiri(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->presensiHariIni();
        $this->setTime('17:30:00');
        $this->mockFoto();

        // Koordinat pulang sama persis dengan koordinat milik sendiri — bukan anomali.
        $result = $this->prosesPresensi('-6.2001,106.8000');

        $this->assertTrue($result['success'], json_encode($result));

        $row = DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->first();
        $this->assertNull($row->flag_manipulasi);
    }

    public function test_flag_teleport_masuk_ke_pulang(): void
    {
        $this->titikLokasi('Kantor A', -6.2000, 106.8000);
        $this->titikLokasi('Kantor B', -6.2000, 140.0000);
        $this->presensiHariIni();
        $this->setTime('16:05:00');
        $this->mockFoto();

        // Jarak ~3660 km dalam 8 jam (dual kantor lintas pulau) → kecepatan > 200 km/jam.
        $result = $this->prosesPresensi('-6.2001,140.0000');

        $this->assertTrue($result['success'], json_encode($result));

        $row = DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->first();
        $this->assertStringContainsString(Presensi::FLAG_TELEPORT, (string) $row->flag_manipulasi);
    }

    // ======================================================================
    // Lokasi tidak valid
    // ======================================================================

    public function test_lokasi_kosong_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Lokasi tidak terdeteksi', $result['message']);
        $this->assertFalse($this->adaPresensi());
    }

    public function test_lokasi_tidak_valid_ditolak(): void
    {
        $this->titikLokasi('Kantor Pusat', -6.2000, 106.8000);
        $this->setTime('08:00:00');

        $result = $this->prosesPresensi('bukan-koordinat');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->adaPresensi());
    }

    // ======================================================================
    // Helper
    // ======================================================================

    private function setTime(string $jam): void
    {
        Carbon::setTestNow(Carbon::parse(self::HARI_INI.' '.$jam, 'Asia/Jakarta'));
    }

    private function titikLokasi(string $nama, float $lat, float $lng): void
    {
        Unitlokasi::create([
            'unit_id' => $this->unit->id,
            'nama_lokasi' => $nama,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    private function presensiHariIni(): void
    {
        DB::table('presensis')->insert([
            'nik' => $this->karyawan->nik,
            'tgl_presensi' => self::HARI_INI,
            'jam_in' => '08:00:00',
            'jam_out' => null,
            'foto_in' => 'foto-in.webp',
            'foto_out' => null,
            'lokasi_in' => '-6.2001,106.8000',
            'lokasi_out' => null,
            'terlambat' => 0,
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }

    private function wfhApprovedHariIni(): void
    {
        DB::table('wfhs')->insert([
            'nik' => $this->karyawan->nik,
            'jabatan' => 'Staff',
            'posisi' => 'Web Developer',
            'tgl_wfh' => self::HARI_INI,
            'deskripsi_pekerjaan' => 'Kerja remote',
            'status' => 'approved',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'dikirim_tanggal' => now('Asia/Jakarta'),
            'created_at' => now('Asia/Jakarta'),
            'updated_at' => now('Asia/Jakarta'),
        ]);
    }

    private function mockFoto(): void
    {
        $this->mock(ImageService::class, function ($mock): void {
            $mock->shouldReceive('processBase64')->andReturn('foto.webp');
        });
    }

    /**
     * @param  array<string, mixed>  $bukti  Override bukti GPS (null untuk menghapus field)
     */
    private function prosesPresensi(string $lokasi, array $bukti = []): array
    {
        $this->actingAs($this->karyawan, 'karyawan');

        $request = Request::create('/presensi/store', 'POST', array_merge([
            'image' => 'data:image/png;base64,AAAA',
            'lokasi' => $lokasi,
            'akurasi' => 10,
            'fix' => 5,
            'durasi_ms' => 10000,
        ], $bukti));

        return app(PresensiService::class)->processPresensi($request);
    }

    private function adaPresensi(): bool
    {
        // Query mentah: cast 'date' pada model menyimpan 'Y-m-d 00:00:00' di SQLite,
        // sehingga pencocokan persis dengan 'Y-m-d' tidak akan match.
        return DB::table('presensis')
            ->where('nik', $this->karyawan->nik)
            ->where('tgl_presensi', 'like', self::HARI_INI.'%')
            ->exists();
    }
}
