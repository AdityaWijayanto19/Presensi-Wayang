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
            'deskripsi_pekerjaan' => 'required|string|min:5|max:2000',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'required|string|in:pending_atasan,pending_admin,approved,rejected,unpaid',
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
            'deskripsi_pekerjaan.max' => 'Deskripsi pekerjaan maksimal 2000 karakter.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
