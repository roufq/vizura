<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_updates_stock_and_cost(): void
    {
        $owner = $this->makeOwner();
        $supplier = Supplier::factory()->create();
        $product = $this->makeProduct();

        $stockItem = StockItem::query()
            ->where('location_id', $owner->active_location_id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $stockItem->setQuantity(5);

        $product->update(['cost_price' => 100]);

        $response = $this
            ->actingAs($owner)
            ->withSession(['active_location_id' => $owner->active_location_id])
            ->post(route('purchases.store', absolute: false), [
                'supplier_id' => $supplier->id,
                'reference_no' => 'PO-001',
                'discount_amount' => 0,
                'tax_amount' => 0,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'unit_cost' => 200,
                    ],
                ],
            ]);

        $response->assertRedirect(route('purchases.index', absolute: false));

        $this->assertSame('10.00', $stockItem->fresh()->quantity_on_hand);
        $this->assertSame('150.00', $product->fresh()->cost_price);
        $this->assertSame(
            '150.00',
            ProductPrice::query()
                ->where('location_id', $owner->active_location_id)
                ->where('product_id', $product->id)
                ->value('cost_price')
        );
    }

    private function makeOwner(): User
    {
        Role::firstOrCreate(['name' => 'Owner']);
        $location = Location::factory()->create();
        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);

        $user->assignRole('Owner');

        return $user;
    }

    private function makeProduct(): Product
    {
        $unit = Unit::factory()->create();

        return Product::create([
            'sku' => 'SKU-'.fake()->unique()->numerify('###'),
            'name' => 'Produk Beli',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);
    }
}
