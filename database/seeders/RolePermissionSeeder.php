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
            'lembur-approve',
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
        $adminPermissions = array_diff($permissions, ['wfh-approve', 'lembur-approve', 'user-manage', 'presensi-edit']);
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
    }
}
