<?php

namespace App\Http\Requests\Push;

use Illuminate\Foundation\Http\FormRequest;

class SubscribePushRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'endpoint' => 'required|url',
            'public_key' => 'required|string',
            'auth_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'endpoint.required' => 'Endpoint wajib diisi.',
            'endpoint.url' => 'Format URL tidak valid.',
            'public_key.required' => 'Public key wajib diisi.',
            'public_key.string' => 'Public key harus berupa teks.',
            'auth_token.required' => 'Auth token wajib diisi.',
            'auth_token.string' => 'Auth token harus berupa teks.',
        ];
    }
}
