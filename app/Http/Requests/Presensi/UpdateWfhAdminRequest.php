<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWfhAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_wfh' => 'required|date',
            'deskripsi_pekerjaan' => 'required|string|min:5',
            'keterangan' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_wfh.required' => 'Tanggal WFH wajib diisi.',
            'tgl_wfh.date' => 'Format tanggal tidak valid.',
            'deskripsi_pekerjaan.required' => 'Deskripsi pekerjaan wajib diisi.',
            'deskripsi_pekerjaan.string' => 'Deskripsi pekerjaan harus berupa teks.',
            'deskripsi_pekerjaan.min' => 'Deskripsi pekerjaan minimal 5 karakter.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
        ];
    }
}
