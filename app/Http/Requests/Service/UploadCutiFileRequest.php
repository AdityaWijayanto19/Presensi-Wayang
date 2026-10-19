<?php

namespace App\Http\Requests\Service;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UploadCutiFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bukti_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_file.required' => 'File cuti wajib diupload.',
            'bukti_file.file' => 'Format file tidak valid.',
            'bukti_file.mimes' => 'File cuti harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_file.max' => 'Ukuran file cuti maksimal 4MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('UploadCutiFileRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
        ]);

        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
