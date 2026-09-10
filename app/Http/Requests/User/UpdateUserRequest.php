<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'nullable|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password minimal 6 karakter',
        ];
    }
}
