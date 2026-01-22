<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnSaleRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\VoidSaleRequest;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockItem;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko|Kasir');
    }

    public function index(): View
    {
        $sales = Sale::query()
            ->with(['cashier'])
            ->latest()
            ->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function create(Request $request): View
    {
        $draft = $this->resolveDraft($request->integer('draft_id'));

        return $this->renderCreateView($draft);
    }

    public function resume(Sale $sale): View|RedirectResponse
    {
        $draft = $this->resolveDraft($sale->id);

        if (! $draft) {
            return redirect()
                ->route('sales.index')
                ->withErrors(['draft' => 'Draft tidak ditemukan atau tidak bisa diakses.']);
        }

        return $this->renderCreateView($draft);
    }

    public function destroyDraft(Sale $sale): RedirectResponse
    {
        $draft = $this->resolveDraft($sale->id);

        if (! $draft) {
            return redirect()
                ->route('sales.create')
                ->withErrors(['draft' => 'Draft tidak ditemukan atau tidak bisa diakses.']);
        }

        DB::transaction(function () use ($draft): void {
            $draft->items()->delete();
            $draft->payments()->delete();
            $draft->delete();
        });

        return redirect()
            ->route('sales.create')
            ->with('status', 'Draft berhasil dihapus.');
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $locationId = ActiveLocation::id();
        if (! $locationId) {
            return redirect()
                ->route('sales.index')
                ->withErrors(['location_id' => 'Lokasi aktif wajib dipilih.']);
        }

        $data = $request->validated();
        $draftSale = $this->resolveDraft($data['draft_id'] ?? null);
        if (($data['draft_id'] ?? null) && ! $draftSale) {
            return redirect()
                ->route('sales.index')
                ->withErrors(['draft' => 'Draft tidak ditemukan atau tidak bisa diakses.']);
        }
        $items = $this->normalizeItems($data['items'] ?? []);

        if ($items === []) {
            return redirect()
                ->route('sales.create')
                ->withErrors(['items' => 'Item penjualan wajib diisi.'])
                ->withInput();
        }

        $subtotal = collect($items)->sum('line_total');
        $orderDiscount = (float) ($data['order_discount'] ?? 0);
        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $isTaxInclusive = (bool) ($data['is_tax_inclusive'] ?? false);
        $baseTotal = max(0, $subtotal - $orderDiscount);
        $normalizedTaxRate = $taxRate > 0 ? $taxRate : 0;
        $taxAmount = $normalizedTaxRate > 0
            ? ($isTaxInclusive
                ? $baseTotal - ($baseTotal / (1 + ($normalizedTaxRate / 100)))
                : $baseTotal * ($normalizedTaxRate / 100))
            : 0;
        $total = $isTaxInclusive ? $baseTotal : $baseTotal + $taxAmount;

        if ($orderDiscount > $subtotal) {
            return redirect()
                ->route('sales.create')
                ->withErrors(['order_discount' => 'Diskon order tidak boleh melebihi subtotal.'])
                ->withInput();
        }

        $payments = $this->normalizePayments($data['payments'] ?? []);
        $cashPayments = collect($payments)->filter(
            fn (array $payment): bool => ($payment['method'] ?? '') !== 'piutang'
        );
        $paidTotal = (float) $cashPayments->sum('amount');
        $changeDue = max(0, $paidTotal - $total);
        $receivablePayment = collect($payments)
            ->first(fn (array $payment): bool => ($payment['method'] ?? '') === 'piutang');
        $hasReceivablePayment = $receivablePayment !== null;
        $receivableBalance = max(0, $total - $paidTotal);
        $hasReceivable = $receivableBalance > 0;
        $paymentStatus = $hasReceivable
            ? 'partial'
            : 'paid';
        $cogsTotal = $data['action'] === 'post'
            ? $this->calculateCogsTotal($items, $locationId)
            : 0.0;
        $paymentLines = $data['action'] === 'post'
            ? $this->preparePaymentLines($payments, $changeDue, $receivableBalance)
            : [];

        if ($data['action'] === 'post' && $paidTotal < $total && ! $hasReceivablePayment) {
            return redirect()
                ->route('sales.create')
                ->withErrors(['payments' => 'Total pembayaran kurang dari total transaksi.'])
                ->withInput();
        }

        $sale = $draftSale;

        try {
            DB::transaction(function () use ($request, $data, $items, $payments, $locationId, $subtotal, $orderDiscount, $taxAmount, $isTaxInclusive, $total, $paidTotal, $changeDue, $receivableBalance, $paymentStatus, $cogsTotal, $paymentLines, &$sale): void {
                if ($data['action'] === 'post') {
                    $this->ensureStockAvailable($items, $locationId);
                }

                $saleData = [
                    'location_id' => $locationId,
                    'cashier_id' => $request->user()?->id,
                    'customer_name' => $data['customer_name'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'reference_no' => $data['reference_no'],
                    'type' => 'sale',
                    'status' => $data['action'] === 'post' ? 'posted' : 'draft',
                    'subtotal' => $subtotal,
                    'order_discount' => $orderDiscount,
                    'tax_amount' => $taxAmount,
                    'is_tax_inclusive' => $isTaxInclusive,
                    'total' => $total,
                    'paid_total' => $data['action'] === 'post' ? $paidTotal : 0,
                    'change_due' => $data['action'] === 'post' ? $changeDue : 0,
                    'receivable_balance' => $data['action'] === 'post' ? $receivableBalance : 0,
                    'payment_status' => $data['action'] === 'post' ? $paymentStatus : 'paid',
                    'posted_at' => $data['action'] === 'post' ? now() : null,
                ];

                if ($sale) {
                    $sale->update($saleData);
                    $sale->items()->delete();
                    $sale->payments()->delete();
                } else {
                    $sale = Sale::create($saleData);
                }

                foreach ($items as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'location_id' => $locationId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_discount' => $item['line_discount'],
                        'line_total' => $item['line_total'],
                    ]);
                }

                if ($data['action'] === 'post') {
                    foreach ($payments as $payment) {
                        SalePayment::create([
                            'sale_id' => $sale->id,
                            'location_id' => $locationId,
                            'method' => $payment['method'],
                            'amount' => $payment['amount'],
                            'reference_no' => $payment['reference_no'],
                        ]);
                    }

                    foreach ($items as $item) {
                        $stockItem = StockItem::firstOrCreate([
                            'location_id' => $locationId,
                            'product_id' => $item['product_id'],
                        ], [
                            'quantity_on_hand' => 0,
                        ]);

                        $stockItem->adjustQuantity(-1 * $item['quantity']);
                    }

                    AuditLog::create([
                        'user_id' => $request->user()?->id,
                        'location_id' => $locationId,
                        'action' => 'sale_posted',
                        'metadata' => [
                            'sale_id' => $sale->id,
                            'reference_no' => $sale->reference_no,
                            'total' => $sale->total,
                        ],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'occurred_at' => now(),
                    ]);

                    app(AccountingService::class)->recordSale($sale, $paymentLines, $cogsTotal);
                }
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('sales.create')
                ->withErrors($exception->errors())
                ->withInput();
        }

        if ($data['action'] === 'post' && $sale) {
            return redirect()
                ->route('sales.receipt', $sale)
                ->with('status', $draftSale ? 'Draft berhasil diposting.' : 'Penjualan berhasil diposting.');
        }

        return redirect()
            ->route('sales.index')
            ->with('status', $draftSale ? 'Draft penjualan berhasil diperbarui.' : 'Draft penjualan berhasil disimpan.');
    }

    public function void(Sale $sale, VoidSaleRequest $request): RedirectResponse
    {
        if ($sale->status !== 'posted' || $sale->type !== 'sale') {
            return redirect()
                ->route('sales.index')
                ->withErrors(['status' => 'Penjualan tidak dapat di-void.']);
        }

        DB::transaction(function () use ($sale, $request): void {
            $sale->load(['items', 'payments']);

            foreach ($sale->items as $item) {
                $stockItem = StockItem::firstOrCreate([
                    'location_id' => $sale->location_id,
                    'product_id' => $item->product_id,
                ], [
                    'quantity_on_hand' => 0,
                ]);

                $stockItem->adjustQuantity((float) $item->quantity);
            }

            $sale->update([
                'status' => 'voided',
                'voided_by' => $request->user()?->id,
                'voided_at' => now(),
                'void_reason' => $request->validated()['void_reason'],
            ]);

            AuditLog::create([
                'user_id' => $request->user()?->id,
                'location_id' => $sale->location_id,
                'action' => 'sale_voided',
                'metadata' => [
                    'sale_id' => $sale->id,
                    'reference_no' => $sale->reference_no,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'occurred_at' => now(),
            ]);

            $paymentLines = $this->preparePaymentLines(
                $sale->payments->map(fn (SalePayment $payment): array => [
                    'method' => $payment->method,
                    'amount' => (float) $payment->amount,
                ])->all(),
                (float) $sale->change_due,
                (float) $sale->receivable_balance
            );

            $cogsTotal = $this->calculateCogsTotalFromItems($sale->items->all(), $sale->location_id);

            app(AccountingService::class)->recordSaleReversal(
                $sale,
                $paymentLines,
                $cogsTotal,
                'Void Penjualan'
            );
        });

        return redirect()
            ->route('sales.index')
            ->with('status', 'Penjualan berhasil di-void.');
    }

    public function return(Sale $sale, ReturnSaleRequest $request): RedirectResponse
    {
        if ($sale->status !== 'posted' || $sale->type !== 'sale') {
            return redirect()
                ->route('sales.index')
                ->withErrors(['status' => 'Penjualan tidak dapat diretur.']);
        }

        if (Sale::query()->where('original_sale_id', $sale->id)->exists()) {
            return redirect()
                ->route('sales.index')
                ->withErrors(['status' => 'Penjualan sudah diretur.']);
        }

        $postedAt = $sale->posted_at ?? $sale->created_at;
        if ($postedAt && now()->diffInDays($postedAt) > 30) {
            return redirect()
                ->route('sales.index')
                ->withErrors(['status' => 'Masa retur sudah lewat.']);
        }

        DB::transaction(function () use ($sale, $request): void {
            $sale->load('items');

            $returnSale = Sale::create([
                'location_id' => $sale->location_id,
                'cashier_id' => $request->user()?->id,
                'original_sale_id' => $sale->id,
                'reference_no' => $this->generateReferenceNo(),
                'type' => 'return',
                'status' => 'posted',
                'subtotal' => -1 * (float) $sale->subtotal,
                'order_discount' => -1 * (float) $sale->order_discount,
                'tax_amount' => -1 * (float) $sale->tax_amount,
                'is_tax_inclusive' => $sale->is_tax_inclusive,
                'total' => -1 * (float) $sale->total,
                'paid_total' => 0,
                'change_due' => 0,
                'posted_at' => now(),
                'returned_by' => $request->user()?->id,
                'returned_at' => now(),
                'return_reason' => $request->validated()['return_reason'],
            ]);

            foreach ($sale->items as $item) {
                SaleItem::create([
                    'sale_id' => $returnSale->id,
                    'location_id' => $sale->location_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_discount' => $item->line_discount,
                    'line_total' => -1 * (float) $item->line_total,
                ]);

                $stockItem = StockItem::firstOrCreate([
                    'location_id' => $sale->location_id,
                    'product_id' => $item->product_id,
                ], [
                    'quantity_on_hand' => 0,
                ]);

                $stockItem->adjustQuantity((float) $item->quantity);
            }

            $sale->update([
                'status' => 'returned',
                'returned_by' => $request->user()?->id,
                'returned_at' => now(),
                'return_reason' => $request->validated()['return_reason'],
            ]);

            AuditLog::create([
                'user_id' => $request->user()?->id,
                'location_id' => $sale->location_id,
                'action' => 'sale_returned',
                'metadata' => [
                    'sale_id' => $sale->id,
                    'return_sale_id' => $returnSale->id,
                    'reference_no' => $sale->reference_no,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'occurred_at' => now(),
            ]);

            $paymentLines = [
                [
                    'method' => 'cash',
                    'amount' => abs((float) $returnSale->total),
                ],
            ];

            $cogsTotal = $this->calculateCogsTotalFromItems($sale->items->all(), $sale->location_id);

            app(AccountingService::class)->recordSaleReversal(
                $returnSale,
                $paymentLines,
                $cogsTotal,
                'Retur Penjualan'
            );
        });

        return redirect()
            ->route('sales.index')
            ->with('status', 'Retur penjualan berhasil diproses.');
    }

    public function receipt(Sale $sale): View|RedirectResponse
    {
        if ($sale->status === 'draft') {
            return redirect()
                ->route('sales.index')
                ->withErrors(['status' => 'Struk hanya tersedia untuk transaksi yang sudah diposting.']);
        }

        $sale->load(['items.product', 'payments', 'location', 'cashier']);

        return view('sales.receipt', compact('sale'));
    }

    private function normalizeItems(array $items): array
    {
        $locationId = ActiveLocation::id();

        return collect($items)
            ->filter(fn ($item): bool => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0)
            ->map(function ($item) use ($locationId): array {
                $productId = (int) $item['product_id'];
                $quantity = (float) $item['quantity'];
                $unitPrice = $this->resolveUnitPrice($productId, $locationId, $item['unit_price'] ?? null);
                $lineDiscount = (float) ($item['line_discount'] ?? 0);
                $lineTotal = ($quantity * $unitPrice) - $lineDiscount;

                if ($lineTotal < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Diskon baris tidak boleh melebihi subtotal baris.',
                    ]);
                }

                return [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_discount' => $lineDiscount,
                    'line_total' => $lineTotal,
                ];
            })
            ->values()
            ->all();
    }

    private function normalizePayments(array $payments): array
    {
        return collect($payments)
            ->filter(fn ($payment): bool => ! empty($payment['method']) && (float) ($payment['amount'] ?? 0) > 0)
            ->map(fn ($payment): array => [
                'method' => (string) $payment['method'],
                'amount' => (float) $payment['amount'],
                'reference_no' => $payment['reference_no'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function resolveUnitPrice(int $productId, ?int $locationId, ?string $inputPrice): float
    {
        if ($inputPrice !== null && $inputPrice !== '') {
            return (float) $inputPrice;
        }

        if ($locationId) {
            $override = ProductPrice::query()
                ->where('location_id', $locationId)
                ->where('product_id', $productId)
                ->value('sale_price');

            if ($override !== null) {
                return (float) $override;
            }
        }

        return (float) Product::query()->whereKey($productId)->value('sale_price');
    }

    private function ensureStockAvailable(array $items, int $locationId): void
    {
        foreach ($items as $item) {
            $product = Product::query()->whereKey($item['product_id'])->first();
            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'Produk tidak ditemukan.',
                ]);
            }

            $stockItem = StockItem::query()
                ->where('location_id', $locationId)
                ->where('product_id', $item['product_id'])
                ->first();

            $available = $stockItem?->quantity_on_hand ?? 0;
            if ((float) $available < (float) $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => 'Stok tidak cukup untuk produk '.$product->name.'.',
                ]);
            }
        }
    }

    private function preparePaymentLines(array $payments, float $changeDue, float $receivableBalance): array
    {
        $lines = [];
        $remainingChange = $changeDue;

        foreach ($payments as $payment) {
            if (($payment['method'] ?? '') === 'piutang') {
                continue;
            }

            $amount = (float) ($payment['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            if (($payment['method'] ?? '') === 'cash' && $remainingChange > 0) {
                $amount = max(0, $amount - $remainingChange);
                $remainingChange = max(0, $remainingChange - (float) $payment['amount']);
            }

            if ($amount <= 0) {
                continue;
            }

            $lines[] = [
                'method' => $payment['method'] ?? 'cash',
                'amount' => $amount,
            ];
        }

        if ($receivableBalance > 0) {
            $lines[] = [
                'method' => 'piutang',
                'amount' => $receivableBalance,
            ];
        }

        return $lines;
    }

    private function calculateCogsTotal(array $items, int $locationId): float
    {
        return collect($items)
            ->sum(fn (array $item): float => (float) $item['quantity'] * $this->resolveCost($item['product_id'], $locationId));
    }

    private function calculateCogsTotalFromItems(array $items, int $locationId): float
    {
        return collect($items)
            ->sum(fn (SaleItem $item): float => (float) $item->quantity * $this->resolveCost($item->product_id, $locationId));
    }

    private function resolveDraft(?int $draftId): ?Sale
    {
        if (! $draftId) {
            return null;
        }

        $sale = Sale::query()
            ->whereKey($draftId)
            ->where('status', 'draft')
            ->where('type', 'sale')
            ->with('items')
            ->first();

        if (! $sale) {
            return null;
        }

        $locationId = ActiveLocation::id();
        if (! $locationId || (int) $sale->location_id !== (int) $locationId) {
            return null;
        }

        return $sale;
    }

    private function renderCreateView(?Sale $draft): View
    {
        $locationId = ActiveLocation::id();

        $products = Product::query()
            ->where('is_active', true)
            ->with(['unit'])
            ->orderBy('name')
            ->get();

        $priceOverrides = $locationId
            ? ProductPrice::query()
                ->where('location_id', $locationId)
                ->pluck('sale_price', 'product_id')
            : collect();

        $stockLevels = $locationId
            ? StockItem::query()
                ->where('location_id', $locationId)
                ->pluck('quantity_on_hand', 'product_id')
            : collect();

        $productsForJs = $products->map(fn (Product $product): array => [
            'id' => $product->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'unit' => $product->unit?->name,
            'price' => (float) ($priceOverrides[$product->id] ?? $product->sale_price),
            'stock' => (float) ($stockLevels[$product->id] ?? 0),
            'block_when_out_of_stock' => (bool) $product->block_when_out_of_stock,
        ]);

        $location = $locationId ? Location::query()->find($locationId) : null;

        $referenceNo = $draft?->reference_no ?? $this->generateReferenceNo();
        $draftItems = $draft
            ? $draft->items->map(fn (SaleItem $item): array => [
                'product_id' => $item->product_id,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_discount' => (float) $item->line_discount,
            ])->all()
            : [];
        $draftTaxRate = $draft ? $this->calculateDraftTaxRate($draft) : 0;
        $drafts = Sale::query()
            ->where('status', 'draft')
            ->where('type', 'sale')
            ->where('location_id', $locationId)
            ->with(['items.product', 'cashier'])
            ->latest()
            ->get();

        return view('sales.create', compact(
            'products',
            'referenceNo',
            'productsForJs',
            'location',
            'draft',
            'draftItems',
            'draftTaxRate',
            'drafts'
        ));
    }

    private function calculateDraftTaxRate(Sale $sale): float
    {
        $baseTotal = max(0, (float) $sale->subtotal - (float) $sale->order_discount);
        if ($baseTotal <= 0 || (float) $sale->tax_amount <= 0) {
            return 0;
        }

        $taxAmount = (float) $sale->tax_amount;
        if ($sale->is_tax_inclusive) {
            $denominator = max(0.01, $baseTotal - $taxAmount);
            return round(($taxAmount / $denominator) * 100, 2);
        }

        return round(($taxAmount / $baseTotal) * 100, 2);
    }

    private function resolveCost(int $productId, int $locationId): float
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
        return 'POS-'.now()->format('Ymd-His').'-'.random_int(100, 999);
    }
}
