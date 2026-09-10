<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Wfh;
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

    public function dataizin(Request $request)
    {
        $izinService = new IzinService();
        $dataizin = $izinService->getDataIzinAdmin($request);
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.izin.index', compact('dataizin', 'unitperusahaan'));
    }

    public function deleteizinadmin(int $id)
    {
        $izinService = new IzinService();
        $result = $izinService->deleteIzinAdmin($id);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function datalembur(Request $request)
    {
        $lemburService = new LemburService();
        $datalembur = $lemburService->getDataLemburAdmin($request);
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.lembur.index', compact('datalembur', 'unitperusahaan'));
    }

    public function deletelemburadmin(int $id)
    {
        $lemburService = new LemburService();
        $result = $lemburService->deleteLemburAdmin($id);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function datawfh(Request $request)
    {
        $data = WfhService::getDataWfhAdmin($request);
        extract($data);
        return view('admin.wfh.index', compact('datawfh', 'unitperusahaan', 'pendingWfhAdmin', 'pendingLaporanAdmin'));
    }

    public function deletewfhadmin(int $id)
    {
        $result = WfhService::deleteWfhAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveWfhAdmin(int $id)
    {
        $result = WfhService::approveWfhAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectWfhAdmin(RejectRequest $request, int $id)
    {
        $result = WfhService::rejectWfhAdmin($id, $request->rejected_reason);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveLaporanAdmin(int $id)
    {
        $result = WfhService::approveLaporanAdmin($id);
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanAdmin(RejectRequest $request, int $id)
    {
        $result = WfhService::rejectLaporanAdmin($id, $request->rejected_reason);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

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
        $izin = Izin::with('karyawan')->findOrFail($id);
        return response()->json($izin);
    }

    public function updateIzinAdmin(UpdateIzinAdminRequest $request, int $id)
    {
        $izin = Izin::findOrFail($id);
        $izin->update([
            'tgl_izin' => $request->tgl_izin,
            'jenis_izin' => $request->jenis_izin,
        ]);

        return redirect()->back()->with('success', 'Data izin berhasil diperbarui');
    }

    public function editLemburAdmin(int $id)
    {
        $lembur = Lembur::with('karyawan')->findOrFail($id);
        return response()->json($lembur);
    }

    public function updateLemburAdmin(UpdateLemburAdminRequest $request, int $id)
    {
        $lembur = Lembur::findOrFail($id);
        $lembur->update([
            'tgl_lembur' => $request->tgl_lembur,
            'durasi' => $request->durasi,
        ]);

        return redirect()->back()->with('success', 'Data lembur berhasil diperbarui');
    }

    public function editWfhAdmin(int $id)
    {
        $wfh = Wfh::with(['karyawan', 'atasan'])->findOrFail($id);
        return response()->json($wfh);
    }

    public function updateWfhAdmin(UpdateWfhAdminRequest $request, int $id)
    {
        $wfh = Wfh::findOrFail($id);
        $wfh->update([
            'tgl_wfh' => $request->tgl_wfh,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data WFH berhasil diperbarui');
    }
}
