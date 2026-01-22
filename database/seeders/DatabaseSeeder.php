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
        $this->call(RolePermissionSeeder::class);

        $location = Location::updateOrCreate(
            ['code' => 'HQ'],
            [
                'name' => 'Kantor Pusat',
                'address' => 'Jalan Contoh No. 1',
                'phone' => '021000000',
                'is_active' => true,
                'toko_pusat' => true,
            ]
        );

        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
            ]
        );

        if (! $user->hasRole('Owner')) {
            $user->assignRole('Owner');
        }

        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
            ]
        );

        if (! $manager->hasRole('Manager')) {
            $manager->assignRole('Manager');
        }

        $manager->locations()->sync([$location->id]);

        $headStore = User::updateOrCreate(
            ['email' => 'kepalatoko@example.com'],
            [
                'name' => 'Kepala Toko',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
            ]
        );

        if (! $headStore->hasRole('KepalaToko')) {
            $headStore->assignRole('KepalaToko');
        }

        $cashier = User::updateOrCreate(
            ['email' => 'kasir@example.com'],
            [
                'name' => 'Kasir User',
                'password' => bcrypt('password'),
                'active_location_id' => $location->id,
            ]
        );

        if (! $cashier->hasRole('Kasir')) {
            $cashier->assignRole('Kasir');
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
