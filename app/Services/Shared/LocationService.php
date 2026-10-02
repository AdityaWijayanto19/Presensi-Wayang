<?php

namespace App\Services\Shared;

class LocationService
{
    private const BUMI_RADIUS_METER = 6371000;

    /**
     * Jarak (meter) dari posisi user ke titik kantor TERDEKAT.
     * Mengembalikan null jika posisi user tidak valid atau tidak ada titik kantor
     * sehingga pemanggil dapat memutuskan sendiri (fail-open / error validasi).
     */
    public function jarakTerdekat(iterable $lokasis, string $lokasi): ?float
    {
        $posisi = self::parseKoordinat($lokasi);
        if ($posisi === null) {
            return null;
        }

        $jarakTerdekat = null;

        foreach ($lokasis as $titik) {
            $jarak = self::jarakMeter(
                $posisi[0],
                $posisi[1],
                (float) $titik->lat,
                (float) $titik->lng
            );

            if ($jarakTerdekat === null || $jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
            }
        }

        return $jarakTerdekat;
    }

    /**
     * Jarak (meter) antara dua string koordinat "lat,lng".
     * Mengembalikan null jika salah satu tidak valid.
     */
    public function jarakAntarLokasi(string $lokasiA, string $lokasiB): ?float
    {
        $a = self::parseKoordinat($lokasiA);
        $b = self::parseKoordinat($lokasiB);

        if ($a === null || $b === null) {
            return null;
        }

        return self::jarakMeter($a[0], $a[1], $b[0], $b[1]);
    }

    /**
     * Parse string koordinat "lat,lng" menjadi [lat, lng], atau null jika tidak valid.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function parseKoordinat(string $coordinates): ?array
    {
        $parts = array_map('trim', explode(',', $coordinates));

        if (count($parts) !== 2 || ! is_numeric($parts[0]) || ! is_numeric($parts[1])) {
            return null;
        }

        $lat = (float) $parts[0];
        $lng = (float) $parts[1];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        return [$lat, $lng];
    }

    /**
     * Jarak lingkaran besar (Haversine) antara dua koordinat dalam meter.
     */
    private static function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * self::BUMI_RADIUS_METER * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function reverseGeocode(string $coordinates): string
    {
        if (empty($coordinates) || $coordinates === '-') {
            return '-';
        }

        $koordinat = self::parseKoordinat($coordinates);
        if ($koordinat === null) {
            return $coordinates;
        }

        $lat = $koordinat[0];
        $lng = $koordinat[1];

        try {
            $url = sprintf(
                'https://nominatim.openstreetmap.org/reverse?lat=%s&lon=%s&format=json&addressdetails=1&accept-language=id',
                urlencode($lat),
                urlencode($lng)
            );

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => ['User-Agent: PresensiDigital/1.0'],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || ! $response) {
                return $coordinates;
            }

            $data = json_decode($response, true);
            if (! isset($data['display_name'])) {
                return $coordinates;
            }

            return $data['display_name'];
        } catch (\Exception $e) {
            return $coordinates;
        }
    }
}
