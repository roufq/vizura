<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashFlowReportRequest;
use App\Http\Requests\CashUpReportRequest;
use App\Http\Requests\IncomeStatementReportRequest;
use App\Http\Requests\SalesReportRequest;
use App\Http\Requests\StockCardReportRequest;
use App\Http\Requests\StockReportRequest;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Support\AccountingService;
use App\Support\LocationResolver;
use App\Support\ReportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        public ReportService $reportService,
        public LocationResolver $locationResolver
    ) {
        $this->middleware('role:Owner|Manager|KepalaToko,web');
    }

    public function sales(SalesReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildSalesReport($data, $canViewAll, $locationId);
        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);
        $cashiers = $this->reportService->cashiers();

        return view('reports.sales', [
            'sales' => $reportData['sales'],
            'summary' => $reportData['summary'],
            'locations' => $locations,
            'cashiers' => $cashiers,
            'paymentMethods' => $reportData['paymentMethods'],
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function exportSales(SalesReportRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildSalesReport($data, $canViewAll, $locationId, false);
        $sales = $reportData['sales'];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pos_sales_report_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal', 'Referensi', 'Tipe', 'Status', 'Kasir', 'Pelanggan', 'Subtotal', 'Diskon', 'Pajak', 'Total']);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->posted_at?->format('Y-m-d H:i') ?? $sale->created_at->format('Y-m-d H:i'),
                    $sale->reference_no,
                    strtoupper($sale->type),
                    strtoupper($sale->status),
                    $sale->cashier?->name,
                    $sale->customer_name ?: 'Umum',
                    (float) $sale->subtotal,
                    (float) $sale->order_discount,
                    (float) $sale->tax_amount,
                    (float) $sale->total,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function cashUp(CashUpReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildCashUpReport($data, $canViewAll, $locationId);
        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);

        return view('reports.cash-up', [
            'rows' => $reportData['rows'],
            'totalsByMethod' => $reportData['totalsByMethod'],
            'grandTotal' => $reportData['grandTotal'],
            'locations' => $locations,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function stock(StockReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildStockReport($data, $canViewAll, $locationId);
        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);

        return view('reports.stock', [
            'stockItems' => $reportData['stockItems'],
            'totalValue' => $reportData['totalValue'],
            'locations' => $locations,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function exportStock(StockReportRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildStockReport($data, $canViewAll, $locationId, false);
        $items = $reportData['stockItems'];

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pos_stock_report_'.now()->format('YmdHis').'.csv"',
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Lokasi', 'SKU', 'Produk', 'Stok Saat Ini', 'Satuan']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->location?->name,
                    $item->product?->sku,
                    $item->product?->name,
                    (float) $item->quantity_on_hand,
                    $item->product?->unit?->name,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stockCard(StockCardReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $products = Product::query()->orderBy('name')->get();
        $productId = $data['product_id'] ?? $products->first()?->id;
        $product = $productId ? Product::query()->find($productId) : null;
        $entries = collect();

        if ($product) {
            $entries = $this->buildStockCardEntries(
                $product->id,
                $locationId,
                $canViewAll,
                $data['start_date'] ?? null,
                $data['end_date'] ?? null
            );
        }

        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);

        return view('reports.stock-card', [
            'entries' => $entries,
            'product' => $product,
            'locations' => $locations,
            'products' => $products,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function incomeStatement(IncomeStatementReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $journalQuery = $this->reportService->buildJournalLineQuery($canViewAll, $locationId, $data['start_date'] ?? null, $data['end_date'] ?? null);

        $incomeTotal = (clone $journalQuery)
            ->join('accounts as income_accounts', 'income_accounts.id', '=', 'journal_lines.account_id')
            ->where('income_accounts.type', 'income')
            ->sum(DB::raw('journal_lines.credit - journal_lines.debit'));

        $cogsTotal = (clone $journalQuery)
            ->join('accounts as cogs_accounts', 'cogs_accounts.id', '=', 'journal_lines.account_id')
            ->where('cogs_accounts.code', AccountingService::ACCOUNT_COGS)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        $expenseTotal = (clone $journalQuery)
            ->join('accounts as expense_accounts', 'expense_accounts.id', '=', 'journal_lines.account_id')
            ->where('expense_accounts.type', 'expense')
            ->where('expense_accounts.code', '!=', AccountingService::ACCOUNT_COGS)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        $grossProfit = (float) $incomeTotal - (float) $cogsTotal;
        $netProfit = $grossProfit - (float) $expenseTotal;

        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);

        return view('reports.income-statement', [
            'incomeTotal' => (float) $incomeTotal,
            'cogsTotal' => (float) $cogsTotal,
            'expenseTotal' => (float) $expenseTotal,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'locations' => $locations,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function cashFlow(CashFlowReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->locationResolver->resolveFromFilters($data, $allowedLocationIds, $canViewAll);

        $reportData = $this->reportService->buildCashFlowReport($data, $canViewAll, $locationId);
        $locations = $this->reportService->locations($canViewAll, $allowedLocationIds);

        return view('reports.cash-flow', [
            'cashMovements' => $reportData['cashMovements'],
            'netChange' => $reportData['netChange'],
            'locations' => $locations,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    private function buildStockCardEntries(int $productId, ?int $locationId, bool $canViewAll, ?string $startDate, ?string $endDate): Collection
    {
        $entries = collect();

        $purchaseItems = PurchaseItem::query()
            ->with('purchase.supplier')
            ->where('product_id', $productId)
            ->whereHas('purchase', function ($query) use ($locationId, $startDate, $endDate): void {
                $query->where('status', 'received');

                if ($locationId) {
                    $query->where('location_id', $locationId);
                }

                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                }

                if ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            })
            ->get();

        foreach ($purchaseItems as $item) {
            $purchase = $item->purchase;
            if (! $purchase) {
                continue;
            }

            $entries->push([
                'date' => $purchase->received_at ?? $purchase->created_at,
                'type' => 'purchase',
                'reference' => $purchase->reference_no,
                'note' => $purchase->supplier?->name,
                'qty_in' => (float) $item->quantity,
                'qty_out' => 0.0,
            ]);
        }

        $adjustmentsQuery = $canViewAll
            ? StockAdjustment::withoutGlobalScope('active_location')
            : StockAdjustment::query();

        $adjustmentsQuery
            ->where('product_id', $productId)
            ->where('status', 'approved');

        if ($locationId) {
            $adjustmentsQuery->where('location_id', $locationId);
        }

        if ($startDate) {
            $adjustmentsQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $adjustmentsQuery->whereDate('created_at', '<=', $endDate);
        }

        foreach ($adjustmentsQuery->get() as $adjustment) {
            $delta = (float) $adjustment->quantity_delta;

            $entries->push([
                'date' => $adjustment->approved_at ?? $adjustment->created_at,
                'type' => 'adjustment',
                'reference' => 'ADJ-'.$adjustment->id,
                'note' => $adjustment->reason,
                'qty_in' => $delta > 0 ? $delta : 0.0,
                'qty_out' => $delta < 0 ? abs($delta) : 0.0,
            ]);
        }

        $transferItemsQuery = StockTransferItem::query()
            ->with(['stockTransfer.destinationLocation', 'stockTransfer.sourceLocation'])
            ->where('product_id', $productId)
            ->whereHas('stockTransfer', function ($query) use ($locationId, $startDate, $endDate): void {
                $query->whereIn('status', ['sent', 'received']);

                if ($locationId) {
                    $query->where(function ($sub) use ($locationId): void {
                        $sub->where('source_location_id', $locationId)
                            ->orWhere('destination_location_id', $locationId);
                    });
                }

                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                }

                if ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            });

        foreach ($transferItemsQuery->get() as $item) {
            $transfer = $item->stockTransfer;
            if (! $transfer) {
                continue;
            }

            if ($locationId && $transfer->source_location_id === $locationId && $transfer->sent_at) {
                $entries->push([
                    'date' => $transfer->sent_at,
                    'type' => 'transfer_out',
                    'reference' => $transfer->reference_no,
                    'note' => 'Kirim ke '.$transfer->destinationLocation?->name,
                    'qty_in' => 0.0,
                    'qty_out' => (float) $item->quantity,
                ]);
            }

            if ($locationId && $transfer->destination_location_id === $locationId && $transfer->received_at) {
                $entries->push([
                    'date' => $transfer->received_at,
                    'type' => 'transfer_in',
                    'reference' => $transfer->reference_no,
                    'note' => 'Terima dari '.$transfer->sourceLocation?->name,
                    'qty_in' => (float) $item->quantity,
                    'qty_out' => 0.0,
                ]);
            }
        }

        $saleItemsQuery = $canViewAll
            ? SaleItem::withoutGlobalScope('active_location')
            : SaleItem::query();

        $saleItemsQuery->where('product_id', $productId)
            ->whereHas('sale', function ($query) use ($locationId, $startDate, $endDate, $canViewAll): void {
                if ($canViewAll) {
                    $query->withoutGlobalScope('active_location');
                }

                $query->whereIn('status', ['posted', 'voided', 'returned'])
                    ->whereIn('type', ['sale', 'return']);

                if ($locationId) {
                    $query->where('location_id', $locationId);
                }

                if ($startDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                }

                if ($endDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
            })
            ->with(['sale' => function ($query) use ($canViewAll): void {
                if ($canViewAll) {
                    $query->withoutGlobalScope('active_location');
                }
            }])
            ->get()
            ->each(function (SaleItem $item) use ($entries): void {
                $sale = $item->sale;
                if (! $sale) {
                    return;
                }

                $postedAt = $sale->posted_at ?? $sale->created_at;

                if ($sale->type === 'sale') {
                    $entries->push([
                        'date' => $postedAt,
                        'type' => 'sale_out',
                        'reference' => $sale->reference_no,
                        'note' => $sale->status,
                        'qty_in' => 0.0,
                        'qty_out' => (float) $item->quantity,
                    ]);

                    if ($sale->status === 'voided' && $sale->voided_at) {
                        $entries->push([
                            'date' => $sale->voided_at,
                            'type' => 'sale_void',
                            'reference' => $sale->reference_no,
                            'note' => $sale->void_reason,
                            'qty_in' => (float) $item->quantity,
                            'qty_out' => 0.0,
                        ]);
                    }

                    return;
                }

                if ($sale->type === 'return') {
                    $entries->push([
                        'date' => $postedAt,
                        'type' => 'sale_return',
                        'reference' => $sale->reference_no,
                        'note' => $sale->return_reason,
                        'qty_in' => (float) $item->quantity,
                        'qty_out' => 0.0,
                    ]);
                }
            });

        $entries = $entries
            ->sortBy('date')
            ->values();

        $running = 0.0;

        return $entries->map(function (array $entry) use (&$running): array {
            $running += $entry['qty_in'] - $entry['qty_out'];
            $entry['balance'] = $running;

            return $entry;
        });
    }

    private function canViewAllLocations(?User $user): bool
    {
        return $user?->hasRole('Owner') ?? false;
    }
}
