<?php

namespace Database\Factories;

use App\Models\Karyawan;
use App\Models\Unitperusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;

    private static int $nikCounter = 1000;

    public function definition(): array
    {
        $nik = (string) (self::$nikCounter++);
        $unit = Unitperusahaan::first()?->unit ?? 'Unit Testing';

        return [
            'nik' => $nik,
            'nama_lengkap' => fake()->name(),
            'jabatan' => 'Staff',
            'posisi' => fake()->jobTitle(),
            'role_approved' => 'Staff',
            'atasan_nik' => null,
            'unit' => $unit,
            'unit_id' => Unitperusahaan::first()?->id ?? 1,
            'no_hp' => fake()->phoneNumber(),
            'foto' => 'nophoto.png',
            'password' => Hash::make('password'),
        ];
    }

    public function direksi(): static
    {
        return $this->state(fn (array $attributes) => [
            'jabatan' => 'Direktur',
            'role_approved' => 'Direktur',
            'atasan_nik' => null,
        ]);
    }

    public function withAtasan(string $atasanNik): static
    {
        return $this->state(fn (array $attributes) => [
            'atasan_nik' => $atasanNik,
        ]);
    }

    public function spv(): static
    {
        return $this->state(fn (array $attributes) => [
            'jabatan' => 'SPV',
            'role_approved' => 'SPV',
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'jabatan' => 'Manager',
            'role_approved' => 'Manager',
        ]);
    }
}
