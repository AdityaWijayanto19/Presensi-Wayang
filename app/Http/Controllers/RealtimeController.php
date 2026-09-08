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
        $lastCheck = $request->last_check ?? now()->subSeconds(10);

        return response()->json([
            'new_data' => Wfh::where('id', '>', $lastId)->count() > 0,
            'updated_data' => Wfh::where('dikirim_tanggal', '>', $lastCheck)->count() > 0,
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
        $hariini = date('Y-m-d');

        $presensi = Presensi::where('nik', $nik)->where('tgl_presensi', $hariini)->first();

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

        $notifications = $karyawan->notifications()->latest()->take(10)->get()->map(fn ($n) => [
            'id' => $n->id,
            'data' => $n->data,
            'read_at' => $n->read_at,
            'created_at' => $n->created_at->diffForHumans(),
        ]);
        $unreadCount = $karyawan->notifications()->whereNull('read_at')->count();

        return response()->json([
            'presensi' => $presensi,
            'wfhSaya' => $wfhSaya,
            'pendingAtasan' => $pendingAtasan,
            'pendingLaporanAtasan' => $pendingLaporanAtasan,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
