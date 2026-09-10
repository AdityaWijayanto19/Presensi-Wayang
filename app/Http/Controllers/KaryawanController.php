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

        $karyawan = $query->paginate(5)->withQueryString();
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

        $foto = 'nophoto.png';
        if ($request->hasFile('foto')) {
            $imageService = app(ImageService::class);
            $fotoPath = $imageService->processUpload($request->file('foto'), 'karyawan');
            if ($fotoPath) {
                $foto = basename($fotoPath);
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
            'password' => Hash::make($request->password),
        ]);

        if ($request->hasFile('foto')) {
            $request->file('foto')->move(public_path('storage/uploads/karyawan'), $foto);
        }

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

            if ($fotoLama && $fotoLama !== 'nophoto.png') {
                $imageService->deleteFile('uploads/karyawan/' . $fotoLama);
            }

            $fotoPath = $imageService->processUpload($request->file('foto'), 'karyawan');
            $foto = $fotoPath ? basename($fotoPath) : $fotoLama;
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

        $query = Karyawan::where('role_approved', $targetPosisi)
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

            if ($karyawan->foto !== 'nophoto.png') {
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

            // Hapus dokumen lembur
            foreach ($karyawan->lembur as $l) {
                if (!empty($l->file_form)) {
                    Storage::delete('public/uploads/lembur/' . $l->file_form);
                }
                if (!empty($l->file_laporan)) {
                    Storage::delete('public/uploads/lembur/' . $l->file_laporan);
                }
            }
            $karyawan->lembur()->delete();

            // Hapus dokumen WFH
            foreach ($karyawan->wfh as $w) {
                if (!empty($w->pdf_form_path)) {
                    Storage::disk('public')->delete($w->pdf_form_path);
                }
                if (!empty($w->laporan_file)) {
                    Storage::disk('public')->delete($w->laporan_file);
                }
            }
            $karyawan->wfh()->delete();

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
