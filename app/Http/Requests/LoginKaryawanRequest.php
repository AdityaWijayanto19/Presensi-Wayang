<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginKaryawanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }
}
