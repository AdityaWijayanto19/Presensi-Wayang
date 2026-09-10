<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePresensiAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jam_in' => 'required|date_format:H:i:s',
            'jam_out' => 'nullable|date_format:H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'jam_in.required' => 'Jam masuk wajib diisi.',
            'jam_in.date_format' => 'Format jam masuk tidak valid (HH:MM:SS).',
            'jam_out.date_format' => 'Format jam pulang tidak valid (HH:MM:SS).',
        ];
    }
}
