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
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending_atasan,pending_admin,approved,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tgl_lembur.date' => 'Format tanggal tidak valid.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
