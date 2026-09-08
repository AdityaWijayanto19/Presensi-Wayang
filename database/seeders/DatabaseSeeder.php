<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserPermissionSeeder::class,
        ]);

        // Buat user default super admin
        if (\App\Models\User::count() === 0) {
            $user = \App\Models\User::create([
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'unit' => 'Admin',
                'password' => '12345678',
            ]);
            $user->assignRole('super_admin');
        }
    }
}
