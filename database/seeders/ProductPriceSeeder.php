<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class ProductPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location = Location::query()->first();
        if (! $location) {
            return;
        }

        $products = Product::query()->take(3)->get();
        foreach ($products as $product) {
            ProductPrice::updateOrCreate(
                [
                    'location_id' => $location->id,
                    'product_id' => $product->id,
                ],
                [
                    'sale_price' => $product->sale_price,
                    'cost_price' => $product->cost_price,
                ]
            );
        }
    }
}
