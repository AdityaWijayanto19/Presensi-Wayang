<?php

namespace Database\Factories;

use App\Models\Unitperusahaan;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitperusahaanFactory extends Factory
{
    protected $model = \App\Models\Unitperusahaan::class;

    public function definition(): array
    {
        return [
            'unit' => fake()->unique()->word(),
            'perusahaan' => fake()->company(),
            'jam_masuk' => '08:00:00',
        ];
    }
}
