<?php

namespace Database\Factories;

use App\Models\Presensi;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresensiFactory extends Factory
{
    protected $model = Presensi::class;

    public function definition(): array
    {
        return [
            'nik' => \App\Models\Karyawan::factory(),
            'tgl_presensi' => now('Asia/Jakarta')->format('Y-m-d'),
            'jam_in' => '08:00:00',
            'jam_out' => null,
            'foto_in' => 'test-photo-in.jpg',
            'foto_out' => null,
            'lokasi_in' => '-6.200000,106.816666',
            'lokasi_out' => null,
            'terlambat' => 0,
        ];
    }

    public function withJamIn(string $jamIn): static
    {
        return $this->state(fn (array $attributes) => [
            'jam_in' => $jamIn,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'tgl_presensi' => $date,
        ]);
    }
}
