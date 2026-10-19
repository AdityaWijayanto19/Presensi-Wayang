<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Permission cuti-approve dibuat lewat migration (bukan seeder) supaya
     * otomatis ikut `php artisan migrate --force` saat deploy.
     *
     * Kenapa penting: route approve/reject cuti memakai
     * middleware `permission:cuti-approve,user`, dan tombol di view dibungkus
     * @can('cuti-approve'). Tidak ada Gate::before bypass untuk super_admin,
     * jadi bila permission ini absen di DB production, Spatie melempar
     * PermissionDoesNotExist dan halaman cuti admin error.
     *
     * Pemberian ke role memakai givePermissionTo (additive), BUKAN
     * syncPermissions, supaya permission lain milik role tidak ikut ter-reset.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate([
            'name' => 'cuti-approve',
            'guard_name' => 'user',
        ]);

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'user',
        ]);
        $superAdmin->givePermissionTo('cuti-approve');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin', 'user');
        if ($superAdmin) {
            $superAdmin->revokePermissionTo('cuti-approve');
        }

        Permission::where('name', 'cuti-approve')
            ->where('guard_name', 'user')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
