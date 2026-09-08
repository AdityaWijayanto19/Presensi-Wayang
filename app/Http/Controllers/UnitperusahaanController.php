<?php

namespace App\Http\Controllers;

use App\Models\Unitperusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UnitperusahaanController extends Controller
{
    public function index()
    {
        $unitperusahaan = Unitperusahaan::orderBy('unit')->get();

        return view('admin.unitperusahaan.index', compact('unitperusahaan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit' => 'required|string|max:255|unique:unitperusahaan,unit',
            'perusahaan' => 'required|string|max:255',
            'jam_masuk' => 'required',
        ]);

        Unitperusahaan::create($request->only('unit', 'perusahaan', 'jam_masuk'));

        return Redirect::back()->with('success', 'Data Berhasil Disimpan!');
    }

    public function edit(Request $request)
    {
        $unitperusahaan = Unitperusahaan::findOrFail($request->unit);

        return view('admin.unitperusahaan.edit', compact('unitperusahaan'));
    }

    public function update(string $unit, Request $request)
    {
        $request->validate([
            'unit' => 'required|string|max:255',
            'perusahaan' => 'required|string|max:255',
            'jam_masuk' => 'required',
        ]);

        $unitperusahaan = Unitperusahaan::findOrFail($unit);
        $unitperusahaan->update($request->only('unit', 'perusahaan', 'jam_masuk'));

        return Redirect::back()->with('success', 'Data Berhasil Diperbarui!');
    }

    public function delete(string $unit)
    {
        $unitperusahaan = Unitperusahaan::findOrFail($unit);
        $unitperusahaan->delete();

        return Redirect::back()->with('success', 'Data Berhasil Dihapus!');
    }
}
