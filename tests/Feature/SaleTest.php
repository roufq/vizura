<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_posted_sale_reduces_stock_and_creates_payments(): void
    {
        $this->seed(AccountSeeder::class);

        $cashier = $this->makeCashier();
        $product = $this->makeProduct();
        $stockItem = StockItem::query()
            ->where('location_id', $cashier->active_location_id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $stockItem->setQuantity(10);

        $response = $this
            ->actingAs($cashier)
            ->withSession(['active_location_id' => $cashier->active_location_id])
            ->post(route('sales.store', absolute: false), [
                'reference_no' => 'POS-1001',
                'order_discount' => 1000,
                'tax_amount' => 0,
                'is_tax_inclusive' => false,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 10000,
                        'line_discount' => 0,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'cash',
                        'amount' => 10000,
                    ],
                    [
                        'method' => 'card',
                        'amount' => 9000,
                        'reference_no' => 'CARD-1',
                    ],
                ],
                'action' => 'post',
            ]);

        $sale = Sale::query()->firstOrFail();
        $response->assertRedirect(route('sales.receipt', $sale, absolute: false));

        $this->assertSame('posted', $sale->status);
        $this->assertSame('19000.00', $sale->total);
        $this->assertSame(2, SalePayment::query()->where('sale_id', $sale->id)->count());
        $this->assertSame('8.00', $stockItem->fresh()->quantity_on_hand);
    }

    public function test_draft_sale_does_not_reduce_stock_or_create_payments(): void
    {
        $cashier = $this->makeCashier();
        $product = $this->makeProduct();
        $stockItem = StockItem::query()
            ->where('location_id', $cashier->active_location_id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $stockItem->setQuantity(10);

        $response = $this
            ->actingAs($cashier)
            ->withSession(['active_location_id' => $cashier->active_location_id])
            ->post(route('sales.store', absolute: false), [
                'reference_no' => 'POS-1002',
                'order_discount' => 0,
                'tax_amount' => 0,
                'is_tax_inclusive' => false,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 10000,
                        'line_discount' => 0,
                    ],
                ],
                'payments' => [],
                'action' => 'draft',
            ]);

        $response->assertRedirect(route('sales.index', absolute: false));

        $sale = Sale::query()->firstOrFail();
        $this->assertSame('draft', $sale->status);
        $this->assertSame('0.00', $sale->paid_total);
        $this->assertSame(0, SalePayment::query()->count());
        $this->assertSame('10.00', $stockItem->fresh()->quantity_on_hand);
    }

    private function makeCashier(): User
    {
        Role::firstOrCreate(['name' => 'Kasir']);
        $location = Location::factory()->create();
        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);

        $user->assignRole('Kasir');

        return $user;
    }

    private function makeProduct(): Product
    {
        $unit = Unit::factory()->create();

        return Product::create([
            'sku' => 'SKU-'.fake()->unique()->numerify('###'),
            'name' => 'Produk POS',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);
    }
}
