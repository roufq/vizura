<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\StockItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_report_uses_location_cost_price(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $location = Location::factory()->create();
        $locationOther = Location::factory()->create();

        $product = Product::factory()->create([
            'cost_price' => 50,
        ]);

        StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $location->id)
            ->where('product_id', $product->id)
            ->update(['quantity_on_hand' => 5]);

        StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $locationOther->id)
            ->where('product_id', $product->id)
            ->update(['quantity_on_hand' => 8]);

        ProductPrice::create([
            'location_id' => $location->id,
            'product_id' => $product->id,
            'cost_price' => 120,
        ]);

        ProductPrice::create([
            'location_id' => $locationOther->id,
            'product_id' => $product->id,
            'cost_price' => 200,
        ]);

        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);
        $user->assignRole('Owner');

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $location->id])
            ->get(route('reports.stock', ['location_id' => $location->id]));

        $response->assertOk();
        $this->assertEquals(600, (float) $response->viewData('totalValue'));
    }

    public function test_sales_and_cash_up_reports_use_posted_at_for_date_filters(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $location = Location::factory()->create();
        $user = User::factory()->create([
            'active_location_id' => $location->id,
        ]);
        $user->assignRole('Owner');

        $inRangeDate = Carbon::parse('2026-02-01');
        $outRangeDate = Carbon::parse('2026-01-01');

        $saleInRange = Sale::create([
            'location_id' => $location->id,
            'reference_no' => 'SALE-IN',
            'type' => 'sale',
            'status' => 'posted',
            'subtotal' => 100,
            'order_discount' => 0,
            'tax_amount' => 0,
            'total' => 100,
            'posted_at' => $inRangeDate,
            'created_at' => $inRangeDate,
        ]);

        $saleOutOfRange = Sale::create([
            'location_id' => $location->id,
            'reference_no' => 'SALE-OUT',
            'type' => 'sale',
            'status' => 'posted',
            'subtotal' => 80,
            'order_discount' => 0,
            'tax_amount' => 0,
            'total' => 80,
            'posted_at' => $outRangeDate,
            'created_at' => $inRangeDate,
        ]);

        SalePayment::create([
            'sale_id' => $saleInRange->id,
            'location_id' => $location->id,
            'method' => 'cash',
            'amount' => 100,
        ]);

        SalePayment::create([
            'sale_id' => $saleOutOfRange->id,
            'location_id' => $location->id,
            'method' => 'cash',
            'amount' => 80,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_location_id' => $location->id])
            ->get(route('reports.sales', [
                'start_date' => $inRangeDate->toDateString(),
                'end_date' => $inRangeDate->toDateString(),
            ]));

        $response->assertOk();
        $sales = $response->viewData('sales');
        $this->assertTrue($sales->getCollection()->contains('id', $saleInRange->id));
        $this->assertFalse($sales->getCollection()->contains('id', $saleOutOfRange->id));

        $cashUpResponse = $this->actingAs($user)
            ->withSession(['active_location_id' => $location->id])
            ->get(route('reports.cash-up', [
                'start_date' => $inRangeDate->toDateString(),
                'end_date' => $inRangeDate->toDateString(),
            ]));

        $cashUpResponse->assertOk();
        $this->assertEquals(100, (float) $cashUpResponse->viewData('grandTotal'));
    }
}
