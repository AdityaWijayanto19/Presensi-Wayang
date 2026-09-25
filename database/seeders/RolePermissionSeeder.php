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
            'presensi-delete',
            'izin-view',
            'izin-delete',
            'izin-approve',
            'lembur-view',
            'lembur-delete',
            'lembur-approve',
            'wfh-view',
            'wfh-delete',
            'wfh-approve',
            'cuti-view',
            'cuti-edit',
            'cuti-delete',
            'laporan-view',
            'user-manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'user']);
        }

        // Super Admin - semua permission
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'user']);
        $superAdmin->syncPermissions($permissions);

        // Admin - hanya read + create (tidak bisa edit, delete, approve)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'user']);
        $adminPermissions = [
            'dashboard-view',
            'karyawan-view',
            'karyawan-create',
            'unit-view',
            'unit-create',
            'monitoring-view',
            'presensi-view',
            'izin-view',
            'lembur-view',
            'wfh-view',
            'cuti-view',
            'cuti-edit',
            'laporan-view',
        ];
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
            'cuti-view',
            'laporan-view',
        ];
        $owner->syncPermissions($ownerPermissions);
    }
}
