<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockItem;
use App\Models\Supplier;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'receiver'])
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $referenceNo = $this->generateReferenceNo();

        return view('purchases.create', compact('suppliers', 'products', 'referenceNo'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $locationId = ActiveLocation::id();
        if (! $locationId) {
            return redirect()
                ->route('purchases.index')
                ->withErrors(['location_id' => 'Lokasi aktif wajib dipilih.']);
        }

        $data = $request->validated();
        $items = $this->normalizeItems($data['items'] ?? []);

        if ($items === []) {
            return redirect()
                ->route('purchases.create')
                ->withErrors(['items' => 'Item pembelian wajib diisi.'])
                ->withInput();
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $tax = (float) ($data['tax_amount'] ?? 0);
        $subtotal = collect($items)->sum('line_total');

        if ($discount > $subtotal) {
            return redirect()
                ->route('purchases.create')
                ->withErrors(['discount_amount' => 'Diskon tidak boleh melebihi subtotal.'])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request, $data, $items, $locationId, $discount, $tax, $subtotal): void {
                $total = max(0, $subtotal - $discount + $tax);

                $isPayable = $data['payment_method'] === 'payable';
                $paidTotal = $isPayable ? 0 : $total;
                $payableBalance = $isPayable ? $total : 0;
                $paymentStatus = $isPayable ? 'unpaid' : 'paid';

                $purchase = Purchase::create([
                    'location_id' => $locationId,
                    'supplier_id' => $data['supplier_id'] ?? null,
                    'reference_no' => $data['reference_no'],
                    'status' => 'received',
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'total' => $total,
                    'payment_method' => $data['payment_method'],
                    'paid_total' => $paidTotal,
                    'payable_balance' => $payableBalance,
                    'payment_status' => $paymentStatus,
                    'received_by' => $request->user()?->id,
                    'received_at' => now(),
                ]);

                foreach ($items as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'line_total' => $item['line_total'],
                    ]);

                    $stockItem = StockItem::firstOrCreate([
                        'location_id' => $locationId,
                        'product_id' => $item['product_id'],
                    ], [
                        'quantity_on_hand' => 0,
                    ]);

                    $currentQty = (float) $stockItem->quantity_on_hand;
                    $currentCost = $this->getCurrentCost($item['product_id'], $locationId);

                    $newQty = $currentQty + $item['quantity'];
                    $newCost = $newQty > 0
                        ? (($currentQty * $currentCost) + ($item['quantity'] * $item['unit_cost'])) / $newQty
                        : $item['unit_cost'];

                    $stockItem->adjustQuantity($item['quantity']);

                    ProductPrice::updateOrCreate(
                        [
                            'location_id' => $locationId,
                            'product_id' => $item['product_id'],
                        ],
                        [
                            'cost_price' => $newCost,
                        ]
                    );

                    Product::whereKey($item['product_id'])->update([
                        'cost_price' => $newCost,
                    ]);
                }

                AuditLog::create([
                    'user_id' => $request->user()?->id,
                    'location_id' => $locationId,
                    'action' => 'purchase_received',
                    'metadata' => [
                        'purchase_id' => $purchase->id,
                        'reference_no' => $purchase->reference_no,
                        'total' => $purchase->total,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'occurred_at' => now(),
                ]);

                app(AccountingService::class)->recordPurchase($purchase);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('purchases.create')
                ->withErrors($exception->errors())
                ->withInput();
        }

        return redirect()
            ->route('purchases.index')
            ->with('status', 'Pembelian berhasil disimpan.');
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item): bool => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0)
            ->map(fn ($item): array => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
                'unit_cost' => (float) $item['unit_cost'],
                'line_total' => (float) $item['quantity'] * (float) $item['unit_cost'],
            ])
            ->values()
            ->all();
    }

    private function getCurrentCost(int $productId, int $locationId): float
    {
        $override = ProductPrice::query()
            ->where('location_id', $locationId)
            ->where('product_id', $productId)
            ->value('cost_price');

        if ($override !== null) {
            return (float) $override;
        }

        return (float) Product::query()->whereKey($productId)->value('cost_price');
    }

    private function generateReferenceNo(): string
    {
        return 'PO-'.now()->format('Ymd-His').'-'.random_int(100, 999);
    }
}
