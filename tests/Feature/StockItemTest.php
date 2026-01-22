<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_item_cannot_be_saved_with_negative_quantity(): void
    {
        $stockItem = $this->makeStockItem();
        $stockItem->quantity_on_hand = -1;

        $this->expectException(ValidationException::class);

        $stockItem->save();
    }

    public function test_adjust_quantity_updates_value(): void
    {
        $stockItem = $this->makeStockItem();

        $stockItem->adjustQuantity(5);

        $this->assertSame('5.00', $stockItem->refresh()->quantity_on_hand);
    }

    private function makeStockItem(): StockItem
    {
        $location = Location::factory()->create();
        $unit = Unit::factory()->create();

        $product = Product::create([
            'sku' => 'SKU-'.fake()->unique()->numerify('###'),
            'name' => 'Produk Stok',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);

        return StockItem::query()
            ->where('product_id', $product->id)
            ->where('location_id', $location->id)
            ->firstOrFail();
    }
}
