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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Shared\LocationService;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\Presensi\GetHistoriRequest;
use App\Http\Requests\Service\StoreIzinRequest;
use App\Http\Requests\Service\StoreLemburRequest;
use App\Http\Requests\Service\StoreWfhRequest;
use App\Http\Requests\Service\StoreLaporanWfhRequest;
use App\Http\Requests\Service\StoreFotoLemburRequest;
use App\Http\Requests\Service\StoreLaporanLemburRequest;
use App\Http\Requests\Service\UpdateIzinKaryawanRequest;

class KaryawanPresensiController extends Controller
{
    public function create()
    {
        $hariini = now('Asia/Jakarta')->format('Y-m-d');
        $nik = Auth::guard('karyawan')->user()->nik;
        $cek = Presensi::where('tgl_presensi', $hariini)->where('nik', $nik)->count();

        return view('karyawan.presensi.create', compact('cek'));
    }

    public function store(Request $request, PresensiService $presensiService)
    {
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

    public function izin(IzinService $izinService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $dataizin = $izinService->getIzinHistory($nik);

        return view('karyawan.izin.index', compact('dataizin'));
    }

    public function buatizin()
    {
        $karyawan = Auth::guard('karyawan')->user()->load('unitperusahaan');

        $unitkerja = Unitperusahaan::where('unit', $karyawan->unit)->first();
        $jamMasuk = $unitkerja?->jam_masuk instanceof \Carbon\Carbon
            ? $unitkerja->jam_masuk->format('H:i:s')
            : ($unitkerja?->jam_masuk ?? '08:00:00');
        $sekarang = now('Asia/Jakarta')->format('H:i:s');
        $batasSubmit = \Carbon\Carbon::parse($jamMasuk)->addHour()->format('H:i:s');
        $disableToday = ($sekarang >= $batasSubmit);

        return view('karyawan.izin.create', compact('karyawan', 'disableToday'));
    }

    public function showfileizin(string $file)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $path = IzinService::showFileIzin($file, $nik);
        if (!$path) abort(404);

        $abs = storage_path('app/public/' . $path);
        if (file_exists($abs)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimeMap = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
            ];
            $contentType = $mimeMap[$ext] ?? 'application/octet-stream';
            return response()->file($abs, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        }
        abort(404);
    }

    public function storeizin(StoreIzinRequest $request, IzinService $izinService)
    {
        $karyawan = Auth::guard('karyawan')->user();

        Log::info('storeizin controller: Request received', [
            'nik' => $karyawan->nik,
            'validated_data' => $request->validated(),
        ]);

        $result = $izinService->storeIzin($request, $karyawan);

        Log::info('storeizin controller: Result', [
            'nik' => $karyawan->nik,
            'success' => $result['success'],
            'message' => $result['message'],
        ]);

        if ($result['success']) {
            return redirect('/izin')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function deleteizin(int $id, IzinService $izinService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $izinService->deleteIzin($id, $nik);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveIzinAtasan(Request $request, int $id, IzinService $izinService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $izinService->approveIzinAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectIzinAtasan(RejectRequest $request, int $id, IzinService $izinService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $izinService->rejectIzinAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ==================== LEMBUR ====================

    public function lembur(LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $datalembur = $lemburService->getLemburHistory($nik);

        return view('karyawan.lembur.index', compact('datalembur'));
    }

    public function buatlembur(LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user()->load('unitperusahaan');
        $canSubmit = $lemburService->canSubmit($karyawan);

        if (!$canSubmit['can']) {
            return redirect('/lembur')->with('error', $canSubmit['message']);
        }

        return view('karyawan.lembur.create', compact('karyawan'));
    }

    public function storelembur(StoreLemburRequest $request, LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $lemburService->storePengajuan($request, $karyawan);

        if ($result['success']) {
            return redirect('/lembur')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function deletelembur(int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $lemburService->deleteLembur($id, $nik);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function showfilelembur(string $file)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $path = LemburService::showFileLembur($file, $nik);
        if (!$path) abort(404);

        $abs = storage_path('app/public/' . $path);
        if (file_exists($abs)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimeMap = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
            ];
            $contentType = $mimeMap[$ext] ?? 'application/octet-stream';
            return response()->file($abs, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        }
        abort(404);
    }

    public function fotoLembur(int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $data = $lemburService->getFotoData($id, $nik);

        if (!$data) {
            return redirect('/lembur')->with('error', 'Data tidak ditemukan atau lembur belum disetujui.');
        }

        return view('karyawan.lembur.foto', ['data' => $data]);
    }

    public function storeFotoLembur(StoreFotoLemburRequest $request, int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $lemburService->storeFoto($request, $id, $nik);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return redirect('/lembur/' . $id . '/foto')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function buatLaporanLembur(int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $data = $lemburService->getLaporanData($id, $nik);

        if (!$data || isset($data->error)) {
            $msg = $data->error ?? 'Data tidak ditemukan';
            return redirect()->back()->with('error', $msg);
        }

        return view('karyawan.lembur.laporan', ['data' => $data]);
    }

    public function storeLaporanLembur(StoreLaporanLemburRequest $request, int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $lemburService->storeLaporanLembur($request, $id, $nik);

        if ($result['success']) {
            return redirect('/lembur')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function editLaporanWfh(int $id, WfhService $wfhService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $wfh = $wfhService->getLaporanData($id, $nik, true);

        if (!$wfh || isset($wfh->error)) {
            $msg = $wfh->error ?? 'Data tidak ditemukan';
            return redirect()->back()->with('error', $msg);
        }

        $liveLocation = $wfh->live_location ?? '-';
        return view('karyawan.wfh.laporan', compact('wfh', 'liveLocation'))->with('isEdit', true);
    }

    public function updateLaporanWfh(StoreLaporanWfhRequest $request, int $id, WfhService $wfhService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $wfhService->storeLaporanWfh($request, $id, $nik, true);

        if ($result['success']) {
            return redirect('/wfh')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function editLaporanLembur(int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $data = $lemburService->getLaporanData($id, $nik, true);

        if (!$data || isset($data->error)) {
            $msg = $data->error ?? 'Data tidak ditemukan';
            return redirect()->back()->with('error', $msg);
        }

        return view('karyawan.lembur.laporan', ['data' => $data])->with('isEdit', true);
    }

    public function updateLaporanLembur(StoreLaporanLemburRequest $request, int $id, LemburService $lemburService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $lemburService->storeLaporanLembur($request, $id, $nik, true);

        if ($result['success']) {
            return redirect('/lembur')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function editIzin(int $id, IzinService $izinService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $izin = $izinService->getEditIzinData($id, $nik);

        if (!$izin) {
            return redirect()->back()->with('error', 'Data izin tidak ditemukan atau tidak dalam status ditolak');
        }

        return view('karyawan.izin.edit', compact('izin'));
    }

    public function updateIzin(\App\Http\Requests\Service\UpdateIzinKaryawanRequest $request, int $id, IzinService $izinService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $izinService->updateIzin($request, $id, $nik);

        if ($result['success']) {
            return redirect('/izin')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function approveLemburAtasan(Request $request, int $id, LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $lemburService->approveLemburAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLemburAtasan(RejectRequest $request, int $id, LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $lemburService->rejectLemburAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveLaporanLemburAtasan(Request $request, int $id, LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $lemburService->approveLaporanAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanLemburAtasan(RejectRequest $request, int $id, LemburService $lemburService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $lemburService->rejectLaporanAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ==================== WFH ====================

    public function wfh(WfhService $wfhService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $datawfh = $wfhService->getWfhHistory($nik);

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

    public function storewfh(StoreWfhRequest $request, WfhService $wfhService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $wfhService->storeWfh($request, $karyawan);

        if ($result['success']) {
            return redirect('/wfh')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function deletewfh(int $id, WfhService $wfhService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $wfhService->deleteWfh($id, $nik);

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function approveWfhAtasan(Request $request, int $id, WfhService $wfhService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $wfhService->approveWfhAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectWfhAtasan(RejectRequest $request, int $id, WfhService $wfhService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $wfhService->rejectWfhAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function buatLaporanWfh(int $id, WfhService $wfhService, LocationService $locationService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $wfh = $wfhService->getLaporanData($id, $nik);

        if (!$wfh || isset($wfh->error)) {
            $msg = $wfh->error ?? 'Data tidak ditemukan';
            return redirect()->back()->with('error', $msg);
        }

        $presensiToday = Presensi::where('nik', $nik)
            ->where('tgl_presensi', now('Asia/Jakarta')->format('Y-m-d'))
            ->first();
        $liveLocation = $locationService->reverseGeocode($presensiToday?->lokasi_in ?? '');

        return view('karyawan.wfh.laporan', compact('wfh', 'liveLocation'));
    }

    public function storeLaporanWfh(StoreLaporanWfhRequest $request, int $id, WfhService $wfhService)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $result = $wfhService->storeLaporanWfh($request, $id, $nik);

        if ($result['success']) {
            return redirect('/wfh')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message'])->withInput();
    }

    public function approveLaporanAtasan(Request $request, int $id, WfhService $wfhService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $wfhService->approveLaporanAtasan($id, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function rejectLaporanAtasan(RejectRequest $request, int $id, WfhService $wfhService)
    {
        $karyawan = Auth::guard('karyawan')->user();
        $result = $wfhService->rejectLaporanAtasan($id, $request->rejected_reason, $karyawan);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
