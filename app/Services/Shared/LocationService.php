<?php

namespace App\Services\Shared;

class LocationService
{
    public function reverseGeocode(string $coordinates): string
    {
        if (empty($coordinates) || $coordinates === '-') {
            return '-';
        }

        $parts = array_map('floatval', explode(',', $coordinates));
        if (count($parts) !== 2) {
            return $coordinates;
        }

        $lat = $parts[0];
        $lng = $parts[1];

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

            if ($httpCode !== 200 || !$response) {
                return $coordinates;
            }

            $data = json_decode($response, true);
            if (!isset($data['display_name'])) {
                return $coordinates;
            }

            return $data['display_name'];
        } catch (\Exception $e) {
            return $coordinates;
        }
    }
}
