<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Wfh;
use App\Models\Unitperusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboardadmin()
    {
        $hariini = now('Asia/Jakarta')->format('Y-m-d');

        $rekappresensi = Presensi::selectRaw('COUNT(nik) as jmlhadir, COUNT(IF(terlambat > 0, 1, NULL)) as jmltelat')
            ->where('tgl_presensi', $hariini)
            ->first();

        $rekapizin = Izin::selectRaw('COUNT(*) as jmlizin')
            ->where('tgl_izin', $hariini)
            ->first();

        $rekaplembur = Lembur::selectRaw('COUNT(*) as jmllembur')
            ->where('tgl_lembur', $hariini)
            ->first();

        $rekapwfh = Wfh::selectRaw('COUNT(*) as jmlwfh')
            ->where('tgl_wfh', $hariini)
            ->first();

        $pendingWfh = Wfh::whereIn('status', ['pending_admin', 'pending_atasan'])->count();
        $pendingWfhAdmin = Wfh::where('status', 'pending_admin')->count();
        $pendingLaporanAdmin = Wfh::where('laporan_status', 'pending_admin')->count();
        $pendingLemburAdmin = Lembur::where('status', 'pending_admin')->count();
        $pendingLaporanLemburAdmin = Lembur::where('laporan_status', 'pending_admin')->count();
        $jmlkaryawan = Karyawan::count();

        return view('admin.index', compact(
            'rekappresensi', 'rekapizin', 'rekaplembur', 'rekapwfh',
            'jmlkaryawan', 'pendingWfh', 'pendingWfhAdmin', 'pendingLaporanAdmin',
            'pendingLemburAdmin', 'pendingLaporanLemburAdmin'
        ));
    }

    public function index()
    {
        $hariini = now('Asia/Jakarta')->format('Y-m-d');
        $bulanini = (int) now('Asia/Jakarta')->format('m');
        $tahunini = now('Asia/Jakarta')->format('Y');
        $nik = Auth::guard('karyawan')->user()->nik;

        $presensihariini = Presensi::where('nik', $nik)
            ->where('tgl_presensi', $hariini)
            ->first();

        $historibulanini = Presensi::where('nik', $nik)
            ->whereRaw('MONTH(tgl_presensi) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_presensi) = ?', [$tahunini])
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        $rekappresensi = Presensi::selectRaw('COUNT(nik) as jmlhadir, COUNT(IF(terlambat > 0, 1, NULL)) as jmltelat')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_presensi) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_presensi) = ?', [$tahunini])
            ->first();

        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        $rekapizin = Izin::selectRaw('COUNT(*) as jmlizin')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_izin) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_izin) = ?', [$tahunini])
            ->first();

        $rekaplembur = Lembur::selectRaw('COUNT(id) as jmllembur')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_lembur) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_lembur) = ?', [$tahunini])
            ->first();

        $rekapwfh = Wfh::selectRaw('COUNT(id) as jmlwfh')
            ->where('nik', $nik)
            ->whereRaw('MONTH(tgl_wfh) = ?', [$bulanini])
            ->whereRaw('YEAR(tgl_wfh) = ?', [$tahunini])
            ->first();

    $wfhSaya = Wfh::where('nik', $nik)
        ->where(function ($q) {
            $q->whereIn('status', ['pending_atasan', 'pending_admin', 'rejected'])
                ->orWhere(function ($q2) {
                    $q2->where('status', 'approved')
                        ->where(function ($q3) {
                            $q3->whereNull('laporan_deskripsi')
                                ->orWhere('laporan_deskripsi', '')
                                ->orWhere('laporan_status', '!=', 'approved');
                        });
                });
        })
        ->orderBy('tgl_wfh', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($w) use ($hariini) {
            $tgl = $w->tgl_wfh instanceof \Carbon\Carbon
                ? $w->tgl_wfh->format('Y-m-d')
                : (string) $w->tgl_wfh;
            $w->is_today = ($tgl === $hariini);
            return $w;
        });

        $karyawan = Karyawan::where('nik', $nik)->first();
        $pendingAtasan = collect();
        $pendingLaporanAtasan = collect();

        if (!empty($karyawan->role_approved)) {
            $pendingAtasan = Wfh::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_wfh', 'desc')
                ->get();

            $pendingLaporanAtasan = Wfh::with(['karyawan.unitperusahaan'])
                ->where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->orderBy('tgl_wfh', 'desc')
                ->get();
        }

        $besok = now('Asia/Jakarta')->addDay()->format('Y-m-d');
        $wfhBesok = Wfh::where('nik', $nik)->where('tgl_wfh', $besok)->where('status', 'approved')->first();
        $wfhHariIni = Wfh::where('nik', $nik)->where('tgl_wfh', $hariini)->where('status', 'approved')->first();
        $jamMasuk = null;
        $tglCountdown = null;
        $sudahAbsenHariIni = $presensihariini && $presensihariini->jam_in;
        if ($wfhBesok) {
            $jamMasuk = Unitperusahaan::where('unit', $karyawan->unit)->value('jam_masuk');
            if ($jamMasuk instanceof \Carbon\Carbon) {
                $jamMasuk = $jamMasuk->format('H:i:s');
            }
            $tglCountdown = date('Y-m-d', strtotime($wfhBesok->tgl_wfh));
        } elseif ($wfhHariIni && !$sudahAbsenHariIni) {
            $jamMasuk = Unitperusahaan::where('unit', $karyawan->unit)->value('jam_masuk');
            if ($jamMasuk instanceof \Carbon\Carbon) {
                $jamMasuk = $jamMasuk->format('H:i:s');
            }
            $tglCountdown = $hariini;
        }

        $lemburSaya = \App\Models\Lembur::where('nik', $nik)
            ->where(function ($q) {
                $q->whereIn('status', ['pending_atasan', 'pending_admin', 'rejected'])
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'approved')
                            ->where(function ($q3) {
                                $q3->whereNull('laporan_deskripsi')
                                    ->orWhere('laporan_deskripsi', '')
                                    ->orWhere('laporan_status', '!=', 'approved');
                            });
                    });
            })
            ->orderBy('tgl_lembur', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($l) use ($hariini) {
                $tgl = $l->tgl_lembur instanceof \Carbon\Carbon
                    ? $l->tgl_lembur->format('Y-m-d')
                    : $l->tgl_lembur;
                $l->is_today = ($tgl === $hariini);
                return $l;
            });

        $pendingAtasanLembur = collect();
        $pendingLaporanLemburAtasan = collect();
        $pendingAtasanIzin = collect();

        if (!empty($karyawan->role_approved)) {
            $pendingAtasanLembur = \App\Models\Lembur::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_lembur', 'desc')
                ->get();

            $pendingLaporanLemburAtasan = \App\Models\Lembur::with(['karyawan.unitperusahaan'])
                ->where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->orderBy('tgl_lembur', 'desc')
                ->get();

            $pendingAtasanIzin = \App\Models\Izin::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_izin', 'desc')
                ->get();
        }

        $izinSaya = \App\Models\Izin::where('nik', $nik)
            ->whereIn('status', ['pending_atasan', 'pending_admin', 'rejected'])
            ->orderBy('tgl_izin', 'desc')
            ->limit(5)
            ->get();

        $notifications = $karyawan->notifications()->latest()->take(5)->get();

        return view('karyawan.index', compact(
            'presensihariini', 'historibulanini', 'namabulan', 'bulanini', 'tahunini',
            'rekappresensi', 'rekapizin', 'rekaplembur', 'rekapwfh',
            'wfhSaya', 'pendingAtasan', 'pendingLaporanAtasan',
            'lemburSaya', 'pendingAtasanLembur', 'pendingLaporanLemburAtasan',
            'pendingAtasanIzin', 'izinSaya',
            'wfhBesok', 'wfhHariIni', 'jamMasuk', 'tglCountdown', 'sudahAbsenHariIni', 'notifications'
        ));
    }
}
