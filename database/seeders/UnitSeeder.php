<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Pcs', 'abbreviation' => 'PCS'],
            ['name' => 'Box', 'abbreviation' => 'BOX'],
            ['name' => 'Kg', 'abbreviation' => 'KG'],
            ['name' => 'Gram', 'abbreviation' => 'G'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Ml', 'abbreviation' => 'ML'],
            ['name' => 'Pack', 'abbreviation' => 'PK'],
            ['name' => 'Carton', 'abbreviation' => 'CTN'],
            ['name' => 'Bottle', 'abbreviation' => 'BTL'],
            ['name' => 'Can', 'abbreviation' => 'CAN'],
            ['name' => 'Sachet', 'abbreviation' => 'SCT'],
            ['name' => 'Dozen', 'abbreviation' => 'DZN'],
            ['name' => 'Bundle', 'abbreviation' => 'BDL'],
            ['name' => 'Sack', 'abbreviation' => 'SCK'],
            ['name' => 'Tray', 'abbreviation' => 'TRY'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], [
                'abbreviation' => $unit['abbreviation'],
                'is_active' => true,
            ]);
        }
    }
}
