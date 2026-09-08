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
        $hariini = date('Y-m-d');

        $rekappresensi = Presensi::selectRaw('COUNT(nik) as jmlhadir, COUNT(IF(terlambat > 0, 1, NULL)) as jmltelat')
            ->where('tgl_presensi', $hariini)
            ->first();

        $rekapizin = Izin::selectRaw('COUNT(*) as jmlizin')
            ->where('tgl_izin', $hariini)
            ->whereIn('jenis_izin', ['i', 's'])
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
        $jmlkaryawan = Karyawan::count();

        return view('admin.index', compact(
            'rekappresensi', 'rekapizin', 'rekaplembur', 'rekapwfh',
            'jmlkaryawan', 'pendingWfh', 'pendingWfhAdmin', 'pendingLaporanAdmin'
        ));
    }

    public function index()
    {
        $hariini = date('Y-m-d');
        $bulanini = (int) date('m');
        $tahunini = date('Y');
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

        $rekapizin = Izin::selectRaw('SUM(IF(jenis_izin = "i" OR jenis_izin = "s", 1, 0)) as jmlizin, SUM(IF(jenis_izin = "l", 1, 0)) as jmllembur')
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
                $q->whereIn('status', ['pending_atasan', 'pending_admin'])
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'approved')
                            ->where(function ($q3) {
                                $q3->whereNull('laporan_deskripsi')->orWhere('laporan_deskripsi', '');
                            });
                    });
            })
            ->orderBy('tgl_wfh', 'desc')
            ->limit(5)
            ->get();

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

        $besok = date('Y-m-d', strtotime('+1 day'));
        $wfhBesok = Wfh::where('nik', $nik)->where('tgl_wfh', $besok)->where('status', 'approved')->first();
        $jamMasuk = null;
        if ($wfhBesok) {
            $jamMasuk = Unitperusahaan::where('unit', $karyawan->unit)->value('jam_masuk');
        }

        $notifications = $karyawan->notifications()->latest()->take(5)->get();

        return view('karyawan.index', compact(
            'presensihariini', 'historibulanini', 'namabulan', 'bulanini', 'tahunini',
            'rekappresensi', 'rekapizin', 'rekaplembur', 'rekapwfh',
            'wfhSaya', 'pendingAtasan', 'pendingLaporanAtasan',
            'wfhBesok', 'jamMasuk', 'notifications'
        ));
    }
}
