<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_receivable_in_assigned_location(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $locationActive = Location::factory()->create();
        $locationAssigned = Location::factory()->create();

        $user = User::factory()->create([
            'active_location_id' => $locationActive->id,
        ]);
        $user->assignRole('Manager');
        $user->locations()->attach($locationAssigned->id);

        $sale = Sale::create([
            'location_id' => $locationAssigned->id,
            'reference_no' => 'SALE-REC-1',
            'type' => 'sale',
            'status' => 'posted',
            'subtotal' => 100,
            'order_discount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'posted_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $locationActive->id])
            ->get(route('receivables.show', $sale));

        $response->assertOk();
    }

    public function test_manager_can_access_payable_in_assigned_location(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $locationActive = Location::factory()->create();
        $locationAssigned = Location::factory()->create();

        $user = User::factory()->create([
            'active_location_id' => $locationActive->id,
        ]);
        $user->assignRole('Manager');
        $user->locations()->attach($locationAssigned->id);

        $purchase = Purchase::create([
            'location_id' => $locationAssigned->id,
            'reference_no' => 'PO-001',
            'status' => 'received',
            'subtotal' => 100,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'payment_method' => 'payable',
            'paid_total' => 0,
            'payable_balance' => 100,
            'payment_status' => 'unpaid',
            'received_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $locationActive->id])
            ->get(route('purchases.payables.show', $purchase));

        $response->assertOk();
    }

    public function test_stock_transfer_send_requires_source_location_access(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $locationSource = Location::factory()->create();
        $locationDestination = Location::factory()->create();

        $product = Product::factory()->create();

        StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $locationSource->id)
            ->where('product_id', $product->id)
            ->update(['quantity_on_hand' => 10]);

        $transfer = StockTransfer::create([
            'reference_no' => 'TRF-001',
            'source_location_id' => $locationSource->id,
            'destination_location_id' => $locationDestination->id,
            'status' => 'draft',
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $locationDestination->id,
        ]);
        $user->assignRole('Manager');
        $user->locations()->attach($locationDestination->id);

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $locationDestination->id])
            ->post(route('stock-transfers.send', $transfer));

        $response->assertSessionHasErrors('location_id');
    }

    public function test_stock_transfer_receive_requires_destination_location_access(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $locationSource = Location::factory()->create();
        $locationDestination = Location::factory()->create();

        $product = Product::factory()->create();

        $transfer = StockTransfer::create([
            'reference_no' => 'TRF-002',
            'source_location_id' => $locationSource->id,
            'destination_location_id' => $locationDestination->id,
            'status' => 'sent',
            'sent_by' => User::factory()->create()->id,
            'sent_at' => now(),
        ]);

        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $locationSource->id,
        ]);
        $user->assignRole('Manager');
        $user->locations()->attach($locationSource->id);

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $locationSource->id])
            ->post(route('stock-transfers.receive', $transfer));

        $response->assertSessionHasErrors('location_id');
    }
}
