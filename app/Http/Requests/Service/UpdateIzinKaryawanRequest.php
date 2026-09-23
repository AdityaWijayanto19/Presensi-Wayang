<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UpdateIzinKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_izin' => 'required|date',
            'jenis_izin' => 'required|in:tidak_masuk,terlambat,setengah_hari,pulang_cepat,sakit',
            'jam_datang' => 'required_if:jenis_izin,terlambat|nullable|in:08:00,09:00,10:00,11:00,12:00',
            'keterangan' => 'required|string|min:5|max:500',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_izin.required' => 'Tanggal izin wajib diisi.',
            'tgl_izin.date' => 'Format tanggal tidak valid.',
            'jenis_izin.required' => 'Kategori izin wajib dipilih.',
            'jenis_izin.in' => 'Kategori izin tidak valid.',
            'jam_datang.required_if' => 'Jam datang wajib diisi untuk izin terlambat.',
            'jam_datang.in' => 'Jam datang tidak valid.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'keterangan.min' => 'Keterangan minimal 5 karakter.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'bukti_file.file' => 'Format file tidak valid.',
            'bukti_file.mimes' => 'Bukti file harus berupa jpg, jpeg, png, atau pdf.',
            'bukti_file.max' => 'Ukuran bukti file maksimal 4MB.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        Log::warning('UpdateIzinKaryawanRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $validator->getData(),
        ]);

        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
