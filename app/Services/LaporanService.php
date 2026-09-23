<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Lembur;
use App\Models\Wfh;
use App\Models\Unitperusahaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LaporanService
{
    private const NAMA_BULAN = [
        "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember",
    ];

    private static function getCutoffDates(int $bulan, int $tahun): array
    {
        $startDate = Carbon::create($tahun, $bulan, 21)->subMonth()->startOfDay();
        $endDate = Carbon::create($tahun, $bulan, 20)->endOfDay();
        return [$startDate, $endDate];
    }

    public static function getLaporanPageData(): array
    {
        $namabulan = self::NAMA_BULAN;
        $unit = Unitperusahaan::orderBy('perusahaan')->get();
        $karyawan = Karyawan::orderBy('nama_lengkap')->get();

        return compact('namabulan', 'karyawan', 'unit');
    }

    public static function cetakLaporan(Request $request)
    {
        if ($request->tipe_export === 'perusahaan') {
            $data = self::buildUnitLaporanData($request);
            if ($data instanceof \Illuminate\Http\RedirectResponse) {
                return $data;
            }
            $pdf = Pdf::loadView('admin.presensi.cetaklaporan-unit', $data);
            return $pdf->download('Laporan_Presensi_' . $data['unitData']->perusahaan . '.pdf');
        }

        $data = self::buildLaporanData($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $pdf = Pdf::loadView('admin.presensi.cetaklaporan', $data);
        return $pdf->download('Laporan_Presensi_' . $data['karyawan']->nama_lengkap . '.pdf');
    }

    public static function previewLaporan(Request $request)
    {
        if ($request->tipe_export === 'perusahaan') {
            $data = self::buildUnitLaporanData($request);
            if ($data instanceof \Illuminate\Http\RedirectResponse) {
                return $data;
            }
            $pdf = Pdf::loadView('admin.presensi.cetaklaporan-unit', $data);
            return $pdf->stream('Laporan_Presensi_' . $data['unitData']->perusahaan . '.pdf');
        }

        $data = self::buildLaporanData($request);
        if ($data instanceof \Illuminate\Http\RedirectResponse) {
            return $data;
        }
        $pdf = Pdf::loadView('admin.presensi.cetaklaporan', $data);
        return $pdf->stream('Laporan_Presensi_' . $data['karyawan']->nama_lengkap . '.pdf');
    }

    private static function buildLaporanData(Request $request): array|\Illuminate\Http\RedirectResponse
    {
        $nik = $request->nik;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unit = $request->unit;

        if (empty($bulan) || empty($tahun) || empty($unit) || empty($nik)) {
            return Redirect::back()->with('warning', 'Harap lengkapi seluruh filter terlebih dahulu');
        }

        $namabulan = self::NAMA_BULAN;
        [$startDate, $endDate] = self::getCutoffDates((int) $bulan, (int) $tahun);

        $karyawan = Karyawan::with('unitperusahaan')
            ->where('nik', $nik)
            ->first();

        if (!$karyawan) {
            return Redirect::back()->with('warning', 'Data karyawan tidak ditemukan');
        }

        $presensi = Presensi::where('nik', $nik)
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        $totalMenitKerja = 0;
        foreach ($presensi as $p) {
            if ($p->jam_out != null) {
                $awal = strtotime($p->jam_in);
                $akhir = strtotime($p->jam_out);
                $selisih = ($akhir - $awal) / 60;
                $totalMenitKerja += $selisih;
            }
        }

        $totalJamKerja = floor($totalMenitKerja / 60);
        $sisaMenitKerja = $totalMenitKerja % 60;

        $lembur = Lembur::where('nik', $nik)
            ->where('status', 'approved')
            ->whereBetween('tgl_lembur', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->tgl_lembur->format('Y-m-d'));

        $totalLembur = 0;
        $totalProrate = 0;
        foreach ($lembur as $item) {
            if ($item->durasi_jam > 5) {
                $totalProrate++;
            } else {
                $totalLembur += (float) $item->durasi_jam;
            }
        }

        $wfh = Wfh::where('nik', $nik)
            ->where('status', 'approved')
            ->whereBetween('tgl_wfh', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->tgl_wfh->format('Y-m-d'));

        $totalWfh = $wfh->count();

        if ($presensi->isEmpty()) {
            return Redirect::back()->with('warning', 'Data presensi tidak ditemukan');
        }

        return compact(
            'bulan', 'tahun', 'namabulan', 'karyawan', 'presensi',
            'lembur', 'wfh', 'totalLembur', 'totalProrate', 'totalWfh',
            'sisaMenitKerja', 'totalJamKerja', 'startDate', 'endDate'
        );
    }

    private static function buildUnitLaporanData(Request $request): array|\Illuminate\Http\RedirectResponse
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unit = $request->unit;

        if (empty($bulan) || empty($tahun) || empty($unit)) {
            return Redirect::back()->with('warning', 'Harap lengkapi seluruh filter terlebih dahulu');
        }

        $namabulan = self::NAMA_BULAN;
        [$startDate, $endDate] = self::getCutoffDates((int) $bulan, (int) $tahun);

        $unitData = Unitperusahaan::where('unit', $unit)->first();
        if (!$unitData) {
            return Redirect::back()->with('warning', 'Data unit perusahaan tidak ditemukan');
        }

        $karyawans = Karyawan::where('unit', $unit)->orderBy('nama_lengkap')->get();

        if ($karyawans->isEmpty()) {
            return Redirect::back()->with('warning', 'Tidak ada karyawan di unit ini');
        }

        $dataKaryawan = [];

        foreach ($karyawans as $k) {
            $presensi = Presensi::where('nik', $k->nik)
                ->whereBetween('tgl_presensi', [$startDate, $endDate])
                ->get();

            $totalHadir = $presensi->count();

            $totalMenitKerja = 0;
            foreach ($presensi as $p) {
                if ($p->jam_out != null) {
                    $awal = strtotime($p->jam_in);
                    $akhir = strtotime($p->jam_out);
                    $selisih = ($akhir - $awal) / 60;
                    $totalMenitKerja += $selisih;
                }
            }

            $totalJamKerja = floor($totalMenitKerja / 60);
            $sisaMenitKerja = $totalMenitKerja % 60;

            $lembur = Lembur::where('nik', $k->nik)
                ->where('status', 'approved')
                ->whereBetween('tgl_lembur', [$startDate, $endDate])
                ->get();

            $totalLembur = 0;
            $totalProrate = 0;
            foreach ($lembur as $item) {
                if ($item->durasi_jam > 5) {
                    $totalProrate++;
                } else {
                    $totalLembur += (float) $item->durasi_jam;
                }
            }

            $wfh = Wfh::where('nik', $k->nik)
                ->where('status', 'approved')
                ->whereBetween('tgl_wfh', [$startDate, $endDate])
                ->count();

            $dataKaryawan[] = [
                'nik' => $k->nik,
                'nama_lengkap' => $k->nama_lengkap,
                'jabatan' => $k->jabatan,
                'totalHadir' => $totalHadir,
                'totalJamKerja' => $totalJamKerja,
                'sisaMenitKerja' => $sisaMenitKerja,
                'totalLembur' => $totalLembur,
                'totalProrate' => $totalProrate,
                'totalWfh' => $wfh,
            ];
        }

        return compact(
            'namabulan', 'bulan', 'tahun', 'unitData', 'dataKaryawan',
            'startDate', 'endDate'
        );
    }
}
