<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_user' => 'required',
            'email' => 'required|email|unique:users,email',
            'unit' => 'required|exists:unitperusahaans,unit',
            'role' => 'required|exists:roles,name',
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_user.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'unit.required' => 'Unit wajib dipilih.',
            'unit.exists' => 'Unit tidak ditemukan.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role tidak ditemukan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
