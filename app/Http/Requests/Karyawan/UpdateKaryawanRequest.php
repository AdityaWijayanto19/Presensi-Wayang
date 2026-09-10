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
        return [
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|min:5',
            'jabatan' => 'required|in:Intern,Staff,SPV,Manager,GM,Direktur',
            'posisi' => 'required',
            'role_approved' => 'nullable|in:Staff,Manager,GM,Direktur',
            'atasan_nik' => 'nullable|exists:karyawans,nik',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran gambar maksimal 2MB.',
            'password.min' => 'Password minimal 5 karakter.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan tidak valid.',
            'posisi.required' => 'Posisi wajib diisi.',
            'role_approved.in' => 'Jabatan atasan tidak valid.',
            'atasan_nik.exists' => 'Atasan tidak ditemukan.',
        ];
    }
}
