<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class GetHistoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2030',
        ];
    }

    public function messages(): array
    {
        return [
            'bulan.required' => 'Bulan wajib dipilih.',
            'bulan.integer' => 'Bulan harus berupa angka.',
            'bulan.min' => 'Bulan minimal 1.',
            'bulan.max' => 'Bulan maksimal 12.',
            'tahun.required' => 'Tahun wajib dipilih.',
            'tahun.integer' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2020.',
            'tahun.max' => 'Tahun maksimal 2030.',
        ];
    }
}
