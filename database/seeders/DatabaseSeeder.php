<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
        ]);

        $tenant = \App\Models\Tenant::updateOrCreate(
            ['slug' => 'main-tenant'],
            [
                'name' => 'Main Business',
                'plan' => 'enterprise',
                'status' => 'active',
            ]
        );

        $location = Location::updateOrCreate(
            ['code' => 'HQ'],
            [
                'name' => 'Main Headquarters',
                'address' => 'Sample Street No. 1',
                'phone' => '021000000',
                'is_active' => true,
                'toko_pusat' => true,
                'tenant_id' => $tenant->id,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Owner',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
                'tenant_id' => $tenant->id,
            ]
        );

        $tenant->update(['owner_id' => $user->id]);

        if (! $user->hasRole('Owner')) {
            $user->assignRole('Owner');
        }

        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
                'tenant_id' => $tenant->id,
            ]
        );

        if (! $manager->hasRole('Manager')) {
            $manager->assignRole('Manager');
        }

        $manager->locations()->sync([$location->id]);

        $headStore = User::updateOrCreate(
            ['email' => 'headstore@example.com'],
            [
                'name' => 'Head Store',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
                'tenant_id' => $tenant->id,
            ]
        );

        if (! $headStore->hasRole('HeadStore')) {
            $headStore->assignRole('HeadStore');
        }

        $cashier = User::updateOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Cashier User',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
                'tenant_id' => $tenant->id,
            ]
        );

        if (! $cashier->hasRole('Cashier')) {
            $cashier->assignRole('Cashier');
        }

        $this->call([
            AccountSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            StockItemSeeder::class,
            ProductPriceSeeder::class,
        ]);
    }
}
