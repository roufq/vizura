<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_adjustment_updates_stock_and_writes_audit_log(): void
    {
        $user = $this->makeOwner();
        $product = $this->makeProduct();

        $adjustment = StockAdjustment::create([
            'location_id' => $user->active_location_id,
            'product_id' => $product->id,
            'requested_by' => $user->id,
            'quantity_delta' => 5,
            'reason' => 'Stok bertambah',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['active_location_id' => $user->active_location_id])
            ->post(route('stock-adjustments.approve', $adjustment, absolute: false));

        $response->assertRedirect(route('stock-adjustments.index', absolute: false));

        $stockItem = StockItem::query()
            ->where('location_id', $user->active_location_id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $this->assertSame('5.00', $stockItem->quantity_on_hand);
        $this->assertSame('approved', $adjustment->fresh()->status);
        $this->assertNotNull(AuditLog::query()->where('action', 'stock_adjustment_approved')->first());
    }

    public function test_bulk_stock_adjustment_store(): void
    {
        $user = $this->makeOwner();
        $p1 = $this->makeProduct();
        $p2 = $this->makeProduct();

        $response = $this
            ->actingAs($user)
            ->withSession(['active_location_id' => $user->active_location_id])
            ->post(route('stock-adjustments.store'), [
                'reason' => 'Bulk adjustment test',
                'items' => [
                    ['product_id' => $p1->id, 'quantity_delta' => 10],
                    ['product_id' => $p2->id, 'quantity_delta' => -5],
                ],
            ]);

        $response->assertRedirect(route('stock-adjustments.index'));
        $this->assertEquals(2, StockAdjustment::where('reason', 'Bulk adjustment test')->count());

        $adj = StockAdjustment::where('product_id', $p1->id)->first();
        $this->assertNotNull($adj->reference_no);
        $this->assertEquals(10, $adj->quantity_delta);
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
            'name' => 'Produk Uji',
            'unit_id' => $unit->id,
            'sale_price' => 10000,
            'cost_price' => 8000,
            'is_taxable' => false,
            'is_active' => true,
            'block_when_out_of_stock' => false,
        ]);
    }
}
