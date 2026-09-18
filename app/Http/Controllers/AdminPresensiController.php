<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Wfh;
use App\Enums\WfhStatus;
use App\Models\Unitperusahaan;
use App\Services\WfhService;
use App\Services\IzinService;
use App\Services\LemburService;
use App\Services\MonitoringService;
use App\Services\LaporanService;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\Presensi\UpdatePresensiAdminRequest;
use App\Http\Requests\Presensi\UpdateIzinAdminRequest;
use App\Http\Requests\Presensi\UpdateLemburAdminRequest;
use App\Http\Requests\Presensi\UpdateWfhAdminRequest;
use Illuminate\Http\Request;

class AdminPresensiController extends Controller
{
    public function monitoring()
    {
        $unitperusahaan = MonitoringService::getUnitPerusahaan();
        return view('admin.presensi.index', compact('unitperusahaan'));
    }

    public function getpresensi(Request $request)
    {
        $presensi = MonitoringService::getPresensi($request);
        return view('partials.getpresensi', compact('presensi'));
    }

    public function tampilkanpetamasuk(Request $request)
    {
        $presensi = MonitoringService::getPresensiById($request->id);
        return view('partials.showmapin', compact('presensi'));
    }

    public function tampilkanpetapulang(Request $request)
    {
        $presensi = MonitoringService::getPresensiById($request->id);
        return view('partials.showmapout', compact('presensi'));
    }

    public function laporan()
    {
        $data = LaporanService::getLaporanPageData();
        return view('admin.presensi.laporan', $data);
    }

    public function getkaryawanbyunit(Request $request)
    {
        $karyawan = MonitoringService::getKaryawanByUnit($request->unit);
        return response()->json($karyawan);
    }

    public function cetaklaporan(Request $request)
    {
        return LaporanService::cetakLaporan($request);
    }

    public function previewLaporan(Request $request)
    {
        return LaporanService::previewLaporan($request);
    }

    // ==================== DATA IZIN ====================

    public function dataizin(Request $request, IzinService $izinService)
    {
        $dataizin = $izinService->getDataIzinAdmin($request);
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.izin.index', compact('dataizin', 'unitperusahaan'));
    }

    public function approveIzinAdmin(int $id, IzinService $izinService)
    {
        $result = $izinService->approveIzinAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectIzinAdmin(RejectRequest $request, int $id, IzinService $izinService)
    {
        $result = $izinService->rejectIzinAdmin($id, $request->rejected_reason);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function deleteizinadmin(int $id, IzinService $izinService)
    {
        $result = $izinService->deleteIzinAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ==================== DATA LEMBUR ====================

    public function datalembur(Request $request, LemburService $lemburService)
    {
        $data = $lemburService->getDataLemburAdmin($request);
        extract($data);
        return view('admin.lembur.index', compact('datalembur', 'unitperusahaan', 'pendingLemburAdmin', 'pendingLaporanAdmin'));
    }

    public function deletelemburadmin(int $id, LemburService $lemburService)
    {
        $result = $lemburService->deleteLemburAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveLemburAdmin(int $id, LemburService $lemburService)
    {
        $result = $lemburService->approveLemburAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLemburAdmin(RejectRequest $request, int $id, LemburService $lemburService)
    {
        $result = $lemburService->rejectLemburAdmin($id, $request->rejected_reason);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveLaporanLemburAdmin(int $id, LemburService $lemburService)
    {
        $result = $lemburService->approveLaporanAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanLemburAdmin(RejectRequest $request, int $id, LemburService $lemburService)
    {
        $result = $lemburService->rejectLaporanAdmin($id, $request->rejected_reason);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function editLemburAdmin(int $id)
    {
        $lembur = Lembur::with(['karyawan', 'atasan'])->findOrFail($id);
        $arr = $lembur->toArray();
        $arr['tgl_lembur'] = $lembur->tgl_lembur instanceof \Carbon\Carbon
            ? $lembur->tgl_lembur->format('Y-m-d')
            : $lembur->tgl_lembur;
        $arr['dikirim_tanggal'] = $lembur->dikirim_tanggal instanceof \Carbon\Carbon
            ? $lembur->dikirim_tanggal->format('Y-m-d H:i')
            : $lembur->dikirim_tanggal;
        $arr['approved_at'] = $lembur->approved_at instanceof \Carbon\Carbon
            ? $lembur->approved_at->format('Y-m-d H:i')
            : $lembur->approved_at;
        $arr['laporan_approved_at'] = $lembur->laporan_approved_at instanceof \Carbon\Carbon
            ? $lembur->laporan_approved_at->format('Y-m-d H:i')
            : $lembur->laporan_approved_at;
        return response()->json($arr);
    }

    public function updateLemburAdmin(UpdateLemburAdminRequest $request, int $id)
    {
        $lembur = Lembur::findOrFail($id);
        $oldStatus = $lembur->status instanceof \App\Enums\LemburStatus ? $lembur->status->value : $lembur->status;
        $newStatus = $request->status;

        $durasiJam = $request->durasi_jam == '5.5' ? 5.5 : (float) $request->durasi_jam;

        $updateData = [
            'tgl_lembur' => $request->tgl_lembur,
            'keterangan' => $request->keterangan,
            'status' => $newStatus,
            'durasi_jam' => $durasiJam,
        ];

        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'approved') {
                $updateData['admin_status'] = 'approved';
                if (empty($lembur->approved_at)) {
                    $updateData['approved_at'] = now('Asia/Jakarta');
                }
            } elseif ($newStatus === 'rejected') {
                $updateData['admin_status'] = 'rejected';
            } elseif ($newStatus === 'pending_atasan') {
                $updateData['admin_status'] = 'pending';
                $updateData['atasan_status'] = 'pending';
            } elseif ($newStatus === 'pending_admin') {
                $updateData['admin_status'] = 'pending';
            }
        }

        $lembur->update($updateData);

        if ($oldStatus !== $newStatus) {
            $karyawan = \App\Models\Karyawan::where('nik', $lembur->nik)->first();
            if ($karyawan) {
                $karyawan->notify(new \App\Notifications\LemburStatusChanged($lembur, $oldStatus, $newStatus));
            }

            cache()->forget('pending_lembur_count');
            cache()->forget('pending_lembur_admin_count');
            cache()->forget('pending_laporan_lembur_admin_count');
        }

        return redirect()->back()->with('success', 'Data lembur berhasil diperbarui');
    }

    // ==================== DATA WFH ====================

    public function datawfh(Request $request, WfhService $wfhService)
    {
        $data = $wfhService->getDataWfhAdmin($request);
        extract($data);
        return view('admin.wfh.index', compact('datawfh', 'unitperusahaan', 'pendingWfhAdmin', 'pendingLaporanAdmin'));
    }

    public function deletewfhadmin(int $id, WfhService $wfhService)
    {
        $result = $wfhService->deleteWfhAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveWfhAdmin(int $id, WfhService $wfhService)
    {
        $result = $wfhService->approveWfhAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectWfhAdmin(RejectRequest $request, int $id, WfhService $wfhService)
    {
        $result = $wfhService->rejectWfhAdmin($id, $request->rejected_reason);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveLaporanAdmin(int $id, WfhService $wfhService)
    {
        $result = $wfhService->approveLaporanAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanAdmin(RejectRequest $request, int $id, WfhService $wfhService)
    {
        $result = $wfhService->rejectLaporanAdmin($id, $request->rejected_reason);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ==================== EDIT DATA ====================

    public function editPresensiAdmin(int $id)
    {
        $presensi = Presensi::with('karyawan')->findOrFail($id);
        return response()->json($presensi);
    }

    public function updatePresensiAdmin(UpdatePresensiAdminRequest $request, int $id)
    {
        $presensi = Presensi::findOrFail($id);
        $presensi->update([
            'jam_in' => $request->jam_in,
            'jam_out' => $request->jam_out,
        ]);

        return redirect()->back()->with('success', 'Data presensi berhasil diperbarui');
    }

    public function editIzinAdmin(int $id)
    {
        $izin = Izin::with(['karyawan', 'atasan'])->findOrFail($id);
        $arr = $izin->toArray();
        $arr['tgl_izin'] = $izin->tgl_izin instanceof \Carbon\Carbon
            ? $izin->tgl_izin->format('Y-m-d')
            : $izin->tgl_izin;
        $arr['dikirim_tanggal'] = $izin->dikirim_tanggal instanceof \Carbon\Carbon
            ? $izin->dikirim_tanggal->format('Y-m-d H:i')
            : $izin->dikirim_tanggal;
        $arr['approved_at'] = $izin->approved_at instanceof \Carbon\Carbon
            ? $izin->approved_at->format('Y-m-d H:i')
            : $izin->approved_at;
        return response()->json($arr);
    }

    public function updateIzinAdmin(UpdateIzinAdminRequest $request, int $id, IzinService $izinService)
    {
        $result = $izinService->updateIzinAdmin($id, $request);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function editWfhAdmin(int $id)
    {
        $wfh = Wfh::with(['karyawan', 'atasan'])->findOrFail($id);
        $arr = $wfh->toArray();
        $arr['tgl_wfh'] = $wfh->tgl_wfh instanceof \Carbon\Carbon
            ? $wfh->tgl_wfh->format('Y-m-d')
            : $wfh->tgl_wfh;
        $arr['dikirim_tanggal'] = $wfh->dikirim_tanggal instanceof \Carbon\Carbon
            ? $wfh->dikirim_tanggal->format('Y-m-d H:i')
            : $wfh->dikirim_tanggal;
        $arr['approved_at'] = $wfh->approved_at instanceof \Carbon\Carbon
            ? $wfh->approved_at->format('Y-m-d H:i')
            : $wfh->approved_at;
        $arr['laporan_approved_at'] = $wfh->laporan_approved_at instanceof \Carbon\Carbon
            ? $wfh->laporan_approved_at->format('Y-m-d H:i')
            : $wfh->laporan_approved_at;
        return response()->json($arr);
    }

    public function updateWfhAdmin(UpdateWfhAdminRequest $request, int $id)
    {
        $wfh = Wfh::findOrFail($id);
        $oldStatus = $wfh->status instanceof WfhStatus ? $wfh->status->value : $wfh->status;
        $newStatus = $request->status;

        $updateData = [
            'tgl_wfh' => $request->tgl_wfh,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'keterangan' => $request->keterangan,
            'status' => $newStatus,
        ];

        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'approved' && empty($wfh->approved_at)) {
                $updateData['approved_at'] = now();
            }

            if ($oldStatus === 'unpaid' && $newStatus === 'approved') {
                $updateData['laporan_status'] = null;
                $updateData['laporan_deskripsi'] = null;
                $updateData['laporan_file'] = null;
                $updateData['laporan_submitted_at'] = null;
                $updateData['laporan_approved_at'] = null;
            }
        }

        $wfh->update($updateData);

        if ($oldStatus !== $newStatus) {
            $karyawan = \App\Models\Karyawan::where('nik', $wfh->nik)->first();
            if ($karyawan) {
                $karyawan->notify(new \App\Notifications\WfhStatusChanged($wfh, $oldStatus, $newStatus));
            }
        }

        return redirect()->back()->with('success', 'Data WFH berhasil diperbarui');
    }
}
