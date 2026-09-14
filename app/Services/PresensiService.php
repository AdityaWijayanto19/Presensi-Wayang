<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresensiService
{
    private const JAM_BUKA_PRESENSI = '07:00:00';
    private const MINIMAL_JAM_KERJA = 8;
    private const UNIT_TANPA_KETERLAMBATAN = 'Arthama';

    public function processPresensi(Request $request): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $tglPresensi = now()->format('Y-m-d');
        $jam = now()->format('H:i:s');

        if ($jam < self::JAM_BUKA_PRESENSI) {
            return ['success' => false, 'message' => 'Presensi baru dibuka pukul 07:00!', 'type' => 'in'];
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.', 'type' => 'in'];
        }

        $unitKerja = Unitperusahaan::where('unit', $karyawan->unit)->first();
        if (!$unitKerja) {
            return ['success' => false, 'message' => 'Unit kerja tidak ditemukan.', 'type' => 'in'];
        }

        $jamMasuk = $unitKerja->jam_masuk instanceof \Carbon\Carbon
            ? $unitKerja->jam_masuk->format('H:i:s')
            : (string) $unitKerja->jam_masuk;
        $terlambat = $this->hitungKeterlambatan($karyawan->unit, $jamMasuk, $jam);

        return DB::transaction(function () use ($nik, $tglPresensi, $jam, $karyawan, $terlambat, $request) {
            $cek = Presensi::where('tgl_presensi', $tglPresensi)
                ->where('nik', $nik)
                ->first();

            $status = $cek ? 'out' : 'in';

            if ($cek && $cek->jam_out == null) {
                $jamMasukTime = strtotime($cek->jam_in);
                $jamSekarang = strtotime($jam);
                $selisihJamKerja = ($jamSekarang - $jamMasukTime) / 3600;

                if ($selisihJamKerja < self::MINIMAL_JAM_KERJA) {
                    return ['success' => false, 'message' => 'Belum bisa presensi pulang! Minimal bekerja 8 jam.', 'type' => 'out'];
                }
            }

            if ($cek && $cek->jam_out == null) {
                $wfhToday = Wfh::where('nik', $nik)
                    ->where('tgl_wfh', $tglPresensi)
                    ->where('status', 'approved')
                    ->first();
                if ($wfhToday && empty($wfhToday->laporan_deskripsi)) {
                    return ['success' => false, 'message' => 'Anda harus mengupload laporan WFH terlebih dahulu sebelum presensi pulang.', 'type' => 'out'];
                }
            }

            if ($cek && $cek->jam_out != null) {
                return ['success' => false, 'message' => 'Anda sudah melakukan presensi pulang!', 'type' => 'done'];
            }

            $fileName = $this->simpanFoto($nik, $tglPresensi, $status, $request->image);

            if ($cek) {
                return $this->prosesPulang($nik, $tglPresensi, $fileName, $request->lokasi);
            } else {
                return $this->prosesMasuk($nik, $tglPresensi, $jam, $fileName, $request->lokasi, $terlambat);
            }
        });
    }

    private function hitungKeterlambatan(string $unit, string $jamMasuk, string $jamSekarang): int
    {
        if ($unit === self::UNIT_TANPA_KETERLAMBATAN) {
            return 0;
        }

        $jamMasukTime = strtotime($jamMasuk);
        $jamAbsen = strtotime($jamSekarang);

        if ($jamAbsen <= $jamMasukTime) {
            return 0;
        }

        $selisihMenit = (int) floor(($jamAbsen - $jamMasukTime) / 60);

        if ($selisihMenit <= 60) {
            return $selisihMenit;
        }

        return (int) ceil($selisihMenit / 60) * 60;
    }

    private function simpanFoto(string $nik, string $tglPresensi, string $status, string $image): string
    {
        $imageService = app(ImageService::class);
        $path = $imageService->processBase64($image, $nik, $status);

        if (!$path) {
            return '';
        }

        return basename($path);
    }

    private function prosesPulang(string $nik, string $tglPresensi, string $fileName, string $lokasi): array
    {
        $presensi = Presensi::where('tgl_presensi', $tglPresensi)
            ->where('nik', $nik)
            ->first();

        if ($presensi) {
            $presensi->update([
                'jam_out' => now()->format('H:i:s'),
                'foto_out' => $fileName,
                'lokasi_out' => $lokasi,
            ]);
            return ['success' => true, 'message' => 'Presensi berhasil, selamat istirahat!', 'type' => 'out'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan foto!', 'type' => 'out'];
    }

    private function prosesMasuk(string $nik, string $tglPresensi, string $jam, string $fileName, string $lokasi, int $terlambat): array
    {
        Presensi::create([
            'nik' => $nik,
            'tgl_presensi' => $tglPresensi,
            'jam_in' => $jam,
            'foto_in' => $fileName,
            'lokasi_in' => $lokasi,
            'terlambat' => $terlambat,
        ]);

        return ['success' => true, 'message' => 'Presensi berhasil, selamat bekerja!', 'type' => 'in'];
    }

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
