<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockTransfer;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_and_receive_transfer_updates_stock(): void
    {
        $owner = $this->makeOwner();
        $destination = Location::factory()->create();
        $product = $this->makeProduct();

        $sourceStock = StockItem::query()
            ->where('location_id', $owner->active_location_id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $sourceStock->setQuantity(10);

        $transfer = StockTransfer::create([
            'reference_no' => 'TRF-001',
            'source_location_id' => $owner->active_location_id,
            'destination_location_id' => $destination->id,
            'status' => 'draft',
            'requested_by' => $owner->id,
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this
            ->actingAs($owner)
            ->withSession(['active_location_id' => $owner->active_location_id])
            ->post(route('stock-transfers.send', $transfer, absolute: false));

        $response->assertRedirect(route('stock-transfers.index', absolute: false));
        $this->assertSame('sent', $transfer->fresh()->status);
        $this->assertSame('5.00', $sourceStock->fresh()->quantity_on_hand);

        $receiver = $this->makeManager($destination->id);

        $response = $this
            ->actingAs($receiver)
            ->withSession(['active_location_id' => $destination->id])
            ->post(route('stock-transfers.receive', $transfer, absolute: false));

        $response->assertRedirect(route('stock-transfers.index', absolute: false));
        $this->assertSame('received', $transfer->fresh()->status);

        $destinationStock = StockItem::query()
            ->where('location_id', $destination->id)
            ->where('product_id', $product->id)
            ->firstOrFail();
        $this->assertSame('5.00', $destinationStock->quantity_on_hand);
    }

    public function test_kepala_toko_cannot_delete_transfer_from_other_location(): void
    {
        Role::firstOrCreate(['name' => 'KepalaToko']);

        $locationA = Location::factory()->create();
        $locationB = Location::factory()->create();

        $user = User::factory()->create([
            'active_location_id' => $locationA->id,
        ]);
        $user->assignRole('KepalaToko');

        $transfer = StockTransfer::create([
            'reference_no' => 'TRF-OTHER',
            'source_location_id' => $locationB->id,
            'destination_location_id' => $locationA->id,
            'status' => 'draft',
            'requested_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_location_id' => $locationA->id])
            ->delete(route('stock-transfers.destroy', $transfer, absolute: false));

        $response->assertRedirect(route('stock-transfers.index', absolute: false));
        $response->assertSessionHasErrors(['location_id']);
        $this->assertDatabaseHas('stock_transfers', ['id' => $transfer->id]);
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

    private function makeManager(int $locationId): User
    {
        Role::firstOrCreate(['name' => 'Manager']);

        $user = User::factory()->create([
            'active_location_id' => $locationId,
        ]);

        $user->assignRole('Manager');

        return $user;
    }

    private function makeProduct(): Product
    {
        $unit = Unit::factory()->create();

        return Product::create([
            'sku' => 'SKU-'.fake()->unique()->numerify('###'),
            'name' => 'Produk Transfer',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);
    }
}
