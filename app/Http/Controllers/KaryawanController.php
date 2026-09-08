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

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|unique:karyawan,nik',
            'nama_lengkap' => 'required',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,Manager,GM,Direktur',
            'atasan_nik' => 'nullable|exists:karyawan,nik',
            'unit' => 'required|exists:unitperusahaan,unit',
            'no_hp' => 'required',
        ]);

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
            $foto = $request->nik . '.' . $request->file('foto')->getClientOriginalExtension();
        }

        $karyawan = Karyawan::create([
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'unit' => $request->unit,
            'jabatan' => $request->jabatan,
            'posisi' => $request->posisi,
            'role_approved' => $roleApproved,
            'atasan_nik' => $atasanNik,
            'no_hp' => $request->no_hp,
            'foto' => $foto,
            'password' => Hash::make('12345'),
        ]);

        if ($request->hasFile('foto')) {
            $request->file('foto')->move(public_path('storage/uploads/karyawan'), $foto);
        }

        return Redirect::back()->with('success', 'Data karyawan berhasil disimpan!');
    }

    public function edit(Request $request)
    {
        $karyawan = Karyawan::findOrFail($request->nik);
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();
        $page = $request->page;

        return view('admin.karyawan.edit', compact('unitperusahaan', 'karyawan', 'page'));
    }

    public function update(string $nik, Request $request)
    {
        $request->validate([
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|min:5',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,Manager,GM,Direktur',
            'atasan_nik' => 'nullable|exists:karyawan,nik',
        ]);

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
            $foto = $nik . '.' . $request->file('foto')->getClientOriginalExtension();
        }

        $updateData = [
            'nama_lengkap' => $request->nama_lengkap,
            'unit' => $request->unit,
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

        if ($request->hasFile('foto') && $fotoLama !== 'nophoto.png') {
            $path = public_path('storage/uploads/karyawan/' . $fotoLama);
            if (file_exists($path)) {
                unlink($path);
            }
            $request->file('foto')->move(public_path('storage/uploads/karyawan'), $foto);
        }

        return Redirect::to('/karyawan?page=' . $request->page)
            ->with('success', 'Data karyawan berhasil diperbarui!');
    }

    public function resetpassword(string $nik)
    {
        $karyawan = Karyawan::findOrFail($nik);
        $karyawan->update(['password' => Hash::make('12345')]);

        return Redirect::back()->with('success', 'Password berhasil direset menjadi 12345');
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
            // Hapus foto profil
            if ($karyawan->foto !== 'nophoto.png') {
                $path = public_path('storage/uploads/karyawan/' . $karyawan->foto);
                if (file_exists($path)) {
                    unlink($path);
                }
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
