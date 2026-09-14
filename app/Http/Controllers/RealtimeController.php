<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Wfh;
use App\Models\Presensi;
use App\Models\Unitperusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RealtimeController extends Controller
{
    public function admin()
    {
        $pendingWfh = Wfh::where('status', 'pending_admin')->count();
        $pendingLaporan = Wfh::where('laporan_status', 'pending_admin')->count();

        return response()->json([
            'pending_wfh' => $pendingWfh,
            'pending_laporan' => $pendingLaporan,
            'total_pending' => $pendingWfh + $pendingLaporan,
        ]);
    }

    public function adminWfhCheck(Request $request)
    {
        $lastId = $request->last_id ?? 0;
        $lastCheck = $request->last_check ?? now('Asia/Jakarta')->subSeconds(10);

        return response()->json([
            'new_data' => Wfh::where('id', '>', $lastId)->count() > 0,
            'updated_data' => Wfh::where('dikirim_tanggal', '>', $lastCheck)
                ->orWhere('updated_at', '>', $lastCheck)
                ->count() > 0,
            'latest_id' => Wfh::max('id'),
        ]);
    }

    public function adminWfhData(Request $request)
    {
        $query = Wfh::with(['karyawan.unitperusahaan', 'atasan']);

        if (!empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
            });
        }
        if (!empty($request->unit)) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('unit', $request->unit);
            });
        }
        if (!empty($request->tanggal)) {
            $query->where('tgl_wfh', $request->tanggal);
        }
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        $datawfh = $query->orderBy('tgl_wfh', 'desc')->paginate(5)->withQueryString();
        $html = view('admin.wfh._rows', compact('datawfh'))->render();
        $pagination = $datawfh->setPath('/panel/wfh')->appends($request->query())->links('vendor.pagination.bootstrap-5')->render();

        return response()->json([
            'html' => $html,
            'pagination' => $pagination,
            'total' => $datawfh->total(),
        ]);
    }

    public function dashboard()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $hariini = now('Asia/Jakarta')->format('Y-m-d');
        $bulanini = (int) now('Asia/Jakarta')->format('m');
        $tahunini = now('Asia/Jakarta')->format('Y');

        $presensi = Presensi::where('nik', $nik)->where('tgl_presensi', $hariini)
            ->select('id', 'nik', 'tgl_presensi', 'jam_in', 'jam_out', 'foto_in', 'foto_out', 'terlambat')
            ->first();

        if ($presensi) {
            $tgl = $presensi->tgl_presensi instanceof \Carbon\Carbon
                ? $presensi->tgl_presensi->format('Y-m-d')
                : $presensi->tgl_presensi;
            $presensi = $presensi->toArray();
            $presensi['tgl_presensi'] = $tgl;
        }

        $rekap = [
            'hadir' => Presensi::where('nik', $nik)
                ->whereMonth('tgl_presensi', $bulanini)
                ->whereYear('tgl_presensi', $tahunini)
                ->count(),
            'wfh' => Wfh::where('nik', $nik)
                ->whereMonth('tgl_wfh', $bulanini)
                ->whereYear('tgl_wfh', $tahunini)
                ->count(),
            'lembur' => \App\Models\Lembur::where('nik', $nik)
                ->whereMonth('tgl_lembur', $bulanini)
                ->whereYear('tgl_lembur', $tahunini)
                ->count(),
            'izin' => \App\Models\Izin::where('nik', $nik)
                ->whereMonth('tgl_izin', $bulanini)
                ->whereYear('tgl_izin', $tahunini)
                ->whereIn('jenis_izin', ['i', 's'])
                ->count(),
        ];

        $histori = Presensi::where('nik', $nik)
            ->whereMonth('tgl_presensi', $bulanini)
            ->whereYear('tgl_presensi', $tahunini)
            ->select('tgl_presensi', 'jam_in', 'jam_out', 'foto_in', 'terlambat')
            ->orderBy('tgl_presensi', 'desc')
            ->limit(15)
            ->get()
            ->map(function ($d) {
                $tgl = $d->tgl_presensi instanceof \Carbon\Carbon
                    ? $d->tgl_presensi->format('Y-m-d')
                    : $d->tgl_presensi;
                $arr = $d->toArray();
                $arr['tgl_presensi'] = $tgl;
                return $arr;
            });

        $wfhSaya = Wfh::where('nik', $nik)
            ->where(function ($q) {
                $q->whereIn('status', ['pending_atasan', 'pending_admin', 'rejected'])
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'approved')
                            ->where(function ($q3) {
                                $q3->whereNull('laporan_deskripsi')->orWhere('laporan_deskripsi', '');
                            });
                    });
            })
            ->orderBy('tgl_wfh', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($w) use ($hariini) {
                $tgl = $w->tgl_wfh instanceof \Carbon\Carbon
                    ? $w->tgl_wfh->format('Y-m-d')
                    : $w->tgl_wfh;
                $arr = $w->toArray();
                $arr['tgl_wfh'] = $tgl;
                $arr['is_today'] = ($tgl === $hariini);
                return $arr;
            });

        $karyawan = Karyawan::where('nik', $nik)->first();
        $pendingAtasan = collect();
        $pendingLaporanAtasan = collect();

        if (!empty($karyawan->role_approved)) {
            $pendingAtasan = Wfh::with(['karyawan.unitperusahaan'])
                ->where('atasan_nik', $nik)
                ->where('status', 'pending_atasan')
                ->orderBy('tgl_wfh', 'desc')
                ->get()
                ->map(function ($w) {
                    $tgl = $w->tgl_wfh instanceof \Carbon\Carbon
                        ? $w->tgl_wfh->format('Y-m-d')
                        : $w->tgl_wfh;
                    $arr = $w->toArray();
                    $arr['tgl_wfh'] = $tgl;
                    return $arr;
                });

            $pendingLaporanAtasan = Wfh::with(['karyawan.unitperusahaan'])
                ->where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->orderBy('tgl_wfh', 'desc')
                ->get()
                ->map(function ($w) {
                    $tgl = $w->tgl_wfh instanceof \Carbon\Carbon
                        ? $w->tgl_wfh->format('Y-m-d')
                        : $w->tgl_wfh;
                    $arr = $w->toArray();
                    $arr['tgl_wfh'] = $tgl;
                    return $arr;
                });
        }

        $notifications = $karyawan->notifications()->latest()->take(10)->get()->map(fn ($n) => [
            'id' => $n->id,
            'data' => $n->data,
            'read_at' => $n->read_at,
            'created_at' => $n->created_at->diffForHumans(),
        ]);
        $unreadCount = $karyawan->notifications()->whereNull('read_at')->count();

        return response()->json([
            'presensi' => $presensi,
            'rekap' => $rekap,
            'histori' => $histori,
            'wfhSaya' => $wfhSaya,
            'pendingAtasan' => $pendingAtasan,
            'pendingLaporanAtasan' => $pendingLaporanAtasan,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
