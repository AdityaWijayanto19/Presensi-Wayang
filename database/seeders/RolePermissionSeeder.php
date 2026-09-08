<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard-view',
            'karyawan-view',
            'karyawan-create',
            'karyawan-edit',
            'karyawan-delete',
            'unit-view',
            'unit-create',
            'unit-edit',
            'unit-delete',
            'monitoring-view',
            'presensi-view',
            'presensi-edit',
            'izin-view',
            'izin-delete',
            'lembur-view',
            'lembur-delete',
            'wfh-view',
            'wfh-delete',
            'wfh-approve',
            'laporan-view',
            'user-manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'user']);
        }

        // Super Admin - semua permission
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'user']);
        $superAdmin->syncPermissions($permissions);

        // Admin - semua kecuali approve dan user-manage
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'user']);
        $adminPermissions = array_diff($permissions, ['wfh-approve', 'user-manage', 'presensi-edit']);
        $admin->syncPermissions($adminPermissions);

        // Owner - hanya view, tanpa create/edit/delete
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'user']);
        $ownerPermissions = [
            'dashboard-view',
            'karyawan-view',
            'unit-view',
            'monitoring-view',
            'presensi-view',
            'izin-view',
            'lembur-view',
            'wfh-view',
            'laporan-view',
        ];
        $owner->syncPermissions($ownerPermissions);

        // Update user ID 1 ke super_admin (hapus role lama)
        $user1 = User::find(1);
        if ($user1) {
            $user1->removeRole('administrator');
            $user1->assignRole('super_admin');
        }

        // Update user lainnya ke admin (hapus role lama)
        User::where('id', '!=', 1)->each(function ($user) {
            $user->removeRole('administrator');
            $user->removeRole('user');
            $user->assignRole('admin');
        });

        // Hapus role lama 'administrator' dan 'user' jika sudah tidak dipakai
        Role::where('name', 'administrator')->delete();
        Role::where('name', 'user')->delete();

        // Buat unit default untuk admin jika belum ada
        if (\App\Models\Unitperusahaan::count() === 0) {
            \App\Models\Unitperusahaan::create([
                'unit' => 'Admin',
                'perusahaan' => 'Admin Utama',
                'jam_masuk' => '08:00:00',
            ]);
        }
    }
}
