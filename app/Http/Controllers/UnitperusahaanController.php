<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unitperusahaan\StoreUnitperusahaanRequest;
use App\Http\Requests\Unitperusahaan\UpdateUnitperusahaanRequest;
use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use App\Services\ImageService;
use App\Services\LemburService;
use App\Services\WfhService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class UnitperusahaanController extends Controller
{
    public function index()
    {
        $unitperusahaan = Unitperusahaan::with('lokasis')->orderBy('unit')->get();

        return view('admin.unitperusahaan.index', compact('unitperusahaan'));
    }

    public function store(StoreUnitperusahaanRequest $request)
    {
        DB::beginTransaction();

        try {
            $unit = Unitperusahaan::create($request->only('unit', 'perusahaan', 'jam_masuk', 'radius_meter'));
            $this->syncLokasi($unit, $request->input('lokasis'));

            DB::commit();

            return Redirect::back()->with('success', 'Data Berhasil Disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();

            return Redirect::back()->with('error', 'Data Gagal Disimpan!');
        }
    }

    public function update(string $id, UpdateUnitperusahaanRequest $request)
    {

        $unitperusahaan = Unitperusahaan::findOrFail($id);
        $oldUnit = $unitperusahaan->unit;
        $newUnit = $request->unit;

        DB::beginTransaction();

        try {
            $unitperusahaan->update($request->only('unit', 'perusahaan', 'jam_masuk', 'radius_meter'));
            $this->syncLokasi($unitperusahaan, $request->input('lokasis'));

            if ($oldUnit !== $newUnit) {
                DB::table('karyawans')->where('unit', $oldUnit)->update(['unit' => $newUnit]);
                DB::table('users')->where('unit', $oldUnit)->update(['unit' => $newUnit]);
            }

            DB::commit();

            return Redirect::back()->with('success', 'Data Berhasil Diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();

            return Redirect::back()->with('error', 'Data Gagal Diperbarui!');
        }
    }

    /**
     * Sinkronisasi titik lokasi kantor (replace all) untuk sebuah unit.
     *
     * @param  array<int, array{nama: string, lat: string|float, lng: string|float}>|null  $lokasis
     */
    private function syncLokasi(Unitperusahaan $unit, ?array $lokasis): void
    {
        $unit->lokasis()->delete();

        foreach ($lokasis ?? [] as $lokasi) {
            $unit->lokasis()->create([
                'nama_lokasi' => $lokasi['nama'],
                'lat' => $lokasi['lat'],
                'lng' => $lokasi['lng'],
            ]);
        }
    }

    public function delete(string $id)
    {
        $unitperusahaan = Unitperusahaan::findOrFail($id);

        DB::beginTransaction();

        try {
            $imageService = app(ImageService::class);
            $karyawans = Karyawan::where('unit', $unitperusahaan->unit)->get();

            foreach ($karyawans as $karyawan) {
                // Hapus foto karyawan
                if ($karyawan->foto) {
                    $imageService->deleteFile('uploads/karyawan/'.$karyawan->foto);
                }

                // Hapus foto presensi
                foreach ($karyawan->presensi as $p) {
                    if (! empty($p->foto_in)) {
                        Storage::delete('public/uploads/absensi/'.$p->foto_in);
                    }
                    if (! empty($p->foto_out)) {
                        Storage::delete('public/uploads/absensi/'.$p->foto_out);
                    }
                }

                // Hapus dokumen izin
                foreach ($karyawan->izin as $i) {
                    if (! empty($i->file)) {
                        Storage::delete('public/uploads/izin/'.$i->file);
                    }
                }

                // Hapus dokumen lembur
                foreach ($karyawan->lembur as $l) {
                    LemburService::deleteLemburFiles($l);
                }

                // Hapus dokumen WFH
                foreach ($karyawan->wfh as $w) {
                    WfhService::deleteWfhFiles($w);
                }
            }

            // Hapus unit (cascade akan hapus karyawan dan relasi terkait di DB)
            $unitperusahaan->delete();

            DB::commit();

            return Redirect::back()->with('success', 'Data Berhasil Dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return Redirect::back()->with('error', 'Data Gagal Dihapus!');
        }
    }
}
