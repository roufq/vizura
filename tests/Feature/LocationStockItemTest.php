<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationStockItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_creation_initializes_stock_items_for_existing_products(): void
    {
        $unit = Unit::factory()->create();
        $product = Product::create([
            'sku' => 'SKU-'.fake()->unique()->numerify('###'),
            'name' => 'Produk Pusat',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);

        $location = Location::factory()->create();

        $this->assertTrue(
            StockItem::query()
                ->where('location_id', $location->id)
                ->where('product_id', $product->id)
                ->exists()
        );
    }
}
