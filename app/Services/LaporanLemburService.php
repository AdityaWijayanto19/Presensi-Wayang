<?php

namespace App\Services;

use App\Enums\Jabatan;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Unitperusahaan;
use App\Services\Shared\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class LaporanLemburService
{
    private const VIEW = 'admin.presensi.cetaklaporan-lembur-gabung';

    /**
     * Gabungan laporan lembur (PDF) per karyawan pada periode cut-off.
     * Cut-off sama dengan rekap laporan presensi: 21 bulan-1 s/d 20 bulan.
     */
    public function buildGabunganData(Request $request): array|RedirectResponse
    {
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');
        $unit = $request->input('unit');
        $niks = array_values(array_unique(array_filter((array) $request->input('niks', []))));

        if (empty($bulan) || empty($tahun) || empty($unit) || empty($niks)) {
            return Redirect::back()->with('warning', 'Harap lengkapi seluruh filter terlebih dahulu');
        }

        $unitData = Unitperusahaan::where('unit', $unit)->first();
        if (!$unitData) {
            return Redirect::back()->with('warning', 'Data unit perusahaan tidak ditemukan');
        }

        [$startDate, $endDate] = LaporanService::getCutoffDates((int) $bulan, (int) $tahun);

        $karyawans = Karyawan::where('unit', $unit)
            ->whereIn('nik', $niks)
            ->orderBy('nama_lengkap')
            ->get();

        if ($karyawans->isEmpty()) {
            return Redirect::back()->with('warning', 'Data karyawan tidak ditemukan');
        }

        $lemburs = Lembur::with('karyawan')
            ->whereIn('nik', $karyawans->pluck('nik'))
            ->where('status', 'approved')
            ->where('laporan_status', 'approved')
            ->whereBetween('tgl_lembur', [$startDate, $endDate])
            ->orderBy('tgl_lembur')
            ->get()
            ->groupBy('nik');

        $stempelPath = app(PdfService::class)->getStempelPath();

        $daftar = [];
        $totalLaporan = 0;
        $totalDurasi = 0.0;

        foreach ($karyawans as $karyawan) {
            $jabatan = $karyawan->jabatan instanceof Jabatan ? $karyawan->jabatan->value : $karyawan->jabatan;

            $reports = [];
            $durasi = 0.0;

            foreach ($lemburs->get($karyawan->nik, collect()) as $lembur) {
                $pdfData = LemburService::buildLaporanPdfData($lembur);
                if (empty($pdfData)) {
                    continue;
                }
                $pdfData['stempelPath'] = $stempelPath;
                $reports[] = $pdfData;
                $durasi += (float) $lembur->durasi_jam;
            }

            $daftar[] = [
                'nik' => $karyawan->nik,
                'nama_lengkap' => $karyawan->nama_lengkap,
                'jabatan' => $jabatan,
                'jumlah' => count($reports),
                'total_durasi' => $durasi,
                'reports' => $reports,
            ];

            $totalLaporan += count($reports);
            $totalDurasi += $durasi;
        }

        if ($totalLaporan === 0) {
            return Redirect::back()->with(
                'warning',
                'Tidak ada laporan lembur yang disetujui pada periode ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')
            );
        }

        return [
            'unitData' => $unitData,
            'daftar' => $daftar,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'bulan' => (int) $bulan,
            'tahun' => (int) $tahun,
            'totalLaporan' => $totalLaporan,
            'totalDurasi' => $totalDurasi,
        ];
    }

    public function cetakLaporan(Request $request): mixed
    {
        $data = $this->buildGabunganData($request);
        if ($data instanceof RedirectResponse) {
            return $data;
        }

        return Pdf::loadView(self::VIEW, $data)->download($this->filename($data));
    }

    public function previewLaporan(Request $request): mixed
    {
        $data = $this->buildGabunganData($request);
        if ($data instanceof RedirectResponse) {
            return $data;
        }

        return Pdf::loadView(self::VIEW, $data)->stream($this->filename($data));
    }

    private function filename(array $data): string
    {
        $unit = str_replace(' ', '_', trim($data['unitData']->unit));

        return sprintf(
            'Laporan_Lembur_%s_%d-%02d.pdf',
            $unit,
            $data['tahun'],
            $data['bulan']
        );
    }
}
