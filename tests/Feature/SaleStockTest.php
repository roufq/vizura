<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_rejects_when_stock_is_insufficient_after_previous_sale(): void
    {
        $this->seed([
            RolePermissionSeeder::class,
            AccountSeeder::class,
        ]);

        $location = Location::factory()->create();
        $product = Product::factory()->create([
            'sale_price' => 100,
            'cost_price' => 80,
        ]);

        StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $location->id)
            ->where('product_id', $product->id)
            ->update(['quantity_on_hand' => 5]);

        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);
        $user->assignRole('Owner');

        $payload = [
            'reference_no' => 'POS-TEST-1',
            'customer_name' => null,
            'customer_phone' => null,
            'notes' => null,
            'order_discount' => 0,
            'tax_rate' => 0,
            'is_tax_inclusive' => false,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                    'line_discount' => 0,
                ],
            ],
            'payments' => [
                [
                    'method' => 'cash',
                    'amount' => 500,
                    'reference_no' => null,
                ],
            ],
            'action' => 'post',
        ];

        $this->actingAs($user)
            ->withSession(['active_location_id' => $location->id])
            ->post(route('sales.store'), $payload)
            ->assertRedirect();

        $this->actingAs($user)
            ->withSession(['active_location_id' => $location->id])
            ->post(route('sales.store'), [
                ...$payload,
                'reference_no' => 'POS-TEST-2',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 100,
                        'line_discount' => 0,
                    ],
                ],
                'payments' => [
                    [
                        'method' => 'cash',
                        'amount' => 100,
                        'reference_no' => null,
                    ],
                ],
            ])
            ->assertSessionHasErrors('items');
    }
}
