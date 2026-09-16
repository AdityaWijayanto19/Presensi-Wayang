<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanLemburRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deskripsi_pekerjaan' => 'required|string|min:10|max:3000',
            'laporan_images' => 'required|array|min:2|max:5',
            'laporan_images.*' => 'required|image|mimes:jpg,jpeg,png|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'deskripsi_pekerjaan.required' => 'Deskripsi pekerjaan wajib diisi.',
            'deskripsi_pekerjaan.min' => 'Deskripsi pekerjaan minimal 10 karakter.',
            'deskripsi_pekerjaan.max' => 'Deskripsi pekerjaan maksimal 3000 karakter.',
            'laporan_images.required' => 'Foto hasil kerja wajib diupload.',
            'laporan_images.array' => 'Format foto tidak valid.',
            'laporan_images.min' => 'Foto hasil kerja minimal 2 foto.',
            'laporan_images.max' => 'Foto hasil kerja maksimal 5 foto.',
            'laporan_images.*.required' => 'Setiap foto wajib diupload.',
            'laporan_images.*.image' => 'File harus berupa gambar.',
            'laporan_images.*.mimes' => 'Format gambar harus JPG, JPEG, atau PNG.',
            'laporan_images.*.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }
}
