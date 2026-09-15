<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreWfhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_wfh' => 'required|date|after_or_equal:today',
            'keterangan' => 'required|string|min:5|max:1000',
            'deskripsi_pekerjaan' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_wfh.required' => 'Tanggal WFH wajib diisi.',
            'tgl_wfh.date' => 'Format tanggal tidak valid.',
            'tgl_wfh.after_or_equal' => 'Tanggal WFH harus hari ini atau tanggal yang akan datang.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.min' => 'Keterangan minimal 5 karakter.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'deskripsi_pekerjaan.required' => 'Deskripsi pekerjaan wajib diisi.',
            'deskripsi_pekerjaan.string' => 'Deskripsi pekerjaan harus berupa teks.',
            'deskripsi_pekerjaan.min' => 'Deskripsi pekerjaan minimal 10 karakter.',
            'deskripsi_pekerjaan.max' => 'Deskripsi pekerjaan maksimal 2000 karakter.',
        ];
    }
}
