<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreIzinRequest extends FormRequest
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
            'keterangan' => 'required|string|min:5|max:500',
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_izin.required' => 'Tanggal izin wajib diisi.',
            'tgl_izin.date' => 'Format tanggal tidak valid.',
            'jenis_izin.required' => 'Kategori izin wajib dipilih.',
            'jenis_izin.in' => 'Kategori izin tidak valid.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'keterangan.min' => 'Keterangan minimal 5 karakter.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'bukti_file.required' => 'Bukti file wajib diupload.',
            'bukti_file.file' => 'Format file tidak valid.',
            'bukti_file.mimes' => 'Bukti file harus berupa jpg, jpeg, png, pdf, doc, atau docx.',
            'bukti_file.max' => 'Ukuran bukti file maksimal 4MB.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        Log::warning('StoreIzinRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $validator->getData(),
        ]);

        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
