<?php

namespace Database\Seeders;

use App\Models\Unitperusahaan;
use Illuminate\Database\Seeder;

class UnitperusahaanSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['unit' => 'Advokat', 'perusahaan' => 'Werkudoro Law Firm', 'jam_masuk' => '08:30:00'],
            ['unit' => 'Arthama', 'perusahaan' => 'PT Arthama Global Indonesia', 'jam_masuk' => '10:05:00'],
            ['unit' => 'BTW Academy', 'perusahaan' => 'PT Bina Taruna Wiratama', 'jam_masuk' => '10:05:00'],
            ['unit' => 'Cerapproval', 'perusahaan' => 'PT Cerapproval Indonesia Manufaktur', 'jam_masuk' => '08:05:00'],
            ['unit' => 'In Wayang', 'perusahaan' => 'PT Wayang Arthasena Group', 'jam_masuk' => '10:05:00'],
            ['unit' => 'JTEN GROUP', 'perusahaan' => 'PT JELAJAH TEKNOLOGI NUSA GROUP', 'jam_masuk' => '08:05:00'],
            ['unit' => 'Lensagram', 'perusahaan' => 'PT Lensa Garuda Media', 'jam_masuk' => '10:05:00'],
            ['unit' => 'Wayang', 'perusahaan' => 'PT Wayang Arthasena Group', 'jam_masuk' => '08:05:00'],
            ['unit' => 'Werkudoro', 'perusahaan' => 'Werkudoro & Partners Law Firm', 'jam_masuk' => '10:05:00'],
        ];

        foreach ($units as $unit) {
            Unitperusahaan::firstOrCreate(
                ['unit' => $unit['unit']],
                $unit
            );
        }
    }
}
