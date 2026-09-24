<?php

namespace App\Http\Controllers;

use App\Enums\JenisIzin;
use App\Models\Cuti;
use App\Models\Izin;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboardadmin()
    {
        $now = now('Asia/Jakarta');
        $hariini = $now->format('Y-m-d');
        $bulanini = (int) $now->format('m');
        $tahunini = (int) $now->format('Y');

        $hariNama = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggalIndo = $hariNama[(int) $now->format('w')].', '.(int) $now->format('j').' '.$bulanNama[(int) $now->format('n')].' '.$now->format('Y');
        $jamSekarang = (int) $now->format('G');
        $sapaan = match (true) {
            $jamSekarang < 11 => 'Pagi',
            $jamSekarang < 15 => 'Siang',
            $jamSekarang < 19 => 'Sore',
            default => 'Malam',
        };

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

        $rekapcuti = Cuti::selectRaw('COUNT(*) as jmlcuti')
            ->whereJsonContains('tanggal_cuti', $hariini)
            ->first();

        $cutiHariIniList = Cuti::with('karyawan')
            ->whereJsonContains('tanggal_cuti', $hariini)
            ->get();

        $pendingWfh = Wfh::whereIn('status', ['pending_admin', 'pending_atasan'])->count();
        $pendingWfhAdmin = Wfh::where('status', 'pending_admin')->count();
        $pendingLaporanAdmin = Wfh::where('laporan_status', 'pending_admin')->count();
        $pendingLemburAdmin = Lembur::where('status', 'pending_admin')->count();
        $pendingLaporanLemburAdmin = Lembur::where('laporan_status', 'pending_admin')->count();
        $pendingIzinAdmin = Izin::where('status', 'pending_admin')->count();
        $jmlkaryawan = Karyawan::count();

        // ── Chart: kehadiran 7 hari terakhir ──────────────────────────
        $labelHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $labels7 = [];
        $hadir7 = [];
        $telat7 = [];

        $mulai7 = $now->copy()->subDays(6)->format('Y-m-d');
        $hadirPerHari = Presensi::selectRaw('tgl_presensi, COUNT(nik) as hadir, COUNT(IF(terlambat > 0, 1, NULL)) as telat')
            ->whereBetween('tgl_presensi', [$mulai7, $hariini])
            ->groupBy('tgl_presensi')
            ->get()
            ->keyBy(function ($row) {
                $t = $row->tgl_presensi;

                return $t instanceof Carbon ? $t->format('Y-m-d') : (string) $t;
            });

        for ($i = 6; $i >= 0; $i--) {
            $tgl = $now->copy()->subDays($i);
            $key = $tgl->format('Y-m-d');
            $row = $hadirPerHari->get($key);
            $labels7[] = $labelHari[(int) $tgl->format('w')];
            $hadir7[] = (int) ($row->hadir ?? 0);
            $telat7[] = (int) ($row->telat ?? 0);
        }

        // ── Chart: karyawan per unit ──────────────────────────────────
        $namaUnit = Unitperusahaan::pluck('perusahaan', 'unit');
        $unitChart = Karyawan::selectRaw('unit, COUNT(*) as jml')
            ->groupBy('unit')
            ->orderByDesc('jml')
            ->get()
            ->map(fn ($row) => [
                'label' => $namaUnit[$row->unit] ?? $row->unit,
                'jml' => (int) $row->jml,
            ]);

        // ── Chart: penggunaan cuti tahun ini ──────────────────────────
        $totalJatahCuti = (int) Karyawan::sum('jatah_cuti');
        $cutiTerpakaiTahun = 0;
        foreach (Cuti::all() as $cuti) {
            foreach ((array) $cuti->tanggal_cuti as $tglCuti) {
                if (str_starts_with((string) $tglCuti, $tahunini.'-')) {
                    $cutiTerpakaiTahun++;
                }
            }
        }
        $cutiSisaTahun = max(0, $totalJatahCuti - $cutiTerpakaiTahun);

        // ── Chart: status pengajuan bulan ini (WFH / Lembur / Izin) ──
        $collapseStatus = function ($rows) {
            $out = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
            foreach ($rows as $status => $count) {
                $status = $status instanceof \BackedEnum ? $status->value : (string) $status;
                if (in_array($status, ['approved', 'unpaid'], true)) {
                    $out['approved'] += (int) $count;
                } elseif ($status === 'rejected') {
                    $out['rejected'] += (int) $count;
                } else {
                    $out['pending'] += (int) $count;
                }
            }

            return $out;
        };

        $statusChart = [
            'wfh' => $collapseStatus(
                Wfh::whereMonth('tgl_wfh', $bulanini)
                    ->whereYear('tgl_wfh', $tahunini)
                    ->selectRaw('status, COUNT(*) as jml')
                    ->groupBy('status')
                    ->pluck('jml', 'status')
            ),
            'lembur' => $collapseStatus(
                Lembur::whereMonth('tgl_lembur', $bulanini)
                    ->whereYear('tgl_lembur', $tahunini)
                    ->selectRaw('status, COUNT(*) as jml')
                    ->groupBy('status')
                    ->pluck('jml', 'status')
            ),
            'izin' => $collapseStatus(
                Izin::whereMonth('tgl_izin', $bulanini)
                    ->whereYear('tgl_izin', $tahunini)
                    ->selectRaw('status, COUNT(*) as jml')
                    ->groupBy('status')
                    ->pluck('jml', 'status')
            ),
        ];

        // ── Pengajuan terbaru (feed) ──────────────────────────────────
        $labelBadge = function ($s) {
            $s = $s instanceof \BackedEnum ? $s->value : (string) $s;

            return match ($s) {
                'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 border border-rose-200'],
                'pending_admin' => ['Menunggu HR', 'bg-blue-50 text-blue-700 border border-blue-200'],
                'pending_atasan' => ['Menunggu Atasan', 'bg-amber-50 text-amber-700 border border-amber-200'],
                default => ['Baru', 'bg-slate-100 text-slate-600 border border-slate-200'],
            };
        };

        $formatTanggal = function ($tgl) {
            if (! $tgl) {
                return '-';
            }
            if ($tgl instanceof Carbon) {
                return $tgl->format('d M Y');
            }

            return (string) $tgl;
        };

        $feed = collect();

        foreach (Wfh::with('karyawan')->latest('dikirim_tanggal')->take(5)->get() as $r) {
            [$bl, $bc] = $labelBadge($r->status);
            $feed->push([
                'jenis' => 'WFH',
                'nama' => optional($r->karyawan)->nama_lengkap ?? $r->nik,
                'detail' => $formatTanggal($r->tgl_wfh),
                'waktu' => optional($r->dikirim_tanggal)->format('d M H:i') ?? '-',
                'badge_label' => $bl,
                'badge_class' => $bc,
                'icon' => 'home',
                'color' => 'bg-blue-50 text-blue-600',
                'sort' => optional($r->dikirim_tanggal)->getTimestamp() ?? 0,
            ]);
        }

        foreach (Lembur::with('karyawan')->latest('dikirim_tanggal')->take(5)->get() as $r) {
            [$bl, $bc] = $labelBadge($r->status);
            $feed->push([
                'jenis' => 'Lembur',
                'nama' => optional($r->karyawan)->nama_lengkap ?? $r->nik,
                'detail' => $formatTanggal($r->tgl_lembur),
                'waktu' => optional($r->dikirim_tanggal)->format('d M H:i') ?? '-',
                'badge_label' => $bl,
                'badge_class' => $bc,
                'icon' => 'briefcase',
                'color' => 'bg-orange-50 text-orange-600',
                'sort' => optional($r->dikirim_tanggal)->getTimestamp() ?? 0,
            ]);
        }

        foreach (Izin::with('karyawan')->latest('dikirim_tanggal')->take(5)->get() as $r) {
            [$bl, $bc] = $labelBadge($r->status);
            $jenisIzin = $r->jenis_izin instanceof JenisIzin
                ? $r->jenis_izin->label()
                : (string) $r->jenis_izin;
            $feed->push([
                'jenis' => 'Izin',
                'nama' => optional($r->karyawan)->nama_lengkap ?? $r->nik,
                'detail' => $jenisIzin.' · '.$formatTanggal($r->tgl_izin),
                'waktu' => optional($r->dikirim_tanggal)->format('d M H:i') ?? '-',
                'badge_label' => $bl,
                'badge_class' => $bc,
                'icon' => 'file-text',
                'color' => 'bg-yellow-50 text-yellow-600',
                'sort' => optional($r->dikirim_tanggal)->getTimestamp() ?? 0,
            ]);
        }

        foreach (Cuti::with('karyawan')->latest('dikirim_tanggal')->take(5)->get() as $r) {
            [$bl, $bc] = $labelBadge(null);
            $feed->push([
                'jenis' => 'Cuti',
                'nama' => optional($r->karyawan)->nama_lengkap ?? $r->nik,
                'detail' => ($r->keterangan ?: 'Cuti').' · '.$r->durasi_hari.' hari',
                'waktu' => optional($r->dikirim_tanggal)->format('d M H:i') ?? '-',
                'badge_label' => $bl,
                'badge_class' => $bc,
                'icon' => 'palmtree',
                'color' => 'bg-purple-50 text-purple-600',
                'sort' => optional($r->dikirim_tanggal)->getTimestamp() ?? 0,
            ]);
        }

        $pengajuanTerbaru = $feed->sortByDesc('sort')->take(8)->values();

        return view('admin.index', compact(
            'rekappresensi', 'rekapizin', 'rekaplembur', 'rekapwfh', 'rekapcuti',
            'cutiHariIniList', 'jmlkaryawan', 'pendingWfh', 'pendingWfhAdmin', 'pendingLaporanAdmin',
            'pendingLemburAdmin', 'pendingLaporanLemburAdmin', 'pendingIzinAdmin',
            'tanggalIndo', 'sapaan',
            'labels7', 'hadir7', 'telat7',
            'unitChart', 'totalJatahCuti', 'cutiTerpakaiTahun', 'cutiSisaTahun',
            'statusChart', 'pengajuanTerbaru'
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

        $namabulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

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
                $tgl = $w->tgl_wfh instanceof Carbon
                    ? $w->tgl_wfh->format('Y-m-d')
                    : (string) $w->tgl_wfh;
                $w->is_today = ($tgl === $hariini);

                return $w;
            });

        $karyawan = Karyawan::where('nik', $nik)->first();
        $pendingAtasan = collect();
        $pendingLaporanAtasan = collect();

        if (! empty($karyawan->role_approved)) {
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
            if ($jamMasuk instanceof Carbon) {
                $jamMasuk = $jamMasuk->format('H:i:s');
            }
            $tglCountdown = date('Y-m-d', strtotime($wfhBesok->tgl_wfh));
        } elseif ($wfhHariIni && ! $sudahAbsenHariIni) {
            $jamMasuk = Unitperusahaan::where('unit', $karyawan->unit)->value('jam_masuk');
            if ($jamMasuk instanceof Carbon) {
                $jamMasuk = $jamMasuk->format('H:i:s');
            }
            $tglCountdown = $hariini;
        }

        $lemburSaya = Lembur::where('nik', $nik)
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
                $tgl = $l->tgl_lembur instanceof Carbon
                    ? $l->tgl_lembur->format('Y-m-d')
                    : $l->tgl_lembur;
                $l->is_today = ($tgl === $hariini);

                return $l;
            });

        $pendingAtasanLembur = collect();
        $pendingLaporanLemburAtasan = collect();
        $pendingAtasanIzin = collect();

        if (! empty($karyawan->role_approved)) {
            $pendingAtasanLembur = Lembur::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_lembur', 'desc')
                ->get();

            $pendingLaporanLemburAtasan = Lembur::with(['karyawan.unitperusahaan'])
                ->where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->orderBy('tgl_lembur', 'desc')
                ->get();

            $pendingAtasanIzin = Izin::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_izin', 'desc')
                ->get();
        }

        $izinSaya = Izin::where('nik', $nik)
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
