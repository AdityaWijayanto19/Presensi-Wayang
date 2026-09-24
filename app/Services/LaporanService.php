<?php

namespace App\Services;

use App\Models\Cuti;
use App\Models\Izin;
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

    private const NAMA_HARI = [
        1 => "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu",
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
            ->get()
            ->keyBy(fn ($item) => $item->tgl_presensi->format('Y-m-d'));

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
        foreach ($lembur as $item) {
            $totalLembur += (float) $item->durasi_jam;
        }

        $izin = Izin::where('nik', $nik)
            ->where('status', 'approved')
            ->whereBetween('tgl_izin', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->tgl_izin->format('Y-m-d'));

        $wfh = Wfh::where('nik', $nik)
            ->whereIn('status', ['approved', 'rejected', 'unpaid'])
            ->whereBetween('tgl_wfh', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->tgl_wfh->format('Y-m-d'));

        $cutiDates = [];
        $startKey = $startDate->format('Y-m-d');
        $endKey = $endDate->format('Y-m-d');
        foreach (Cuti::where('nik', $nik)->get() as $cuti) {
            foreach ((array) $cuti->tanggal_cuti as $tgl) {
                $ts = strtotime($tgl);
                if ($ts === false) {
                    continue;
                }
                $key = date('Y-m-d', $ts);
                if ($key >= $startKey && $key <= $endKey) {
                    $cutiDates[$key] = true;
                }
            }
        }

        $totalWfh = $wfh->filter(fn ($item) => $item->status->value === 'approved')->count();

        $days = [];
        $cursor = $startDate->copy()->startOfDay();
        $last = $endDate->copy()->startOfDay();
        while ($cursor->lte($last)) {
            $key = $cursor->format('Y-m-d');
            $p = $presensi->get($key);
            $l = $lembur->get($key);
            $i = $izin->get($key);
            $w = $wfh->get($key);
            $isMinggu = $cursor->dayOfWeek === Carbon::SUNDAY;
            $isCuti = isset($cutiDates[$key]);

            if ($p) {
                if ($p->jam_out == null) {
                    $keterangan = 'Belum Absen Pulang';
                } elseif ($p->terlambat > 0) {
                    $keterangan = 'Terlambat ' . $p->terlambat . ' Menit';
                } else {
                    $keterangan = 'Tepat Waktu';
                }
            } elseif ($i) {
                $keterangan = $i->jenis_izin->label();
            } elseif ($w && $w->status->value === 'approved') {
                $keterangan = 'WFH';
            } elseif ($isCuti) {
                $keterangan = 'Cuti';
            } elseif ($isMinggu) {
                $keterangan = 'Libur';
            } else {
                $keterangan = 'Tidak Masuk Kerja';
            }

            $days[] = [
                'tanggal' => $cursor->copy(),
                'hari' => self::NAMA_HARI[$cursor->dayOfWeekIso],
                'is_minggu' => $isMinggu,
                'presensi' => $p,
                'lembur' => $l,
                'lembur_jam' => $l ? self::formatJam((float) $l->durasi_jam) : null,
                'izin' => $i,
                'wfh' => $w,
                'is_cuti' => $isCuti,
                'keterangan' => $keterangan,
            ];

            $cursor->addDay();
        }

        return compact(
            'bulan', 'tahun', 'namabulan', 'karyawan', 'days',
            'lembur', 'wfh', 'totalLembur', 'totalWfh',
            'sisaMenitKerja', 'totalJamKerja', 'startDate', 'endDate'
        );
    }

    private static function formatJam(float $jam): string
    {
        $formatted = rtrim(rtrim(number_format($jam, 1, '.', ''), '0'), '.');

        return str_replace('.', ',', $formatted);
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

        $niks = $karyawans->pluck('nik');
        $startKey = $startDate->format('Y-m-d');
        $endKey = $endDate->format('Y-m-d');

        $presensis = Presensi::whereIn('nik', $niks)
            ->whereBetween('tgl_presensi', [$startDate, $endDate])
            ->get()
            ->groupBy('nik');

        $lemburs = Lembur::whereIn('nik', $niks)
            ->where('status', 'approved')
            ->whereBetween('tgl_lembur', [$startDate, $endDate])
            ->get()
            ->groupBy('nik');

        $wfhs = Wfh::whereIn('nik', $niks)
            ->where('status', 'approved')
            ->whereBetween('tgl_wfh', [$startDate, $endDate])
            ->get()
            ->groupBy('nik');

        $wfhUnpaid = Wfh::whereIn('nik', $niks)
            ->where('status', 'unpaid')
            ->whereBetween('tgl_wfh', [$startDate, $endDate])
            ->get()
            ->groupBy('nik');

        $izinGroups = Izin::whereIn('nik', $niks)
            ->where('status', 'approved')
            ->whereBetween('tgl_izin', [$startDate, $endDate])
            ->get()
            ->groupBy('nik');

        $cutiDates = [];
        foreach (Cuti::whereIn('nik', $niks)->get() as $cuti) {
            foreach ((array) $cuti->tanggal_cuti as $tgl) {
                $ts = strtotime($tgl);
                if ($ts === false) {
                    continue;
                }
                $key = date('Y-m-d', $ts);
                if ($key >= $startKey && $key <= $endKey) {
                    $cutiDates[$cuti->nik][$key] = true;
                }
            }
        }

        $dataKaryawan = [];
        $grand = [
            'totalHadir' => 0,
            'totalMenitKerja' => 0,
            'totalLembur' => 0.0,
            'totalProrate' => 0,
            'totalWfh' => 0,
            'totalTerlambatMenit' => 0,
            'totalIzin' => 0,
            'totalSakit' => 0,
            'totalUnpaid' => 0,
            'totalCuti' => 0,
        ];

        foreach ($karyawans as $k) {
            $nikCuti = $cutiDates[$k->nik] ?? [];
            $isCutiDate = fn (string $key): bool => isset($nikCuti[$key]);

            $totalHadir = 0;
            $totalMenitKerja = 0;
            $totalTerlambatMenit = 0;

            foreach ($presensis->get($k->nik, collect()) as $p) {
                if ($isCutiDate($p->tgl_presensi->format('Y-m-d'))) {
                    continue;
                }

                $totalHadir++;
                $totalTerlambatMenit += (int) $p->terlambat;

                if ($p->jam_out != null) {
                    $awal = strtotime($p->jam_in);
                    $akhir = strtotime($p->jam_out);
                    $selisih = ($akhir - $awal) / 60;
                    $totalMenitKerja += $selisih;
                }
            }

            $totalJamKerja = floor($totalMenitKerja / 60);
            $sisaMenitKerja = $totalMenitKerja % 60;

            $totalLembur = 0;
            $totalProrate = 0;
            foreach ($lemburs->get($k->nik, collect()) as $item) {
                if ($item->durasi_jam > 5) {
                    $totalProrate++;
                } else {
                    $totalLembur += (float) $item->durasi_jam;
                }
            }

            $totalWfh = $wfhs->get($k->nik, collect())->count();
            $totalUnpaid = $wfhUnpaid->get($k->nik, collect())->count();

            $totalIzin = 0;
            $totalSakit = 0;
            foreach ($izinGroups->get($k->nik, collect()) as $item) {
                if ($item->jenis_izin->value === 'sakit') {
                    $totalSakit++;
                } else {
                    $totalIzin++;
                }
            }

            $totalCuti = count($nikCuti);

            $dataKaryawan[] = [
                'nik' => $k->nik,
                'nama_lengkap' => $k->nama_lengkap,
                'jabatan' => $k->jabatan,
                'totalHadir' => $totalHadir,
                'totalJamKerja' => $totalJamKerja,
                'sisaMenitKerja' => $sisaMenitKerja,
                'totalLembur' => $totalLembur,
                'totalProrate' => $totalProrate,
                'totalWfh' => $totalWfh,
                'totalTerlambatMenit' => $totalTerlambatMenit,
                'totalIzin' => $totalIzin,
                'totalSakit' => $totalSakit,
                'totalUnpaid' => $totalUnpaid,
                'totalCuti' => $totalCuti,
            ];

            $grand['totalHadir'] += $totalHadir;
            $grand['totalMenitKerja'] += $totalMenitKerja;
            $grand['totalLembur'] += $totalLembur;
            $grand['totalProrate'] += $totalProrate;
            $grand['totalWfh'] += $totalWfh;
            $grand['totalTerlambatMenit'] += $totalTerlambatMenit;
            $grand['totalIzin'] += $totalIzin;
            $grand['totalSakit'] += $totalSakit;
            $grand['totalUnpaid'] += $totalUnpaid;
            $grand['totalCuti'] += $totalCuti;
        }

        $grandTotal = [
            'totalHadir' => $grand['totalHadir'],
            'totalJamKerja' => (int) floor($grand['totalMenitKerja'] / 60),
            'sisaMenitKerja' => (int) ($grand['totalMenitKerja'] % 60),
            'totalLembur' => $grand['totalLembur'],
            'totalProrate' => $grand['totalProrate'],
            'totalWfh' => $grand['totalWfh'],
            'totalTerlambatMenit' => $grand['totalTerlambatMenit'],
            'totalIzin' => $grand['totalIzin'],
            'totalSakit' => $grand['totalSakit'],
            'totalUnpaid' => $grand['totalUnpaid'],
            'totalCuti' => $grand['totalCuti'],
        ];

        return compact(
            'namabulan', 'bulan', 'tahun', 'unitData', 'dataKaryawan', 'grandTotal',
            'startDate', 'endDate'
        );
    }
}
