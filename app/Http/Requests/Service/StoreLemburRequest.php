<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreLemburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keterangan' => 'required|string|min:5|max:1000',
            'tgl_lembur' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'keterangan.required' => 'Keterangan lembur wajib diisi.',
            'keterangan.min' => 'Keterangan lembur minimal 5 karakter.',
            'keterangan.max' => 'Keterangan lembur maksimal 1000 karakter.',
            'tgl_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tgl_lembur.date' => 'Format tanggal tidak valid.',
        ];
    }
}
