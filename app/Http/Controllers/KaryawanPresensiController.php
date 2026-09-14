<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Unitperusahaan;
use App\Models\Wfh;
use App\Services\WfhService;
use App\Services\PresensiService;
use App\Services\IzinService;
use App\Services\LemburService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\Presensi\GetHistoriRequest;
use App\Http\Requests\Service\StoreIzinRequest;
use App\Http\Requests\Service\StoreLemburRequest;
use App\Http\Requests\Service\StoreWfhRequest;
use App\Http\Requests\Service\StoreLaporanWfhRequest;

class KaryawanPresensiController extends Controller
{
    public function create()
    {
        $hariini = now('Asia/Jakarta')->format('Y-m-d');
        $nik = Auth::guard('karyawan')->user()->nik;
        $cek = Presensi::where('tgl_presensi', $hariini)->where('nik', $nik)->count();

        return view('karyawan.presensi.create', compact('cek'));
    }

    public function store(Request $request)
    {
        $presensiService = new PresensiService();
        $result = $presensiService->processPresensi($request);

        echo $result['success'] ? "success|{$result['message']}|{$result['type']}" : "error|{$result['message']}|{$result['type']}";
    }

    public function histori()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        return view('karyawan.presensi.index', compact('namabulan'));
    }

    public function gethistori(GetHistoriRequest $request)
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        $histori = Presensi::whereRaw('MONTH(tgl_presensi) = ?', [$request->bulan])
            ->whereRaw('YEAR(tgl_presensi) = ?', [$request->tahun])
            ->where('nik', $nik)
            ->orderBy('tgl_presensi', 'desc')
            ->get();

        return view('karyawan.presensi._rows', compact('histori'));
    }

    public function izin()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $izinService = new IzinService();
        $dataizin = $izinService->getIzinByKaryawan($nik);

        return view('karyawan.izin.index', compact('dataizin'));
    }

    public function buatizin()
    {
        return view('karyawan.izin.create');
    }

    public function showfile(string $file)
    {
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            abort(404);
        }

        $path = storage_path('app/public/uploads/izin/' . $file);
        if (!file_exists($path)) abort(404);

        return response()->file($path);
    }

    public function storeizin(StoreIzinRequest $request)
    {
        $izinService = new IzinService();
        $result = $izinService->storeIzin($request);

        if ($result['success']) {
            return redirect('/izin')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function deleteizin(int $id)
    {
        $izinService = new IzinService();
        $result = $izinService->deleteIzin($id);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function lembur()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $lemburService = new LemburService();
        $datalembur = $lemburService->getLemburByKaryawan($nik);

        return view('karyawan.lembur.index', compact('datalembur'));
    }

    public function buatlembur()
    {
        return view('karyawan.lembur.create');
    }

    public function showfilelembur(string $file)
    {
        $file = basename($file);
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $file)) {
            abort(404);
        }

        $path = storage_path('app/public/uploads/lembur/' . $file);
        if (!file_exists($path)) abort(404);

        return response()->file($path);
    }

    public function storelembur(StoreLemburRequest $request)
    {
        $lemburService = new LemburService();
        $result = $lemburService->storeLembur($request);

        if ($result['success']) {
            return redirect('/lembur')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function deletelembur(int $id)
    {
        $lemburService = new LemburService();
        $result = $lemburService->deleteLembur($id);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function wfh()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $datawfh = WfhService::getWfhHistory($nik);

        return view('karyawan.wfh.index', compact('datawfh'));
    }

    public function buatwfh()
    {
        $karyawan = Auth::guard('karyawan')->user()->load('unitperusahaan');
        $presensiToday = Presensi::where('nik', $karyawan->nik)
            ->where('tgl_presensi', now('Asia/Jakarta')->format('Y-m-d'))
            ->first();

        $unitkerja = Unitperusahaan::where('unit', $karyawan->unit)->first();
        $jamMasuk = $unitkerja?->jam_masuk instanceof \Carbon\Carbon
            ? $unitkerja->jam_masuk->format('H:i:s')
            : ($unitkerja?->jam_masuk ?? '08:00:00');
        $sekarang = now('Asia/Jakarta')->format('H:i:s');
        $disableToday = ($sekarang >= $jamMasuk);

        return view('karyawan.wfh.create', compact('karyawan', 'presensiToday', 'disableToday'));
    }

    public function showfilewfh(string $file)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $path = WfhService::showFileWfh($file, $nik);
        if (!$path) abort(404);

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }
        $abs = storage_path('app/public/' . $path);
        if (file_exists($abs)) return response()->file($abs);
        abort(404);
    }

    public function storewfh(StoreWfhRequest $request)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = WfhService::storeWfh($request, $karyawan);

        if ($result['success']) {
            return redirect('/wfh')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function deletewfh(int $id)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = WfhService::deleteWfh($id, $nik);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveWfhAtasan(Request $request, int $id)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = WfhService::approveWfhAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectWfhAtasan(RejectRequest $request, int $id)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = WfhService::rejectWfhAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function buatLaporanWfh(int $id)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $wfh = WfhService::getLaporanData($id, $nik);

        if (!$wfh || isset($wfh->error)) {
            $msg = $wfh->error ?? 'Data tidak ditemukan';
            return redirect()->back()->with('error', $msg);
        }

        $presensiToday = Presensi::where('nik', $nik)
            ->where('tgl_presensi', now('Asia/Jakarta')->format('Y-m-d'))
            ->first();
        $presensiService = new PresensiService();
        $liveLocation = $presensiService->reverseGeocode($presensiToday?->lokasi_in ?? '');

        return view('karyawan.wfh.laporan', compact('wfh', 'liveLocation'));
    }

    public function storeLaporanWfh(StoreLaporanWfhRequest $request, int $id)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = WfhService::storeLaporanWfh($request, $id, $nik);

        if ($result['success']) {
            return redirect('/wfh')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function approveLaporanAtasan(Request $request, int $id)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = WfhService::approveLaporanAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanAtasan(RejectRequest $request, int $id)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = WfhService::rejectLaporanAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
