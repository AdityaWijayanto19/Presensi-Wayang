<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TogglePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission' => 'required|in:location,camera,notifications',
        ];
    }

    public function messages(): array
    {
        return [
            'permission.required' => 'Izin wajib dipilih.',
            'permission.in' => 'Izin tidak valid.',
        ];
    }
}
