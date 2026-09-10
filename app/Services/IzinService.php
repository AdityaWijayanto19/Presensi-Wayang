<?php

namespace App\Services;

use App\Models\Izin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IzinService
{
    public function getIzinByKaryawan(string $nik)
    {
        return Izin::where('nik', $nik)
            ->orderBy('tgl_izin', 'desc')
            ->get();
    }

    public function storeIzin(Request $request): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        $cek = Izin::where('nik', $nik)
            ->where('tgl_izin', $request->tgl_izin)
            ->count();

        if ($cek > 0) {
            return ['success' => false, 'message' => 'Anda sudah mengirim data izin pada tanggal tersebut!'];
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $namaFile = date('YmdHis') . '-' . $file->getClientOriginalName();
            $file->storeAs('public/uploads/izin', $namaFile);

            Izin::create([
                'nik' => $nik,
                'tgl_izin' => $request->tgl_izin,
                'jenis_izin' => $request->jenis_izin,
                'file' => $namaFile,
                'dikirim_tanggal' => now(),
            ]);

            return ['success' => true, 'message' => 'Data izin berhasil dikirim!'];
        }

        return ['success' => false, 'message' => 'Data gagal dikirim!'];
    }

    public function deleteIzin(int $id): array
    {
        $nik = Auth::guard('karyawan')->user()->nik;

        $izin = Izin::where('id', $id)->where('nik', $nik)->first();
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        Storage::delete('public/uploads/izin/' . $izin->file);
        $izin->delete();

        return ['success' => true, 'message' => 'Data izin berhasil dihapus!'];
    }

    public function deleteIzinAdmin(int $id): array
    {
        $izin = Izin::find($id);
        if (!$izin) {
            return ['success' => false, 'message' => 'Data tidak ditemukan!'];
        }

        Storage::delete('public/uploads/izin/' . $izin->file);
        $izin->delete();

        return ['success' => true, 'message' => 'Data izin berhasil dihapus!'];
    }

    public function getDataIzinAdmin(Request $request)
    {
        $query = Izin::with(['karyawan.unitperusahaan']);

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

        if (!empty($request->jenis_izin)) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        if (!empty($request->tanggal)) {
            $query->where('tgl_izin', $request->tanggal);
        }

        return $query->orderBy('tgl_izin', 'desc')->paginate(5);
    }
}
