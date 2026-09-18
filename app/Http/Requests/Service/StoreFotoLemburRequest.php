<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreFotoLemburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:mulai,selesai',
            'image' => 'required|string|max:10485760',
            'lokasi' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe foto wajib diisi.',
            'type.in' => 'Tipe foto harus mulai atau selesai.',
            'image.required' => 'Foto wajib diambil.',
            'image.string' => 'Format foto tidak valid.',
        ];
    }
}
