<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIzinAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_izin' => 'required|date',
            'jenis_izin' => 'required|in:tidak_masuk,terlambat,pulang_cepat,sakit',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_izin.required' => 'Tanggal izin wajib diisi.',
            'tgl_izin.date' => 'Format tanggal tidak valid.',
            'jenis_izin.required' => 'Kategori izin wajib dipilih.',
            'jenis_izin.in' => 'Kategori izin tidak valid.',
        ];
    }
}
