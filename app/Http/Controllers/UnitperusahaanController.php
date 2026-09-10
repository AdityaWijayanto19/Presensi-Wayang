<?php

namespace App\Http\Controllers;

use App\Models\Unitperusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Http\Requests\Unitperusahaan\StoreUnitperusahaanRequest;
use App\Http\Requests\Unitperusahaan\UpdateUnitperusahaanRequest;

class UnitperusahaanController extends Controller
{
    public function index()
    {
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.unitperusahaan.index', compact('unitperusahaan'));
    }

    public function store(StoreUnitperusahaanRequest $request)
    {

        Unitperusahaan::create($request->only('unit', 'perusahaan', 'jam_masuk'));

        return Redirect::back()->with('success', 'Data Berhasil Disimpan!');
    }

    public function edit(Request $request)
    {
        $unitperusahaan = Unitperusahaan::findOrFail($request->id);

        return view('admin.unitperusahaan.edit', compact('unitperusahaan'));
    }

    public function update(string $id, UpdateUnitperusahaanRequest $request)
    {

        $unitperusahaan = Unitperusahaan::findOrFail($id);
        $oldUnit = $unitperusahaan->unit;
        $newUnit = $request->unit;

        DB::beginTransaction();

        try {
            $unitperusahaan->update($request->only('unit', 'perusahaan', 'jam_masuk'));

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

    public function delete(string $id)
    {
        $unitperusahaan = Unitperusahaan::findOrFail($id);
        $unitperusahaan->delete();

        return Redirect::back()->with('success', 'Data Berhasil Dihapus!');
    }
}
