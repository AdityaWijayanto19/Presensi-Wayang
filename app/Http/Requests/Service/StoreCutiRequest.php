<?php

namespace App\Http\Requests\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreCutiRequest extends FormRequest
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
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
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
            'bukti_file.required' => 'File cuti wajib diupload.',
            'bukti_file.file' => 'Format file tidak valid.',
            'bukti_file.mimes' => 'File cuti harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_file.max' => 'Ukuran file cuti maksimal 4MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('StoreCutiRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
        ]);

        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
