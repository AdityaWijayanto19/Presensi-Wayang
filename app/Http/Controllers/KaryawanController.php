<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Models\Presensi;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\Wfh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Services\LemburService;
use App\Services\WfhService;
use App\Services\CutiService;
use App\Services\ImageService;
use App\Http\Requests\Karyawan\StoreKaryawanRequest;
use App\Http\Requests\Karyawan\UpdateKaryawanRequest;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::with(['unitperusahaan', 'atasan'])
            ->orderBy('nama_lengkap');

        if (!empty($request->nama_karyawan)) {
            $query->where('nama_lengkap', 'like', '%' . $request->nama_karyawan . '%');
        }

        if (!empty($request->unit)) {
            $query->where('unit', $request->unit);
        }

        if (!empty($request->jabatan_filter)) {
            $query->where('jabatan', $request->jabatan_filter);
        }

        $karyawan = $query->paginate(10)->withQueryString();
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.karyawan.index', compact('karyawan', 'unitperusahaan'));
    }

    public function store(StoreKaryawanRequest $request)
    {

        $roleApproved = $request->role_approved ?: null;
        $atasanNik = $request->atasan_nik ?: null;

        if ($roleApproved === 'Direktur') {
            $atasanNik = null;
        }
        if ($atasanNik === $request->nik) {
            $atasanNik = null;
        }

        $foto = null;
        if ($request->hasFile('foto')) {
            $imageService = app(ImageService::class);
            $fotoPath = $imageService->processUpload($request->file('foto'), 'karyawan', $request->nik);
            if ($fotoPath) {
                $foto = basename($fotoPath);
            } else {
                $foto = $request->nik . '.' . $request->file('foto')->getClientOriginalExtension();
                $request->file('foto')->move(public_path('storage/uploads/karyawan'), $foto);
            }
        }

        $unitId = Unitperusahaan::where('unit', $request->unit)->value('id');

        $karyawan = Karyawan::create([
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'unit' => $request->unit,
            'unit_id' => $unitId,
            'jabatan' => $request->jabatan,
            'posisi' => $request->posisi,
            'role_approved' => $roleApproved,
            'atasan_nik' => $atasanNik,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
            'jatah_cuti' => (int) $request->jatah_cuti,
            'password' => Hash::make($request->password),
        ]);

        return Redirect::back()->with('success', 'Data karyawan berhasil disimpan!');
    }

    public function update(string $nik, UpdateKaryawanRequest $request)
    {

        $karyawan = Karyawan::findOrFail($nik);
        $roleApproved = $request->role_approved ?: null;
        $atasanNik = $request->atasan_nik ?: null;

        if ($roleApproved === 'Direktur') {
            $atasanNik = null;
        }
        if ($atasanNik === $nik) {
            $atasanNik = null;
        }

        $fotoLama = $request->foto_lama;
        $foto = $fotoLama;

        if ($request->hasFile('foto')) {
            $imageService = app(ImageService::class);

            if ($fotoLama) {
                $imageService->deleteFile('uploads/karyawan/' . $fotoLama);
            }

            $fotoPath = $imageService->processUpload($request->file('foto'), 'karyawan', $nik);
            if ($fotoPath) {
                $foto = basename($fotoPath);
            } else {
                $foto = $nik . '.' . $request->file('foto')->getClientOriginalExtension();
                $request->file('foto')->move(public_path('storage/uploads/karyawan'), $foto);
            }
        }

        $updateData = [
            'nama_lengkap' => $request->nama_lengkap,
            'unit' => $request->unit,
            'unit_id' => Unitperusahaan::where('unit', $request->unit)->value('id'),
            'jabatan' => $request->jabatan,
            'posisi' => $request->posisi,
            'role_approved' => $roleApproved,
            'atasan_nik' => $atasanNik,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
            'jatah_cuti' => (int) $request->jatah_cuti,
        ];

        if (!empty($request->password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        $karyawan->update($updateData);

        return Redirect::to('/panel/karyawan?page=' . $request->page)
            ->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function getAtasan(Request $request)
    {
        $roleApproved = $request->role_approved;
        if (!$roleApproved) {
            return response()->json([]);
        }

        $atasanMap = [
            'Staff' => 'Manager',
            'Manager' => 'GM',
            'GM' => 'Direktur',
            'Direktur' => null,
        ];

        $targetPosisi = $atasanMap[$roleApproved] ?? null;
        if (!$targetPosisi) {
            return response()->json([]);
        }

        $query = Karyawan::where('jabatan', $targetPosisi)
            ->select('nik', 'nama_lengkap', 'jabatan', 'posisi')
            ->orderBy('nama_lengkap');

        if ($request->exclude_nik) {
            $query->where('nik', '!=', $request->exclude_nik);
        }

        return response()->json($query->get());
    }

    public function delete(string $nik)
    {
        $karyawan = Karyawan::findOrFail($nik);

        DB::beginTransaction();

        try {
            $imageService = app(ImageService::class);

            if ($karyawan->foto) {
                $imageService->deleteFile('uploads/karyawan/' . $karyawan->foto);
            }

            // Hapus foto presensi
            foreach ($karyawan->presensi as $p) {
                if (!empty($p->foto_in)) {
                    Storage::delete('public/uploads/absensi/' . $p->foto_in);
                }
                if (!empty($p->foto_out)) {
                    Storage::delete('public/uploads/absensi/' . $p->foto_out);
                }
            }
            $karyawan->presensi()->delete();

            // Hapus dokumen izin
            foreach ($karyawan->izin as $i) {
                if (!empty($i->file)) {
                    Storage::delete('public/uploads/izin/' . $i->file);
                }
            }
            $karyawan->izin()->delete();

            // Hapus dokumen lembur (termasuk pdf, foto, laporan)
            foreach ($karyawan->lembur as $l) {
                LemburService::deleteLemburFiles($l);
            }
            $karyawan->lembur()->delete();

            // Hapus dokumen WFH (termasuk pdf, laporan)
            foreach ($karyawan->wfh as $w) {
                WfhService::deleteWfhFiles($w);
            }
            $karyawan->wfh()->delete();

            // Hapus dokumen cuti
            foreach ($karyawan->cuti as $c) {
                CutiService::deleteCutiFile($c);
            }
            $karyawan->cuti()->delete();

            // Null-kan atasan yang dipegang karyawan ini
            Karyawan::where('atasan_nik', $nik)->update(['atasan_nik' => null]);

            // Null-kan atasan di WFH yang menunggu approval
            Wfh::where('atasan_nik', $nik)->update([
                'atasan_nik' => null,
                'status' => 'pending_admin',
                'atasan_status' => 'pending',
            ]);

            // Update laporan yang menunggu approval atasan ini
            Wfh::where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->update([
                    'laporan_atasan_nik' => null,
                    'laporan_status' => 'pending_admin',
                    'laporan_atasan_status' => 'pending',
                ]);

            // Null-kan atasan di Lembur yang menunggu approval
            \App\Models\Lembur::where('atasan_nik', $nik)->update([
                'atasan_nik' => null,
                'status' => 'pending_admin',
                'atasan_status' => 'pending',
            ]);

            // Update laporan lembur yang menunggu approval atasan ini
            \App\Models\Lembur::where('laporan_atasan_nik', $nik)
                ->where('laporan_status', 'pending_atasan')
                ->update([
                    'laporan_atasan_nik' => null,
                    'laporan_status' => 'pending_admin',
                    'laporan_atasan_status' => 'pending',
                ]);

            // Hapus data karyawan
            $karyawan->delete();

            DB::commit();

            return Redirect::back()->with('success', 'Data karyawan dan riwayatnya berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', 'Data karyawan gagal dihapus!');
        }
    }
}
