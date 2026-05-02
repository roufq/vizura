<?php

namespace App\Support;

use App\Models\Location;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockItem;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(?User $user, ?int $locationId, array $allowedLocationIds, bool $canViewAll, array $filters): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $isOwner = (bool) ($user?->hasRole('Owner') || $user?->hasRole('Super Admin'));
        $isManager = $user?->hasRole('Manager') ?? false;
        $isHeadStore = $user?->hasRole('HeadStore') ?? false;
        $isCashier = $user?->hasRole('Cashier') ?? false;
        $canCrossLocation = $isOwner;

        $salesQuery = $this->buildSalesQuery($canCrossLocation, $allowedLocationIds, $locationId, $canViewAll);
        $salesToday = (clone $salesQuery)
            ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), $today)
            ->sum('total');

        $salesMonth = (clone $salesQuery)
            ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), '>=', $monthStart)
            ->sum('total');

        $transactionsToday = (clone $salesQuery)
            ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), $today)
            ->count();

        $cashierSalesToday = null;
        $cashierTransactionsToday = null;
        if ($isCashier && $user) {
            $cashierQuery = (clone $salesQuery)->where('cashier_id', $user->id);
            $cashierSalesToday = (clone $cashierQuery)
                ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), $today)
                ->sum('total');
            $cashierTransactionsToday = (clone $cashierQuery)
                ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), $today)
                ->count();
        }

        $activeProducts = Product::query()->where('is_active', true)->count();

        $lowStocks = $this->buildLowStocks($canCrossLocation, $allowedLocationIds, $locationId, $canViewAll);

        $topProducts = $this->buildTopProducts($canCrossLocation, $allowedLocationIds, $locationId);

        $recentAdjustments = $this->buildRecentAdjustments($canCrossLocation, $allowedLocationIds, $locationId, $canViewAll);

        $salesSeries = $this->buildSalesSeries($canCrossLocation, $allowedLocationIds, $locationId, $canViewAll);

        $labels = [];
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');
            $series[] = (float) ($salesSeries[$date]->total ?? 0);
        }

        $locationName = $this->resolveLocationName($locationId, $canViewAll);

        $receivableTotal = null;
        $payableTotal = null;
        $stockValueTotal = null;
        $grossProfitMonth = null;

        if ($isOwner || $isManager) {
            $receivableTotal = $this->calculateReceivableTotal($canCrossLocation, $allowedLocationIds, $locationId);
            $payableTotal = $this->calculatePayableTotal($canCrossLocation, $allowedLocationIds, $locationId);
        }

        if ($isOwner) {
            $stockValueTotal = $this->calculateStockValue($canCrossLocation, $allowedLocationIds, $locationId);
            $grossProfitMonth = $this->calculateGrossProfit($monthStart, $today, $locationId, $canViewAll, $allowedLocationIds);
        }

        $pendingTransfers = null;
        $pendingAdjustments = null;
        $pendingTransfersIn = null;
        $pendingTransfersOut = null;

        if ($isManager) {
            $pendingTransfers = $this->countPendingTransfers($locationId, $allowedLocationIds, $canViewAll);
            $pendingAdjustments = $this->countPendingAdjustments($locationId, $allowedLocationIds, $canViewAll);
        }

        if ($isHeadStore) {
            $pendingTransfersIn = $this->countPendingTransfersByDirection($locationId, 'in');
            $pendingTransfersOut = $this->countPendingTransfersByDirection($locationId, 'out');
        }

        $draftSales = [];
        if ($isCashier && $user) {
            $draftSales = Sale::query()
                ->where('status', 'draft')
                ->where('type', 'sale')
                ->where('cashier_id', $user->id)
                ->when($locationId, function ($query) use ($locationId): void {
                    $query->where('location_id', $locationId);
                })
                ->latest()
                ->limit(5)
                ->get();
        }

        $locations = collect();
        $canSelectLocations = false;
        if ($isOwner) {
            $locations = Location::active()->orderBy('name')->get();
            $canSelectLocations = true;
        }

        return compact(
            'isOwner',
            'isManager',
            'isHeadStore',
            'isCashier',
            'locationId',
            'locationName',
            'filters',
            'locations',
            'canViewAll',
            'canSelectLocations',
            'salesToday',
            'salesMonth',
            'transactionsToday',
            'cashierSalesToday',
            'cashierTransactionsToday',
            'activeProducts',
            'lowStocks',
            'topProducts',
            'recentAdjustments',
            'labels',
            'series',
            'receivableTotal',
            'payableTotal',
            'stockValueTotal',
            'grossProfitMonth',
            'pendingTransfers',
            'pendingAdjustments',
            'pendingTransfersIn',
            'pendingTransfersOut',
            'draftSales'
        );
    }

    private function buildSalesQuery(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId, bool $canViewAll)
    {
        $query = $canCrossLocation
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        $query->where('status', 'posted')
            ->where('type', 'sale');

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return $query;
    }

    private function buildLowStocks(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId, bool $canViewAll)
    {
        $query = $canCrossLocation
            ? StockItem::withoutGlobalScope('active_location')
            : StockItem::query();

        if ($locationId) {
            $query->where('stock_items.location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->whereIn('stock_items.location_id', $allowedLocationIds);
        }

        return $query
            ->with('product')
            ->where('quantity_on_hand', '<=', 5)
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get();
    }

    private function buildTopProducts(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId)
    {
        $query = $canCrossLocation
            ? SaleItem::withoutGlobalScope('active_location')
            : SaleItem::query();

        $query->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'posted')
            ->where('sales.type', 'sale');

        if ($locationId) {
            $query->where('sales.location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif ($allowedLocationIds !== []) {
            $query->whereIn('sales.location_id', $allowedLocationIds);
        }

        return $query
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_qty'), DB::raw('SUM(sale_items.line_total) as total_sales'))
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(5)
            ->get();
    }

    private function buildRecentAdjustments(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId, bool $canViewAll)
    {
        $query = $canCrossLocation
            ? StockAdjustment::withoutGlobalScope('active_location')
            : StockAdjustment::query();

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return $query
            ->with(['product', 'requester'])
            ->latest()
            ->limit(5)
            ->get();
    }

    private function buildSalesSeries(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId, bool $canViewAll)
    {
        $query = $canCrossLocation
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        $query->where('status', 'posted')
            ->where('type', 'sale')
            ->whereDate(DB::raw('COALESCE(posted_at, created_at)'), '>=', now()->subDays(6)->toDateString());

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return $query
            ->selectRaw('DATE(COALESCE(posted_at, created_at)) as sale_date, SUM(total) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');
    }

    private function calculateReceivableTotal(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId): float
    {
        $query = $canCrossLocation
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        $query->where('status', 'posted')
            ->where('type', 'sale')
            ->where('payment_status', '!=', 'paid');

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif ($allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return (float) $query->sum('receivable_balance');
    }

    private function calculatePayableTotal(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId): float
    {
        $query = $canCrossLocation
            ? Purchase::withoutGlobalScope('active_location')
            : Purchase::query();

        $query->where('payment_method', 'payable')
            ->where('payment_status', '!=', 'paid');

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif ($allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return (float) $query->sum('payable_balance');
    }

    private function calculateStockValue(bool $canCrossLocation, array $allowedLocationIds, ?int $locationId): float
    {
        $query = $canCrossLocation
            ? StockItem::withoutGlobalScope('active_location')
            : StockItem::query();

        if ($locationId) {
            $query->where('stock_items.location_id', $locationId);
        } elseif (! $canCrossLocation) {
            $query->whereRaw('1 = 0');
        } elseif ($allowedLocationIds !== []) {
            $query->whereIn('stock_items.location_id', $allowedLocationIds);
        }

        return (float) $query
            ->join('products', 'products.id', '=', 'stock_items.product_id')
            ->leftJoin('product_prices', function ($join): void {
                $join->on('product_prices.product_id', '=', 'stock_items.product_id')
                    ->on('product_prices.location_id', '=', 'stock_items.location_id');
            })
            ->sum(DB::raw('stock_items.quantity_on_hand * COALESCE(product_prices.cost_price, products.cost_price)'));
    }

    private function calculateGrossProfit(string $startDate, string $endDate, ?int $locationId, bool $canViewAll, array $allowedLocationIds): float
    {
        if (! Schema::hasTable('journal_lines')) {
            return 0.0;
        }

        $journalQuery = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id');

        if ($locationId) {
            $journalQuery->where('journals.location_id', $locationId);
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $journalQuery->whereIn('journals.location_id', $allowedLocationIds);
        }

        $journalQuery->whereDate('journals.posted_at', '>=', $startDate)
            ->whereDate('journals.posted_at', '<=', $endDate);

        $revenueTotal = (clone $journalQuery)
            ->where('accounts.code', AccountingService::ACCOUNT_REVENUE)
            ->sum(DB::raw('journal_lines.credit - journal_lines.debit'));

        $cogsTotal = (clone $journalQuery)
            ->where('accounts.code', AccountingService::ACCOUNT_COGS)
            ->sum(DB::raw('journal_lines.debit - journal_lines.credit'));

        return (float) $revenueTotal - (float) $cogsTotal;
    }

    private function countPendingTransfers(?int $locationId, array $allowedLocationIds, bool $canViewAll): int
    {
        $query = StockTransfer::query()
            ->whereIn('status', ['draft', 'sent']);

        if ($locationId) {
            $query->where(function ($sub) use ($locationId): void {
                $sub->where('source_location_id', $locationId)
                    ->orWhere('destination_location_id', $locationId);
            });
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->where(function ($sub) use ($allowedLocationIds): void {
                $sub->whereIn('source_location_id', $allowedLocationIds)
                    ->orWhereIn('destination_location_id', $allowedLocationIds);
            });
        }

        return (int) $query->count();
    }

    private function countPendingTransfersByDirection(?int $locationId, string $direction): int
    {
        if (! $locationId) {
            return 0;
        }

        $query = StockTransfer::query()
            ->whereIn('status', ['draft', 'sent']);

        if ($direction === 'in') {
            $query->where('destination_location_id', $locationId);
        } else {
            $query->where('source_location_id', $locationId);
        }

        return (int) $query->count();
    }

    private function countPendingAdjustments(?int $locationId, array $allowedLocationIds, bool $canViewAll): int
    {
        $query = StockAdjustment::withoutGlobalScope('active_location')
            ->where('status', 'pending');

        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif (! $canViewAll && $allowedLocationIds !== []) {
            $query->whereIn('location_id', $allowedLocationIds);
        }

        return (int) $query->count();
    }

    private function resolveLocationName(?int $locationId, bool $canViewAll): string
    {
        if ($locationId) {
            $location = Location::query()->find($locationId);

            return $location?->name ?? '-';
        }

        return $canViewAll ? 'All Locations' : '-';
    }
}
