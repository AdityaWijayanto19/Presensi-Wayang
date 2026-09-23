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
            'keterangan' => 'required|string|min:5|max:1000',
            'tgl_lembur' => 'required|date|after_or_equal:today',
            'durasi_jam' => 'required|in:1,1.5,2,2.5,3,3.5,4,4.5,5,prorate',
            'jam_mulai' => 'required|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'keterangan.required' => 'Keterangan lembur wajib diisi.',
            'keterangan.min' => 'Keterangan lembur minimal 5 karakter.',
            'keterangan.max' => 'Keterangan lembur maksimal 1000 karakter.',
            'tgl_lembur.required' => 'Tanggal lembur wajib diisi.',
            'tgl_lembur.date' => 'Format tanggal tidak valid.',
            'tgl_lembur.after_or_equal' => 'Tanggal lembur tidak boleh sebelum hari ini.',
            'durasi_jam.required' => 'Durasi lembur wajib diisi.',
            'durasi_jam.in' => 'Durasi lembur harus 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, atau Prorate.',
            'jam_mulai.required' => 'Rencana jam mulai lembur wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid (HH:MM).',
        ];
    }
}
