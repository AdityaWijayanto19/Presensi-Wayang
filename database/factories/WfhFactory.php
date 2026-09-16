<?php

namespace Database\Factories;

use App\Models\Wfh;
use Illuminate\Database\Eloquent\Factories\Factory;

class WfhFactory extends Factory
{
    protected $model = Wfh::class;

    public function definition(): array
    {
        return [
            'nik' => \App\Models\Karyawan::factory(),
            'jabatan' => 'Staff',
            'posisi' => fake()->jobTitle(),
            'tgl_wfh' => now('Asia/Jakarta')->format('Y-m-d'),
            'deskripsi_pekerjaan' => fake()->sentence(),
            'keterangan' => fake()->sentence(),
            'atasan_nik' => null,
            'status' => 'pending_atasan',
            'atasan_status' => 'pending',
            'admin_status' => 'pending',
            'rejected_reason' => null,
            'pdf_form_path' => null,
            'laporan_deskripsi' => null,
            'laporan_file' => null,
            'laporan_images' => null,
            'laporan_atasan_nik' => null,
            'laporan_status' => null,
            'laporan_atasan_status' => null,
            'laporan_admin_status' => null,
            'laporan_rejected_reason' => null,
            'laporan_approved_at' => null,
            'approved_at' => null,
            'dikirim_tanggal' => now('Asia/Jakarta'),
        ];
    }

    public function pendingAtasan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_atasan',
            'atasan_status' => 'pending',
        ]);
    }

    public function pendingAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_admin',
            'atasan_status' => 'approved',
            'admin_status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'atasan_status' => 'approved',
            'admin_status' => 'approved',
            'approved_at' => now('Asia/Jakarta'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejected_reason' => fake()->sentence(),
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'tgl_wfh' => $date,
        ]);
    }

    public function withAtasanNik(string $atasanNik): static
    {
        return $this->state(fn (array $attributes) => [
            'atasan_nik' => $atasanNik,
        ]);
    }
}
