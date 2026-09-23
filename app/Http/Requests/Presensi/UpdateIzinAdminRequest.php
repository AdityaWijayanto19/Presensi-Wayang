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
            'jenis_izin' => 'required|in:tidak_masuk,terlambat,setengah_hari,pulang_cepat,sakit',
            'jam_datang' => 'required_if:jenis_izin,terlambat|nullable|date_format:H:i|after_or_equal:08:00|before_or_equal:12:00',
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
            'jam_datang.date_format' => 'Format jam datang tidak valid (HH:MM).',
            'jam_datang.after_or_equal' => 'Jam datang tidak boleh sebelum 08:00.',
            'jam_datang.before_or_equal' => 'Jam datang tidak boleh setelah 12:00.',
        ];
    }
}
