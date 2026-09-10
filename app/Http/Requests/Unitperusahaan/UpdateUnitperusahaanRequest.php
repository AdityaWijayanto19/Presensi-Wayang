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
            'unit' => 'required|string|max:255|unique:unitperusahaans,unit,' . $id . ',id',
            'perusahaan' => 'required|string|max:255',
            'jam_masuk' => 'required',
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
        ];
    }
}
