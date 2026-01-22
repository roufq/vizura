<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_sku_must_be_unique(): void
    {
        $user = $this->makeOwner();
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        Product::create([
            'sku' => 'SKU-100',
            'name' => 'Produk Awal',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_location_id' => $user->active_location_id])
            ->post(route('products.store', absolute: false), [
                'sku' => 'SKU-100',
                'name' => 'Produk Duplikat',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'sale_price' => 12000,
                'cost_price' => 9000,
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors(['sku']);
    }

    public function test_stock_items_are_initialized_for_each_location(): void
    {
        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();
        $unit = Unit::factory()->create();

        $product = Product::create([
            'sku' => 'SKU-200',
            'name' => 'Produk Stok',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);

        $this->assertSame(2, StockItem::query()->count());
        $this->assertTrue(
            StockItem::query()
                ->where('product_id', $product->id)
                ->where('location_id', $locationA->id)
                ->exists()
        );
        $this->assertTrue(
            StockItem::query()
                ->where('product_id', $product->id)
                ->where('location_id', $locationB->id)
                ->exists()
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
}
