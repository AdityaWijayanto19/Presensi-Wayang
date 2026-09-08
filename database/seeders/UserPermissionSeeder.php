<?php

namespace Database\Seeders;

use App\Models\UserPermission;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['location', 'camera', 'notifications'];

        foreach (Karyawan::all() as $karyawan) {
            foreach ($permissions as $perm) {
                UserPermission::firstOrCreate([
                    'user_type' => 'karyawan',
                    'user_id' => $karyawan->nik,
                    'permission_name' => $perm,
                ], ['is_enabled' => true]);
            }
        }

        foreach (User::all() as $user) {
            foreach ($permissions as $perm) {
                UserPermission::firstOrCreate([
                    'user_type' => 'admin',
                    'user_id' => (string) $user->id,
                    'permission_name' => $perm,
                ], ['is_enabled' => true]);
            }
        }
    }
}
