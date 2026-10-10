<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nik' => 'required|string|max:16|unique:karyawans,nik,' . $this->route('nik') . ',nik',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'nullable|min:5',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,SPV,Manager,GM,Direktur',
            'atasan_nik' => 'nullable|exists:karyawans,nik',
            'jatah_cuti' => 'required|integer|min:0|max:12',
        ];

        $nik = $this->route('nik');
        if ($nik) {
            $terpakai = (int) \App\Models\Cuti::where('nik', $nik)->sum('durasi_hari');
            if ($terpakai > 0) {
                $rules['jatah_cuti'] .= '|gte:' . $terpakai;
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.max' => 'NIK maksimal 16 karakter.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran gambar maksimal 2MB.',
            'password.min' => 'Password minimal 5 karakter.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan tidak valid.',
            'posisi.required' => 'Posisi wajib diisi.',
            'role_approved.in' => 'Role Approved tidak valid.',
            'atasan_nik.exists' => 'Atasan tidak ditemukan.',
            'jatah_cuti.required' => 'Jatah cuti wajib diisi.',
            'jatah_cuti.integer' => 'Jatah cuti tidak valid.',
            'jatah_cuti.min' => 'Jatah cuti minimal 0.',
            'jatah_cuti.max' => 'Jatah cuti maksimal 12.',
            'jatah_cuti.gte' => 'Jatah cuti tidak boleh kurang dari cuti yang sudah terpakai.',
        ];
    }
}
