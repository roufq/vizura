<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed base roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'create',
            'update',
            'delete',
            'approve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::query()
            ->whereIn('name', ['Owner', 'Manager', 'KepalaToko', 'Kasir'])
            ->update(['guard_name' => 'web']);

        $owner = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $headStore = Role::firstOrCreate(['name' => 'KepalaToko', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);

        $owner->syncPermissions($permissions);
        $manager->syncPermissions(['create', 'update', 'approve']);
        $headStore->syncPermissions(['create', 'update', 'approve']);
        $cashier->syncPermissions(['create']);
    }
}
