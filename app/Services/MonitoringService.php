<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use Illuminate\Http\Request;

class MonitoringService
{
    public static function getUnitPerusahaan()
    {
        return Unitperusahaan::orderBy('unit')->get();
    }

    public static function getPresensi(Request $request)
    {
        $query = Presensi::with([
                'karyawan.unitperusahaan',
                'lembur' => fn ($q) => $q->where('tgl_lembur', $request->tanggal),
            ])
            ->where('tgl_presensi', $request->tanggal);

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

        if ($request->filter_ketepatan === '0') {
            $query->where('terlambat', 0);
        } elseif ($request->filter_ketepatan === '1') {
            $query->where('terlambat', '>', 0);
        }

        return $query->get();
    }

    public static function getPresensiById(int $id)
    {
        return Presensi::with('karyawan')->find($id);
    }

    public static function getKaryawanByUnit(string $unit)
    {
        return Karyawan::where('unit', $unit)
            ->orderBy('nama_lengkap')
            ->get();
    }
}
