<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Database\Seeder;

class StockItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::query()->pluck('id');
        if ($locations->isEmpty()) {
            return;
        }

        $products = Product::query()->pluck('id');
        if ($products->isEmpty()) {
            return;
        }

        foreach ($products as $productId) {
            foreach ($locations as $locationId) {
                $stockItem = StockItem::firstOrCreate([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                ], [
                    'quantity_on_hand' => random_int(5, 50),
                ]);

                if ((float) $stockItem->quantity_on_hand === 0.0) {
                    $stockItem->update([
                        'quantity_on_hand' => random_int(5, 50),
                    ]);
                }
            }
        }
    }
}
