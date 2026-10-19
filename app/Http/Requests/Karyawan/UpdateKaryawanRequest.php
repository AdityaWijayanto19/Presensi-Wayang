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
            'nama_lengkap' => 'required',
            'unit' => 'required|exists:unitperusahaans,unit',
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
            $terpakai = (int) \App\Models\Cuti::where('nik', $nik)
                ->approved()
                ->sum('durasi_hari');
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
            'nik.string' => 'NIK hanya boleh berisi teks.',
            'nik.max' => 'NIK maksimal 16 karakter.',
            'nik.unique' => 'NIK sudah dipakai karyawan lain. Silakan gunakan NIK lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'unit.required' => 'Unit perusahaan wajib dipilih.',
            'unit.exists' => 'Unit perusahaan tidak ditemukan.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'password.min' => 'Password minimal 5 karakter.',
            'password.string' => 'Password hanya boleh berisi teks.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan yang dipilih tidak valid.',
            'posisi.required' => 'Posisi wajib diisi.',
            'role_approved.in' => 'Role Approved yang dipilih tidak valid.',
            'atasan_nik.exists' => 'Atasan dengan NIK tersebut tidak ditemukan.',
            'jatah_cuti.required' => 'Jatah cuti wajib diisi.',
            'jatah_cuti.integer' => 'Jatah cuti harus berupa angka bulat.',
            'jatah_cuti.min' => 'Jatah cuti minimal 0 hari.',
            'jatah_cuti.max' => 'Jatah cuti maksimal 12 hari.',
            'jatah_cuti.gte' => 'Jatah cuti tidak boleh kurang dari cuti yang sudah terpakai (:value hari).',
        ];
    }

    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'unit' => 'Unit Perusahaan',
            'foto' => 'Foto',
            'password' => 'Password',
            'jabatan' => 'Jabatan',
            'posisi' => 'Posisi',
            'role_approved' => 'Role Approved',
            'atasan_nik' => 'Atasan',
            'jatah_cuti' => 'Jatah Cuti',
        ];
    }
}
