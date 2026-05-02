<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\StockItem;
use App\Support\ActiveLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function search(Request $request): JsonResponse
    {
        $locationId = ActiveLocation::id();
        $query = trim($request->string('query')->toString() ?: $request->string('q')->toString() ?: $request->string('term')->toString());

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::query()
            ->where('is_active', true)
            ->with(['unit'])
            ->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $priceOverrides = $locationId
            ? ProductPrice::query()
                ->where('location_id', $locationId)
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('sale_price', 'product_id')
            : collect();

        $stockLevels = $locationId
            ? StockItem::query()
                ->where('location_id', $locationId)
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('quantity_on_hand', 'product_id')
            : collect();

        $results = $products->map(fn (Product $product): array => [
            'id' => $product->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'unit' => $product->unit?->name,
            'price' => (float) ($priceOverrides[$product->id] ?? $product->sale_price),
            'stock' => (float) ($stockLevels[$product->id] ?? 0),
            'block_when_out_of_stock' => (bool) $product->block_when_out_of_stock,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
        ]);

        return response()->json($results);
    }
}
