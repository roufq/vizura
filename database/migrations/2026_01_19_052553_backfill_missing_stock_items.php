<?php

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $productIds = Product::query()->pluck('id');
        if ($productIds->isEmpty()) {
            return;
        }

        $now = now();

        foreach (Location::query()->pluck('id') as $locationId) {
            $existingProductIds = StockItem::withoutGlobalScope('active_location')
                ->where('location_id', $locationId)
                ->pluck('product_id');

            $missingProductIds = $productIds->diff($existingProductIds);
            if ($missingProductIds->isEmpty()) {
                continue;
            }

            $payload = $missingProductIds
                ->map(fn (int $productId): array => [
                    'location_id' => $locationId,
                    'product_id' => $productId,
                    'quantity_on_hand' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            StockItem::query()->insertOrIgnore($payload);
        }
    }

    public function down(): void
    {
        //
    }
};
