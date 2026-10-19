<?php

namespace App\Http\Requests\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StoreCutiDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Rule "size:" hanya menerima angka literal, bukan nama field.
        // "size:durasi_hari" membuat Brick\Math gagal parse "durasi_hari"
        // menjadi angka dan melempar NumberFormatException (HTTP 500).
        $durasi = (int) $this->input('durasi_hari');

        return [
            'durasi_hari' => 'required|integer|in:1,2,3',
            'tanggal_cuti' => 'required|array' . ($durasi > 0 ? "|size:{$durasi}" : ''),
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

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('StoreCutiDraftRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
        ]);

        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
