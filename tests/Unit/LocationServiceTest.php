<?php

namespace Tests\Unit;

use App\Models\Unitlokasi;
use App\Services\Shared\LocationService;
use Tests\TestCase;

class LocationServiceTest extends TestCase
{
    private LocationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new LocationService;
    }

    public function test_mengambil_jarak_ke_titik_kantor_terdekat(): void
    {
        $lokasis = [
            $this->titik('A', -6.2000, 106.8000),
            $this->titik('B', -6.2010, 106.8000),
        ];

        $jarak = $this->service->jarakTerdekat($lokasis, '-6.2011,106.8000');

        $this->assertNotNull($jarak);
        $this->assertEqualsWithDelta(11.12, $jarak, 1.0);
    }

    public function test_jarak_haversine_akurat(): void
    {
        $lokasis = [$this->titik('Kantor', -6.2000, 106.8000)];

        $jarak = $this->service->jarakTerdekat($lokasis, '-6.2010,106.8000');

        // 0.001 derajat latitude ≈ 111.19 meter.
        $this->assertNotNull($jarak);
        $this->assertEqualsWithDelta(111.19, $jarak, 1.0);
    }

    public function test_kembali_null_jika_posisi_user_tidak_valid(): void
    {
        $lokasis = [$this->titik('Kantor', -6.2, 106.8)];

        $this->assertNull($this->service->jarakTerdekat($lokasis, ''));
        $this->assertNull($this->service->jarakTerdekat($lokasis, 'bukan-koordinat'));
        $this->assertNull($this->service->jarakTerdekat($lokasis, '91,181'));
        $this->assertNull($this->service->jarakTerdekat($lokasis, '1,2,3'));
    }

    public function test_kembali_null_jika_tidak_ada_titik_kantor(): void
    {
        $this->assertNull($this->service->jarakTerdekat([], '-6.2,106.8'));
    }

    public function test_parse_koordinat_menerima_spasi(): void
    {
        $hasil = LocationService::parseKoordinat(' -6.2000 , 106.8000 ');

        $this->assertNotNull($hasil);
        $this->assertSame(-6.2, $hasil[0]);
        $this->assertSame(106.8, $hasil[1]);
    }

    public function test_parse_koordinat_menolak_nilai_di_luar_rentang(): void
    {
        $this->assertNull(LocationService::parseKoordinat('91,106.8'));
        $this->assertNull(LocationService::parseKoordinat('-6.2,-181'));
    }

    private function titik(string $nama, float $lat, float $lng): Unitlokasi
    {
        return new Unitlokasi([
            'nama_lokasi' => $nama,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }
}
