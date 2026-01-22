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
            'Makanan',
            'Minuman',
            'Bahan Pokok',
            'Snack',
            'Sembako',
            'Frozen Food',
            'Bumbu Dapur',
            'Kebersihan',
            'Perawatan',
            'ATK',
            'Perlengkapan Bayi',
            'Kesehatan',
            'Roti dan Kue',
            'Produk Segar',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name], [
                'is_active' => true,
            ]);
        }
    }
}
