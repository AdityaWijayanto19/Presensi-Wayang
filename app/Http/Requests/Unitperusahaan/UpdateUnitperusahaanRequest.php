<?php

namespace App\Http\Requests\Unitperusahaan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitperusahaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'unit' => 'required|string|max:255|unique:unitperusahaans,unit,'.$id.',id',
            'perusahaan' => 'required|string|max:255',
            'jam_masuk' => 'required',
            'radius_meter' => 'required|integer|min:10|max:10000',
            'lokasis' => 'nullable|array',
            'lokasis.*.nama' => 'required|string|max:100',
            'lokasis.*.lat' => 'required|numeric|between:-90,90',
            'lokasis.*.lng' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'unit.required' => 'Unit wajib diisi.',
            'unit.string' => 'Unit harus berupa teks.',
            'unit.max' => 'Unit maksimal 255 karakter.',
            'unit.unique' => 'Unit sudah terdaftar.',
            'perusahaan.required' => 'Perusahaan wajib diisi.',
            'perusahaan.string' => 'Perusahaan harus berupa teks.',
            'perusahaan.max' => 'Perusahaan maksimal 255 karakter.',
            'jam_masuk.required' => 'Jam masuk wajib diisi.',
            'radius_meter.required' => 'Radius presensi wajib diisi.',
            'radius_meter.integer' => 'Radius presensi harus berupa angka.',
            'radius_meter.min' => 'Radius presensi minimal 10 meter.',
            'radius_meter.max' => 'Radius presensi maksimal 10000 meter.',
            'lokasis.array' => 'Format lokasi kantor tidak valid.',
            'lokasis.*.nama.required' => 'Nama lokasi wajib diisi.',
            'lokasis.*.nama.string' => 'Nama lokasi harus berupa teks.',
            'lokasis.*.nama.max' => 'Nama lokasi maksimal 100 karakter.',
            'lokasis.*.lat.required' => 'Latitude wajib diisi.',
            'lokasis.*.lat.numeric' => 'Latitude harus berupa angka.',
            'lokasis.*.lat.between' => 'Latitude harus antara -90 sampai 90.',
            'lokasis.*.lng.required' => 'Longitude wajib diisi.',
            'lokasis.*.lng.numeric' => 'Longitude harus berupa angka.',
            'lokasis.*.lng.between' => 'Longitude harus antara -180 sampai 180.',
        ];
    }
}
