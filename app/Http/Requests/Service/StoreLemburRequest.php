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
            'tgl_lembur' => 'required|date',
            'durasi_jam' => 'required|numeric|in:0.5,1,1.5,2,2.5,3',
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
            'durasi_jam.required' => 'Durasi lembur wajib diisi.',
            'durasi_jam.numeric' => 'Format durasi tidak valid.',
            'durasi_jam.in' => 'Durasi lembur harus 0.5, 1, 1.5, 2, 2.5, atau 3 jam.',
            'jam_mulai.required' => 'Rencana jam mulai lembur wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid (HH:MM).',
        ];
    }
}
