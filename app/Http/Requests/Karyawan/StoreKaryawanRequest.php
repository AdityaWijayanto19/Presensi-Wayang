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
            'nik' => 'required|string|max:16|unique:karyawans,nik',
            'nama_lengkap' => 'required',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,SPV,Manager,GM,Direktur',
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
            'nik.string' => 'NIK hanya boleh berisi teks.',
            'nik.max' => 'NIK maksimal 16 karakter.',
            'nik.unique' => 'NIK sudah dipakai karyawan lain. Silakan gunakan NIK lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan yang dipilih tidak valid.',
            'posisi.required' => 'Posisi wajib diisi.',
            'role_approved.in' => 'Role Approved yang dipilih tidak valid.',
            'atasan_nik.exists' => 'Atasan dengan NIK tersebut tidak ditemukan.',
            'unit.required' => 'Unit perusahaan wajib dipilih.',
            'unit.exists' => 'Unit perusahaan tidak ditemukan.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'jatah_cuti.required' => 'Jatah cuti wajib diisi.',
            'jatah_cuti.integer' => 'Jatah cuti harus berupa angka bulat.',
            'jatah_cuti.min' => 'Jatah cuti minimal 0 hari.',
            'jatah_cuti.max' => 'Jatah cuti maksimal 12 hari.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 5 karakter.',
            'password.string' => 'Password hanya boleh berisi teks.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'jabatan' => 'Jabatan',
            'posisi' => 'Posisi',
            'role_approved' => 'Role Approved',
            'atasan_nik' => 'Atasan',
            'unit' => 'Unit Perusahaan',
            'no_hp' => 'Nomor HP',
            'jatah_cuti' => 'Jatah Cuti',
            'password' => 'Password',
            'foto' => 'Foto',
        ];
    }
}
