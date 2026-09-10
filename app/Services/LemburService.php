<?php

namespace App\Services;

use App\Models\Lembur;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LemburService
{
    public function getLemburByKaryawan(string $nik)
    {
        return Lembur::where('nik', $nik)
            ->orderBy('tgl_lembur', 'desc')
            ->get();
    }

    public function storeLembur(Request $request): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        if ($request->tgl_lembur > date('Y-m-d')) {
            return ['success' => false, 'message' => 'Tanggal lembur tidak boleh melebihi hari ini!'];
        }

        $cek = Lembur::where('nik', $nik)
            ->where('tgl_lembur', $request->tgl_lembur)
            ->count();

        if ($cek > 0) {
            return ['success' => false, 'message' => 'Anda sudah mengirim data lembur pada tanggal tersebut!'];
        }

        if ($request->hasFile('file_form') && $request->hasFile('file_laporan')) {
            $form = $request->file('file_form');
            $laporan = $request->file('file_laporan');
            $timestamp = date('YmdHis');

            $imageService = app(ImageService::class);

            if (str_starts_with($form->getMimeType(), 'image/')) {
                $formPath = $imageService->processUpload($form, 'lembur');
                $namaForm = $formPath ? basename($formPath) : $timestamp . '-form-' . $form->getClientOriginalName();
            } else {
                $namaForm = $timestamp . '-form-' . $form->getClientOriginalName();
                $form->storeAs('public/uploads/lembur', $namaForm);
            }

            if (str_starts_with($laporan->getMimeType(), 'image/')) {
                $laporanPath = $imageService->processUpload($laporan, 'lembur');
                $namaLaporan = $laporanPath ? basename($laporanPath) : $timestamp . '-laporan-' . $laporan->getClientOriginalName();
            } else {
                $namaLaporan = $timestamp . '-laporan-' . $laporan->getClientOriginalName();
                $laporan->storeAs('public/uploads/lembur', $namaLaporan);
            }

            Lembur::create([
                'nik' => $nik,
                'tgl_lembur' => $request->tgl_lembur,
                'durasi' => $request->durasi,
                'file_form' => $namaForm,
                'file_laporan' => $namaLaporan,
                'dikirim_tanggal' => now(),
            ]);

            return ['success' => true, 'message' => 'Data lembur berhasil dikirim!'];
        }

        return ['success' => false, 'message' => 'Data gagal dikirim!'];
    }

    public function deleteLembur(int $id): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        $lembur = Lembur::where('id', $id)->where('nik', $nik)->first();
        if (!$lembur) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        Storage::delete('public/uploads/lembur/' . $lembur->file_form);
        Storage::delete('public/uploads/lembur/' . $lembur->file_laporan);
        $lembur->delete();

        return ['success' => true, 'message' => 'Data lembur berhasil dihapus!'];
    }

    public function deleteLemburAdmin(int $id): array
    {
        $lembur = Lembur::find($id);
        if (!$lembur) {
            return ['success' => true, 'message' => 'Data lembur berhasil dihapus!'];
        }

        Storage::delete('public/uploads/lembur/' . $lembur->file_form);
        Storage::delete('public/uploads/lembur/' . $lembur->file_laporan);
        $lembur->delete();

        return ['success' => true, 'message' => 'Data lembur berhasil dihapus!'];
    }

    public function getDataLemburAdmin(Request $request)
    {
        $query = Lembur::with(['karyawan.unitperusahaan']);

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
            $query->where('tgl_lembur', $request->tanggal);
        }

        return $query->orderBy('tgl_lembur', 'desc')->paginate(5);
    }
}
