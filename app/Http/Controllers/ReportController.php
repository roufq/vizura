<?php

namespace App\Http\Controllers;

use App\Http\Requests\CashUpReportRequest;
use App\Http\Requests\CashFlowReportRequest;
use App\Http\Requests\IncomeStatementReportRequest;
use App\Http\Requests\SalesReportRequest;
use App\Http\Requests\StockCardReportRequest;
use App\Http\Requests\StockReportRequest;
use App\Models\Account;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockAdjustment;
use App\Models\StockItem;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko,web');
    }

    public function sales(SalesReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

        $salesQuery = $canViewAll
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        if ($locationId) {
            $salesQuery->where('location_id', $locationId);
        }

        if (! empty($data['start_date'])) {
            $salesQuery->whereDate('created_at', '>=', $data['start_date']);
        }

        if (! empty($data['end_date'])) {
            $salesQuery->whereDate('created_at', '<=', $data['end_date']);
        }

        if (! empty($data['cashier_id'])) {
            $salesQuery->where('cashier_id', $data['cashier_id']);
        }

        if (! empty($data['status'])) {
            $salesQuery->where('status', $data['status']);
        }

        if (! empty($data['type'])) {
            $salesQuery->where('type', $data['type']);
        }

        if (! empty($data['method'])) {
            $salesQuery->whereHas('payments', function ($query) use ($data): void {
                $query->where('method', $data['method']);
            });
        }

        $summary = (clone $salesQuery)
            ->selectRaw('COALESCE(SUM(subtotal), 0) as gross_total')
            ->selectRaw('COALESCE(SUM(order_discount), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(total), 0) as net_total')
            ->first();

        $sales = $salesQuery
            ->with(['cashier', 'payments'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();
        $cashiers = User::role('Kasir')->orderBy('name')->get();
        $paymentMethods = $this->getPaymentMethods($canViewAll, $locationId);

        return view('reports.sales', [
            'sales' => $sales,
            'summary' => $summary,
            'locations' => $locations,
            'cashiers' => $cashiers,
            'paymentMethods' => $paymentMethods,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function cashUp(CashUpReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

        $paymentsQuery = $canViewAll
            ? SalePayment::withoutGlobalScope('active_location')
            : SalePayment::query();

        $paymentsQuery
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.status', 'posted')
            ->where('sales.type', 'sale');

        if ($locationId) {
            $paymentsQuery->where('sales.location_id', $locationId);
        }

        if (! empty($data['start_date'])) {
            $paymentsQuery->whereDate('sales.created_at', '>=', $data['start_date']);
        }

        if (! empty($data['end_date'])) {
            $paymentsQuery->whereDate('sales.created_at', '<=', $data['end_date']);
        }

        $rows = $paymentsQuery
            ->selectRaw('DATE(sales.created_at) as sale_date, sale_payments.method, SUM(sale_payments.amount) as total')
            ->groupBy('sale_date', 'sale_payments.method')
            ->orderByDesc('sale_date')
            ->get();

        $totalsByMethod = $rows
            ->groupBy('method')
            ->map(fn (Collection $group): float => (float) $group->sum('total'));

        $grandTotal = (float) $rows->sum('total');

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        return view('reports.cash-up', [
            'rows' => $rows,
            'totalsByMethod' => $totalsByMethod,
            'grandTotal' => $grandTotal,
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
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

        $stockQuery = $canViewAll
            ? StockItem::withoutGlobalScope('active_location')
            : StockItem::query();

        if ($locationId) {
            $stockQuery->where('location_id', $locationId);
        }

        if (! empty($data['search'])) {
            $stockQuery->whereHas('product', function ($query) use ($data): void {
                $query->where('name', 'like', '%'.$data['search'].'%')
                    ->orWhere('sku', 'like', '%'.$data['search'].'%');
            });
        }

        $totalValue = (clone $stockQuery)
            ->join('products', 'products.id', '=', 'stock_items.product_id')
            ->sum(DB::raw('stock_items.quantity_on_hand * products.cost_price'));

        $stockItems = $stockQuery
            ->with(['product', 'location'])
            ->orderBy('product_id')
            ->paginate(20)
            ->withQueryString();

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        return view('reports.stock', [
            'stockItems' => $stockItems,
            'totalValue' => (float) $totalValue,
            'locations' => $locations,
            'filters' => $data,
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function stockCard(StockCardReportRequest $request): View
    {
        $data = $request->validated();
        $user = $request->user();
        $canViewAll = $this->canViewAllLocations($user);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

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

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

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
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

        $journalQuery = $this->buildJournalLineQuery($canViewAll, $locationId, $data['start_date'] ?? null, $data['end_date'] ?? null);

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

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

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
        $locationId = $this->resolveLocationId($data, $allowedLocationIds, $canViewAll);

        $journalQuery = $this->buildJournalLineQuery($canViewAll, $locationId, $data['start_date'] ?? null, $data['end_date'] ?? null);

        $cashAccounts = Account::query()
            ->where('is_cash', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cashMovements = $cashAccounts->map(function (Account $account) use ($journalQuery): array {
            $total = (clone $journalQuery)
                ->where('journal_lines.account_id', $account->id)
                ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

            return [
                'account' => $account,
                'total' => (float) $total,
            ];
        });

        $netChange = (float) $cashMovements->sum('total');

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        return view('reports.cash-flow', [
            'cashMovements' => $cashMovements,
            'netChange' => $netChange,
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

    private function resolveLocationId(array $data, array $allowedLocationIds, bool $canViewAll): ?int
    {
        if ($canViewAll && ($data['all_locations'] ?? false)) {
            return null;
        }

        if (! empty($data['location_id'])) {
            $requested = (int) $data['location_id'];
            if ($allowedLocationIds === [] || in_array($requested, $allowedLocationIds, true)) {
                return $requested;
            }
        }

        $activeLocationId = ActiveLocation::id();
        if ($activeLocationId && ($allowedLocationIds === [] || in_array($activeLocationId, $allowedLocationIds, true))) {
            return $activeLocationId;
        }

        return $allowedLocationIds[0] ?? null;
    }

    private function getPaymentMethods(bool $canViewAll, ?int $locationId): Collection
    {
        $query = $canViewAll
            ? SalePayment::withoutGlobalScope('active_location')
            : SalePayment::query();

        $query->join('sales', 'sales.id', '=', 'sale_payments.sale_id');

        if ($locationId) {
            $query->where('sales.location_id', $locationId);
        }

        return $query
            ->select('sale_payments.method')
            ->distinct()
            ->orderBy('sale_payments.method')
            ->pluck('method');
    }

    private function buildJournalLineQuery(bool $canViewAll, ?int $locationId, ?string $startDate, ?string $endDate)
    {
        $query = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id');

        if (! $canViewAll && ! $locationId) {
            $query->where('journals.location_id', ActiveLocation::id());
        }

        if ($locationId) {
            $query->where('journals.location_id', $locationId);
        }

        if ($startDate) {
            $query->whereDate('journals.posted_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('journals.posted_at', '<=', $endDate);
        }

        return $query;
    }
}
