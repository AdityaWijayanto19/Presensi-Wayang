<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitperusahaanSeeder::class,
            RolePermissionSeeder::class,
            KaryawanSeeder::class,
            UserPermissionSeeder::class,
        ]);

        // Buat user default super admin
        if (\App\Models\User::count() === 0) {
            $WayangUnit = \App\Models\Unitperusahaan::where('unit', 'Wayang')->first();
            $user = \App\Models\User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'unit' => 'Wayang',
                'unit_id' => $WayangUnit?->id,
                'password' => '12345678',
            ]);
            $user->assignRole('super_admin');
        }
    }
}
