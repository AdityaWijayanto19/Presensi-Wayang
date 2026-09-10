<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreLemburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_lembur' => 'required|date',
            'durasi' => 'required|in:1 Jam,1.5 Jam,2 Jam,2.5 Jam,3 Jam,3.5 Jam,4 Jam,4.5 Jam,5 Jam,Prorate',
            'file_form' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
            'file_laporan' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tgl_lembur.date' => 'Format tanggal tidak valid.',
            'durasi.required' => 'Durasi wajib dipilih.',
            'durasi.in' => 'Durasi tidak valid.',
            'file_form.required' => 'Form lembur wajib diupload.',
            'file_form.mimes' => 'Format file harus PDF, DOC, DOCX, JPG, JPEG, atau PNG.',
            'file_form.max' => 'Ukuran file maksimal 4MB.',
            'file_laporan.required' => 'Laporan lembur wajib diupload.',
            'file_laporan.mimes' => 'Format file harus PDF, DOC, DOCX, JPG, JPEG, atau PNG.',
            'file_laporan.max' => 'Ukuran file maksimal 4MB.',
        ];
    }
}
