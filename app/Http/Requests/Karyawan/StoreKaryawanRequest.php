<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|unique:karyawans,nik',
            'nama_lengkap' => 'required',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,Manager,GM,Direktur',
            'atasan_nik' => 'nullable|exists:karyawans,nik',
            'unit' => 'required|exists:unitperusahaans,unit',
            'no_hp' => 'required',
            'jatah_cuti' => 'required|integer|min:0|max:12',
            'password' => 'required|min:5',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan tidak valid.',
            'posisi.required' => 'Posisi wajib diisi.',
            'role_approved.in' => 'Jabatan atasan tidak valid.',
            'atasan_nik.exists' => 'Atasan tidak ditemukan.',
            'unit.required' => 'Unit wajib dipilih.',
            'unit.exists' => 'Unit tidak ditemukan.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'jatah_cuti.required' => 'Jatah cuti wajib diisi.',
            'jatah_cuti.integer' => 'Jatah cuti tidak valid.',
            'jatah_cuti.min' => 'Jatah cuti minimal 0.',
            'jatah_cuti.max' => 'Jatah cuti maksimal 12.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 5 karakter.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
