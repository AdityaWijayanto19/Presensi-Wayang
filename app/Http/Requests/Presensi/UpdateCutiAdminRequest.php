<?php

namespace App\Http\Requests\Presensi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCutiAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'durasi_hari' => 'required|integer|in:1,2,3',
            'tanggal_cuti' => 'required|array|size:durasi_hari',
            'tanggal_cuti.*' => 'required|date|distinct',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'durasi_hari.required' => 'Durasi cuti wajib dipilih.',
            'durasi_hari.integer' => 'Durasi cuti tidak valid.',
            'durasi_hari.in' => 'Durasi cuti maksimal 3 hari dalam satu pengajuan.',
            'tanggal_cuti.required' => 'Tanggal cuti wajib diisi.',
            'tanggal_cuti.array' => 'Tanggal cuti tidak valid.',
            'tanggal_cuti.size' => 'Jumlah tanggal harus sesuai dengan durasi cuti.',
            'tanggal_cuti.*.required' => 'Setiap tanggal cuti wajib diisi.',
            'tanggal_cuti.*.date' => 'Format tanggal cuti tidak valid.',
            'tanggal_cuti.*.distinct' => 'Tanggal cuti tidak boleh sama.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
