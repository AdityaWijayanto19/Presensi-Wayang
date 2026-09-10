<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLemburAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_lembur' => 'required|date',
            'durasi' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tgl_lembur.date' => 'Format tanggal tidak valid.',
            'durasi.required' => 'Durasi wajib diisi.',
            'durasi.integer' => 'Durasi harus berupa angka.',
            'durasi.min' => 'Durasi minimal 1 jam.',
            'durasi.max' => 'Durasi maksimal 5 jam.',
        ];
    }
}
