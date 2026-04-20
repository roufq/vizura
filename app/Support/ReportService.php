<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Location;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildSalesReport(array $filters, bool $canViewAll, ?int $locationId, bool $paginate = true): array
    {
        $salesQuery = $canViewAll
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        if ($locationId) {
            $salesQuery->where('location_id', $locationId);
        }

        if (! empty($filters['start_date'])) {
            $salesQuery->whereDate(DB::raw('COALESCE(posted_at, created_at)'), '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $salesQuery->whereDate(DB::raw('COALESCE(posted_at, created_at)'), '<=', $filters['end_date']);
        }

        if (! empty($filters['cashier_id'])) {
            $salesQuery->where('cashier_id', $filters['cashier_id']);
        }

        if (! empty($filters['status'])) {
            $salesQuery->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $salesQuery->where('type', $filters['type']);
        }

        if (! empty($filters['method'])) {
            $salesQuery->whereHas('payments', function ($query) use ($filters): void {
                $query->where('method', $filters['method']);
            });
        }

        $summary = (clone $salesQuery)
            ->selectRaw('COALESCE(SUM(subtotal), 0) as gross_total')
            ->selectRaw('COALESCE(SUM(order_discount), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(total), 0) as net_total')
            ->first();

        $query = $salesQuery
            ->with(['cashier', 'payments'])
            ->orderByDesc(DB::raw('COALESCE(posted_at, created_at)'));

        $sales = $paginate
            ? $query->paginate(15)->withQueryString()
            : $query->get();

        $paymentMethods = $this->getPaymentMethods($canViewAll, $locationId);

        return compact('sales', 'summary', 'paymentMethods');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildCashUpReport(array $filters, bool $canViewAll, ?int $locationId): array
    {
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

        if (! empty($filters['start_date'])) {
            $paymentsQuery->whereDate(DB::raw('COALESCE(sales.posted_at, sales.created_at)'), '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $paymentsQuery->whereDate(DB::raw('COALESCE(sales.posted_at, sales.created_at)'), '<=', $filters['end_date']);
        }

        $rows = $paymentsQuery
            ->selectRaw('DATE(COALESCE(sales.posted_at, sales.created_at)) as sale_date, sale_payments.method, SUM(sale_payments.amount) as total')
            ->groupBy('sale_date', 'sale_payments.method')
            ->orderByDesc('sale_date')
            ->get();

        $totalsByMethod = $rows
            ->groupBy('method')
            ->map(fn (Collection $group): float => (float) $group->sum('total'));

        $grandTotal = (float) $rows->sum('total');

        return compact('rows', 'totalsByMethod', 'grandTotal');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildStockReport(array $filters, bool $canViewAll, ?int $locationId, bool $paginate = true): array
    {
        $stockQuery = $canViewAll
            ? StockItem::withoutGlobalScope('active_location')
            : StockItem::query();

        if ($locationId) {
            $stockQuery->where('stock_items.location_id', $locationId);
        }

        if (! empty($filters['search'])) {
            $stockQuery->whereHas('product', function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('sku', 'like', '%'.$filters['search'].'%');
            });
        }

        $totalValue = (clone $stockQuery)
            ->join('products', 'products.id', '=', 'stock_items.product_id')
            ->leftJoin('product_prices', function ($join) use ($locationId): void {
                $join->on('product_prices.product_id', '=', 'stock_items.product_id');

                if ($locationId) {
                    $join->where('product_prices.location_id', $locationId);
                } else {
                    $join->on('product_prices.location_id', '=', 'stock_items.location_id');
                }
            })
            ->sum(DB::raw('stock_items.quantity_on_hand * COALESCE(product_prices.cost_price, products.cost_price)'));

        $query = $stockQuery
            ->with(['product', 'location'])
            ->orderBy('product_id');

        $stockItems = $paginate
            ? $query->paginate(20)->withQueryString()
            : $query->get();

        return [
            'stockItems' => $stockItems,
            'totalValue' => (float) $totalValue,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function buildCashFlowReport(array $filters, bool $canViewAll, ?int $locationId): array
    {
        $journalQuery = $this->buildJournalLineQuery($canViewAll, $locationId, $filters['start_date'] ?? null, $filters['end_date'] ?? null);

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

        return compact('cashMovements', 'netChange');
    }

    public function locations(bool $canViewAll, array $allowedLocationIds): Collection
    {
        return $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();
    }

    public function cashiers(): Collection
    {
        return \App\Models\User::role('Cashier')->orderBy('name')->get();
    }

    public function getPaymentMethods(bool $canViewAll, ?int $locationId): Collection
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

    public function buildJournalLineQuery(bool $canViewAll, ?int $locationId, ?string $startDate, ?string $endDate)
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
