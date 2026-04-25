<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $admin = User::firstOrCreate([
            'email' => 'admin@vizura.com',
        ], [
            'name' => 'Super Admin Vizura',
            'password' => bcrypt('password'), // Change this in production
            'tenant_id' => null, // Super Admin has no tenant
        ]);

        $admin->assignRole($role);
    }
}
