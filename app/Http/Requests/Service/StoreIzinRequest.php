<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

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
            'jenis_izin' => 'required',
            'file' => 'required|mimes:pdf,doc,docx|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_izin.required' => 'Tanggal izin wajib diisi.',
            'tgl_izin.date' => 'Format tanggal tidak valid.',
            'jenis_izin.required' => 'Jenis izin wajib dipilih.',
            'file.required' => 'Dokumen izin wajib diupload.',
            'file.mimes' => 'Format file harus PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran file maksimal 4MB.',
        ];
    }
}
