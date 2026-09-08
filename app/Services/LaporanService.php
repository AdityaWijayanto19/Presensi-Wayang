<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Lembur;
use App\Models\Wfh;
use App\Models\Unitperusahaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LaporanService
{
    private const NAMA_BULAN = [
        "", "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember",
    ];

    public static function getLaporanPageData(): array
    {
        $namabulan = self::NAMA_BULAN;
        $unit = Unitperusahaan::orderBy('perusahaan')->get();
        $karyawan = Karyawan::orderBy('nama_lengkap')->get();

        return compact('namabulan', 'karyawan', 'unit');
    }

    public static function cetakLaporan(Request $request)
    {
        $nik = $request->nik;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $unit = $request->unit;

        if (empty($bulan) || empty($tahun) || empty($unit) || empty($nik)) {
            return Redirect::back()->with('warning', 'Harap lengkapi seluruh filter terlebih dahulu');
        }

        $namabulan = self::NAMA_BULAN;

        $karyawan = Karyawan::with('unitperusahaan')
            ->where('karyawan.nik', $nik)
            ->first();

        if (!$karyawan) {
            return Redirect::back()->with('warning', 'Data karyawan tidak ditemukan');
        }

        $presensi = Presensi::where('nik', $nik)
            ->whereMonth('tgl_presensi', $bulan)
            ->whereYear('tgl_presensi', $tahun)
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
            ->whereMonth('tgl_lembur', $bulan)
            ->whereYear('tgl_lembur', $tahun)
            ->get()
            ->keyBy('tgl_lembur');

        $totalLembur = 0;
        $totalProrate = 0;
        foreach ($lembur as $item) {
            if ($item->durasi == 'Prorate') {
                $totalProrate++;
            } else {
                $totalLembur += (float) $item->durasi;
            }
        }

        $wfh = Wfh::where('nik', $nik)
            ->where('status', 'approved')
            ->whereMonth('tgl_wfh', $bulan)
            ->whereYear('tgl_wfh', $tahun)
            ->get()
            ->keyBy('tgl_wfh');

        $totalWfh = $wfh->count();

        if ($presensi->isEmpty()) {
            return Redirect::back()->with('warning', 'Data presensi tidak ditemukan');
        }

        $pdf = Pdf::loadView('presensi.cetaklaporan', compact(
            'bulan', 'tahun', 'namabulan', 'karyawan', 'presensi',
            'lembur', 'wfh', 'totalLembur', 'totalProrate', 'totalWfh',
            'sisaMenitKerja', 'totalJamKerja'
        ));

        return $pdf->download('Laporan_Presensi_' . $karyawan->nama_lengkap . '.pdf');
    }
}
