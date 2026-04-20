<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Food',
            'Beverages',
            'Essential Goods',
            'Snack',
            'Groceries',
            'Frozen Food',
            'Kitchen Spices',
            'Cleaning Supplies',
            'Personal Care',
            'Stationery',
            'Baby Care',
            'Health Care',
            'Bakery',
            'Fresh Products',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name], [
                'is_active' => true,
            ]);
        }
    }
}
