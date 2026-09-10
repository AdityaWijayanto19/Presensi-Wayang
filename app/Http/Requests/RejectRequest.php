<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejected_reason' => 'required|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'rejected_reason.required' => 'Alasan penolakan wajib diisi.',
            'rejected_reason.string' => 'Alasan penolakan harus berupa teks.',
            'rejected_reason.min' => 'Alasan penolakan minimal 5 karakter.',
            'rejected_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
