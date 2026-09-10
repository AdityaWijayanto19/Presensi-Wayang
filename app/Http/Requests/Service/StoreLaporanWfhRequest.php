<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanWfhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'laporan_deskripsi' => 'required|string|min:10|max:3000',
            'laporan_images' => 'required|array|min:2|max:5',
            'laporan_images.*' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'laporan_deskripsi.required' => 'Deskripsi laporan wajib diisi.',
            'laporan_deskripsi.string' => 'Deskripsi laporan harus berupa teks.',
            'laporan_deskripsi.min' => 'Deskripsi laporan minimal 10 karakter.',
            'laporan_deskripsi.max' => 'Deskripsi laporan maksimal 3000 karakter.',
            'laporan_images.required' => 'Gambar laporan wajib diupload.',
            'laporan_images.array' => 'Format gambar tidak valid.',
            'laporan_images.min' => 'Minimal 2 gambar harus diupload.',
            'laporan_images.max' => 'Maksimal 5 gambar yang diizinkan.',
            'laporan_images.*.required' => 'Setiap gambar wajib diisi.',
            'laporan_images.*.image' => 'File harus berupa gambar.',
            'laporan_images.*.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'laporan_images.*.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }
}
