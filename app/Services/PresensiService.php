<?php

namespace App\Services;

use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use App\Services\Shared\LocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresensiService
{
    private LocationService $location;

    public function __construct(LocationService $location)
    {
        $this->location = $location;
    }

    private const JAM_BUKA_PRESENSI = '07:00:00';

    private const MINIMAL_JAM_KERJA = 8;

    private const DEFAULT_JAM_MASUK = '08:00:00';

    private const DEFAULT_RADIUS_METER = 100;

    private const PESAN_LOKASI_TIDAK_VALID = 'Lokasi tidak terdeteksi. Aktifkan GPS lalu coba lagi.';

    /** Batas akurasi GPS (meter) yang diterima saat presensi kantor. */
    private const MAX_AKURASI_METER = 50;

    /** Minimal durasi pantauan watchPosition (ms) sebelum presensi diizinkan. */
    private const MIN_DURASI_PANTAU_MS = 5000;

    /**
     * Minimal jumlah fix GPS kumulatif yang harus diterima sebelum presensi diizinkan.
     * Fix awal getCurrentPosition + watchPosition sudah memenuhi ini walau HP diam.
     */
    private const MIN_JUMLAH_FIX = 2;

    /** Jendela pencarian koordinat identik (hari). */
    private const JENDELA_DUPLOKASI_HARI = 30;

    /** Kecepatan tidak masuk akal (km/jam) yang ditandai sebagai teleport. */
    private const BATAS_TELEPORT_KM_PER_JAM = 200.0;

    public function processPresensi(Request $request): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $tglPresensi = now('Asia/Jakarta')->format('Y-m-d');
        $jam = now('Asia/Jakarta')->format('H:i:s');

        if ($jam < self::JAM_BUKA_PRESENSI) {
            return ['success' => false, 'message' => 'Presensi baru dibuka pukul 07:00!', 'type' => 'in'];
        }

        $karyawan = Karyawan::where('nik', $nik)->first();
        if (! $karyawan) {
            return ['success' => false, 'message' => 'Data karyawan tidak ditemukan.', 'type' => 'in'];
        }

        $unitKerja = Unitperusahaan::where('unit', $karyawan->unit)->first();
        if (! $unitKerja) {
            return ['success' => false, 'message' => 'Unit kerja tidak ditemukan.', 'type' => 'in'];
        }

        $jamMasuk = $this->formatJamMasuk($unitKerja->jam_masuk);
        $terlambat = $this->hitungKeterlambatan($jamMasuk, $jam);

        return DB::transaction(function () use ($nik, $tglPresensi, $jam, $unitKerja, $terlambat, $request) {
            $cek = Presensi::where('tgl_presensi', $tglPresensi)
                ->where('nik', $nik)
                ->first();

            $status = $cek ? 'out' : 'in';

            $wfhHariIni = Wfh::where('nik', $nik)
                ->where('tgl_wfh', $tglPresensi)
                ->where('status', 'approved')
                ->first();

            if ($cek && $cek->jam_out == null) {
                $jamMasukTime = strtotime($cek->jam_in);
                $jamSekarang = strtotime($jam);
                $selisihJamKerja = ($jamSekarang - $jamMasukTime) / 3600;

                // Check if approved izin pulang cepat exists for today
                $izinPulangCepat = Izin::where('nik', $nik)
                    ->where('tgl_izin', $tglPresensi)
                    ->where('jenis_izin', 'pulang_cepat')
                    ->where('status', 'approved')
                    ->exists();

                if (! $izinPulangCepat && $selisihJamKerja < self::MINIMAL_JAM_KERJA) {
                    return ['success' => false, 'message' => 'Belum bisa presensi pulang! Minimal bekerja 8 jam.', 'type' => 'out'];
                }
            }

            if ($cek && $cek->jam_out == null) {
                if ($wfhHariIni && empty($wfhHariIni->laporan_deskripsi)) {
                    return ['success' => false, 'message' => 'Anda harus mengupload laporan WFH terlebih dahulu sebelum presensi pulang.', 'type' => 'out'];
                }
            }

            if ($cek && $cek->jam_out != null) {
                return ['success' => false, 'message' => 'Anda sudah melakukan presensi pulang!', 'type' => 'done'];
            }

            $gagal = $this->validasiRadius($request, $unitKerja, $wfhHariIni, $status);
            if ($gagal !== null) {
                return $gagal;
            }

            $fileName = $this->simpanFoto($nik, $tglPresensi, $status, $request->image);

            $bukti = $this->buktiGps($request, $unitKerja);

            if ($cek) {
                return $this->prosesPulang($nik, $tglPresensi, $fileName, $bukti, $cek);
            }

            return $this->prosesMasuk($nik, $tglPresensi, $jam, $fileName, $bukti, $terlambat);
        });
    }

    /**
     * Validasi geofencing presensi kantor.
     *
     * Presensi lolos jika GPS berada di dalam radius salah satu titik lokasi
     * unit kerja (logika OR). Cabang WFH (approved hari ini) bebas lokasi,
     * dan unit yang belum memiliki titik lokasi dijadikan fail-open.
     *
     * @return array{success: bool, message: string, type: string}|null null jika lolos validasi
     */
    private function validasiRadius(Request $request, Unitperusahaan $unitKerja, ?Wfh $wfhHariIni, string $status): ?array
    {
        $lokasi = trim((string) $request->input('lokasi', ''));
        if ($lokasi === '') {
            return ['success' => false, 'message' => self::PESAN_LOKASI_TIDAK_VALID, 'type' => $status];
        }

        if ($wfhHariIni !== null) {
            return null;
        }

        $lokasis = $unitKerja->lokasis;
        if ($lokasis->isEmpty()) {
            return null;
        }

        $gagalBukti = $this->validasiBuktiGps($request, $status);
        if ($gagalBukti !== null) {
            return $gagalBukti;
        }

        $jarak = $this->location->jarakTerdekat($lokasis, $lokasi);
        if ($jarak === null) {
            return ['success' => false, 'message' => self::PESAN_LOKASI_TIDAK_VALID, 'type' => $status];
        }

        $radius = (int) ($unitKerja->radius_meter ?? self::DEFAULT_RADIUS_METER);
        if ($jarak > $radius) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Anda berada %d m dari lokasi kantor terdekat (radius %d m). Silakan presensi dari area kantor.',
                    (int) round($jarak),
                    $radius
                ),
                'type' => $status,
            ];
        }

        return null;
    }

    /**
     * Validasi bukti GPS tambahan (akurasi, jumlah fix, durasi pantauan).
     *
     * Dilakukan hanya untuk cabang kantor agar manipulasi lewat fake GPS
     * lebih sulit dan setiap presensi meninggalkan jejak yang bisa diaudit.
     *
     * @return array{success: bool, message: string, type: string}|null null jika lolos
     */
    private function validasiBuktiGps(Request $request, string $status): ?array
    {
        $akurasiInput = $request->input('akurasi');
        $akurasi = is_numeric($akurasiInput) ? (float) $akurasiInput : null;

        if ($akurasi === null || $akurasi <= 0) {
            return [
                'success' => false,
                'message' => 'Data GPS tidak lengkap. Muat ulang halaman lalu coba lagi.',
                'type' => $status,
            ];
        }

        if ($akurasi > self::MAX_AKURASI_METER) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Sinyal GPS belum akurat (±%d m, maksimal %d m). Pindah ke area terbuka lalu coba lagi.',
                    (int) round($akurasi),
                    self::MAX_AKURASI_METER
                ),
                'type' => $status,
            ];
        }

        $fix = (int) $request->input('fix', 0);
        $durasiMs = (int) $request->input('durasi_ms', 0);

        if ($fix < self::MIN_JUMLAH_FIX || $durasiMs < self::MIN_DURASI_PANTAU_MS) {
            return [
                'success' => false,
                'message' => 'Lokasi belum stabil. Tunggu beberapa detik sampai posisi terkunci lalu coba lagi.',
                'type' => $status,
            ];
        }

        return null;
    }

    /**
     * Kumpulkan bukti GPS untuk disimpan (akurasi, jarak server-side, fix, durasi, IP).
     *
     * @return array{lokasi: string, akurasi: ?float, jarak: ?int, fix: ?int, durasi: ?int, ip: ?string}
     */
    private function buktiGps(Request $request, Unitperusahaan $unitKerja): array
    {
        $lokasi = trim((string) $request->input('lokasi', ''));

        $akurasiInput = $request->input('akurasi');
        $fix = (int) $request->input('fix', 0);
        $durasiMs = (int) $request->input('durasi_ms', 0);

        $jarak = $lokasi === '' ? null : $this->location->jarakTerdekat($unitKerja->lokasis, $lokasi);

        return [
            'lokasi' => $lokasi,
            'akurasi' => is_numeric($akurasiInput) ? round((float) $akurasiInput, 2) : null,
            'jarak' => $jarak === null ? null : (int) round($jarak),
            'fix' => $fix > 0 ? $fix : null,
            'durasi' => $durasiMs > 0 ? $durasiMs : null,
            'ip' => $request->ip(),
        ];
    }

    /**
     * Flag anomali saat presensi masuk: koordinat identik dipakai record lain.
     *
     * @return array<int, string>
     */
    private function deteksiFlagMasuk(string $nik, string $tglPresensi, string $lokasi): array
    {
        $flag = [];

        if ($this->koordinatIdentik($lokasi, $nik, $tglPresensi)) {
            $flag[] = Presensi::FLAG_KOORDINAT_IDENTIK;
        }

        return $flag;
    }

    /**
     * Flag anomali saat presensi pulang: koordinat identik + teleport masuk→pulang.
     *
     * @return array<int, string>
     */
    private function deteksiFlagPulang(Presensi $presensi, array $bukti): array
    {
        $flag = [];

        $tgl = $presensi->tgl_presensi instanceof Carbon
            ? $presensi->tgl_presensi->format('Y-m-d')
            : (string) $presensi->tgl_presensi;

        if ($this->koordinatIdentik($bukti['lokasi'], $presensi->nik, $tgl)) {
            $flag[] = Presensi::FLAG_KOORDINAT_IDENTIK;
        }

        if ($this->isTeleport($presensi, $bukti['lokasi'])) {
            $flag[] = Presensi::FLAG_TELEPORT;
        }

        return $flag;
    }

    /**
     * Cek apakah koordinat persis (string identik) sudah dipakai record lain
     * dalam jendela 30 hari — indikator klasik fake GPS (titik mati).
     */
    private function koordinatIdentik(string $lokasi, string $nik, string $tglPresensi): bool
    {
        $lokasi = trim($lokasi);
        if ($lokasi === '') {
            return false;
        }

        return Presensi::query()
            ->where('created_at', '>=', now('Asia/Jakarta')->subDays(self::JENDELA_DUPLOKASI_HARI))
            ->where(function ($query) use ($lokasi) {
                $query->where('lokasi_in', $lokasi)
                    ->orWhere('lokasi_out', $lokasi);
            })
            ->where(function ($query) use ($nik, $tglPresensi) {
                $query->where('nik', '!=', $nik)
                    ->orWhereRaw('DATE(tgl_presensi) != ?', [$tglPresensi]);
            })
            ->exists();
    }

    /**
     * Cek teleport: jarak masuk→pulang tidak masuk akal terhadap waktu tempuh.
     */
    private function isTeleport(Presensi $presensi, string $lokasiOut): bool
    {
        if (empty($presensi->lokasi_in) || trim($lokasiOut) === '' || empty($presensi->jam_in)) {
            return false;
        }

        $jarakMeter = $this->location->jarakAntarLokasi($presensi->lokasi_in, $lokasiOut);
        if ($jarakMeter === null) {
            return false;
        }

        $jamOut = now('Asia/Jakarta')->format('H:i:s');
        $jamSelisihJam = max((strtotime($jamOut) - strtotime($presensi->jam_in)) / 3600, 1 / 60);

        $kecepatan = ($jarakMeter / 1000) / $jamSelisihJam;

        return $kecepatan > self::BATAS_TELEPORT_KM_PER_JAM;
    }

    /**
     * Gabungkan flag lama (masuk) dengan flag baru (pulang) tanpa duplikat.
     *
     * @param  array<int, string>  $baru
     */
    private function gabungFlag(?string $lama, array $baru): ?string
    {
        $semua = array_unique(array_filter(array_merge(
            $lama === null || $lama === '' ? [] : explode(',', $lama),
            $baru
        )));

        return $semua === [] ? null : implode(',', $semua);
    }

    private function hitungKeterlambatan(string $jamMasuk, string $jamSekarang): int
    {
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

    public function getJamMasukUnit(string $unit): string
    {
        $jamMasuk = Unitperusahaan::where('unit', $unit)->value('jam_masuk');

        return is_null($jamMasuk)
            ? self::DEFAULT_JAM_MASUK
            : $this->formatJamMasuk($jamMasuk);
    }

    public function hitungTerlambatPresensi(string $unit, string $jamAbsen): int
    {
        return $this->hitungKeterlambatan($this->getJamMasukUnit($unit), $jamAbsen);
    }

    public function deletePresensiAdmin(int $id): array
    {
        $presensi = Presensi::find($id);

        if (! $presensi) {
            return ['success' => false, 'message' => 'Data presensi tidak ditemukan'];
        }

        foreach (['foto_in', 'foto_out'] as $foto) {
            if (! empty($presensi->{$foto})) {
                Storage::disk('public')->delete('uploads/absensi/'.$presensi->{$foto});
            }
        }

        $presensi->delete();

        return ['success' => true, 'message' => 'Data presensi berhasil dihapus'];
    }

    private function formatJamMasuk($jamMasuk): string
    {
        if ($jamMasuk instanceof Carbon) {
            return $jamMasuk->format('H:i:s');
        }

        return (string) $jamMasuk;
    }

    private function simpanFoto(string $nik, string $tglPresensi, string $status, string $image): string
    {
        $imageService = app(ImageService::class);
        $path = $imageService->processBase64($image, $nik, $status);

        if (! $path) {
            return '';
        }

        return basename($path);
    }

    /**
     * @param  array{lokasi: string, akurasi: ?float, jarak: ?int, fix: ?int, durasi: ?int, ip: ?string}  $bukti
     */
    private function prosesPulang(string $nik, string $tglPresensi, string $fileName, array $bukti, Presensi $presensi): array
    {
        if ($presensi) {
            $flag = $this->gabungFlag(
                $presensi->flag_manipulasi,
                $this->deteksiFlagPulang($presensi, $bukti)
            );

            $presensi->update([
                'jam_out' => now('Asia/Jakarta')->format('H:i:s'),
                'foto_out' => $fileName,
                'lokasi_out' => $bukti['lokasi'],
                'lokasi_out_akurasi' => $bukti['akurasi'],
                'lokasi_out_jarak' => $bukti['jarak'],
                'gps_fix_out' => $bukti['fix'],
                'gps_durasi_out_ms' => $bukti['durasi'],
                'ip_out' => $bukti['ip'],
                'flag_manipulasi' => $flag,
            ]);

            return ['success' => true, 'message' => 'Presensi berhasil, selamat istirahat!', 'type' => 'out'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan foto!', 'type' => 'out'];
    }

    /**
     * @param  array{lokasi: string, akurasi: ?float, jarak: ?int, fix: ?int, durasi: ?int, ip: ?string}  $bukti
     */
    private function prosesMasuk(string $nik, string $tglPresensi, string $jam, string $fileName, array $bukti, int $terlambat): array
    {
        $flag = $this->gabungFlag(null, $this->deteksiFlagMasuk($nik, $tglPresensi, $bukti['lokasi']));

        Presensi::create([
            'nik' => $nik,
            'tgl_presensi' => $tglPresensi,
            'jam_in' => $jam,
            'foto_in' => $fileName,
            'lokasi_in' => $bukti['lokasi'],
            'lokasi_in_akurasi' => $bukti['akurasi'],
            'lokasi_in_jarak' => $bukti['jarak'],
            'gps_fix_in' => $bukti['fix'],
            'gps_durasi_in_ms' => $bukti['durasi'],
            'ip_in' => $bukti['ip'],
            'terlambat' => $terlambat,
            'flag_manipulasi' => $flag,
        ]);

        return ['success' => true, 'message' => 'Presensi berhasil, selamat bekerja!', 'type' => 'in'];
    }

    public function reverseGeocode(string $coordinates): string
    {
        return $this->location->reverseGeocode($coordinates);
    }
}
