<?php

use App\Http\Controllers\ActiveLocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerLocationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePayableController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/locations/active', [ActiveLocationController::class, 'show'])->name('locations.active');
    Route::post('/locations/active', [ActiveLocationController::class, 'update'])->name('locations.active.update');
});

Route::middleware(['auth', 'active_location'])->group(function () {
    Route::get('/dashboard', function () {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $salesToday = Sale::query()
            ->where('status', 'posted')
            ->where('type', 'sale')
            ->whereDate('posted_at', $today)
            ->sum('total');

        $salesMonth = Sale::query()
            ->where('status', 'posted')
            ->where('type', 'sale')
            ->whereDate('posted_at', '>=', $monthStart)
            ->sum('total');

        $transactionsToday = Sale::query()
            ->where('status', 'posted')
            ->where('type', 'sale')
            ->whereDate('posted_at', $today)
            ->count();

        $activeProducts = Product::query()->where('is_active', true)->count();

        $lowStocks = StockItem::query()
            ->with('product')
            ->where('quantity_on_hand', '<=', 5)
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get();

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'posted')
            ->where('sales.type', 'sale')
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_qty'), DB::raw('SUM(sale_items.line_total) as total_sales'))
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(5)
            ->get();

        $recentAdjustments = StockAdjustment::query()
            ->with(['product', 'requester'])
            ->latest()
            ->limit(5)
            ->get();

        $salesSeries = Sale::query()
            ->where('status', 'posted')
            ->where('type', 'sale')
            ->whereDate('posted_at', '>=', now()->subDays(6)->toDateString())
            ->selectRaw("DATE(COALESCE(posted_at, created_at)) as sale_date, SUM(total) as total")
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $labels = [];
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');
            $series[] = (float) ($salesSeries[$date]->total ?? 0);
        }

        $locationName = Auth::user()?->activeLocation?->name ?? '-';

        return view('dashboard', compact(
            'salesToday',
            'salesMonth',
            'transactionsToday',
            'activeProducts',
            'lowStocks',
            'topProducts',
            'recentAdjustments',
            'labels',
            'series',
            'locationName'
        ));
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('locations', LocationController::class)->except(['show']);
    Route::post('locations/{location}/sync-stock', [LocationController::class, 'syncStock'])
        ->name('locations.sync-stock');
    Route::get('manager-locations', [ManagerLocationController::class, 'index'])
        ->name('manager-locations.index');
    Route::get('manager-locations/{manager}/edit', [ManagerLocationController::class, 'edit'])
        ->name('manager-locations.edit');
    Route::put('manager-locations/{manager}', [ManagerLocationController::class, 'update'])
        ->name('manager-locations.update');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['show']);
    Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])
        ->name('stock-adjustments.approve');
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('purchases', PurchaseController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::get('purchases/payables', [PurchasePayableController::class, 'index'])
        ->name('purchases.payables.index');
    Route::get('purchases/payables/{purchase}', [PurchasePayableController::class, 'show'])
        ->name('purchases.payables.show');
    Route::post('purchases/payables/{purchase}', [PurchasePayableController::class, 'store'])
        ->name('purchases.payables.store');
    Route::get('receivables', [ReceivableController::class, 'index'])
        ->name('receivables.index');
    Route::get('receivables/{sale}', [ReceivableController::class, 'show'])
        ->name('receivables.show');
    Route::post('receivables/{sale}', [ReceivableController::class, 'store'])
        ->name('receivables.store');
    Route::resource('stock-transfers', StockTransferController::class)->except(['show', 'edit', 'update']);
    Route::post('stock-transfers/{stockTransfer}/send', [StockTransferController::class, 'send'])
        ->name('stock-transfers.send');
    Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])
        ->name('stock-transfers.receive');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store']);
    Route::resource('sales', SaleController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::get('sales/{sale}/resume', [SaleController::class, 'resume'])
        ->name('sales.resume');
    Route::delete('sales/{sale}/draft', [SaleController::class, 'destroyDraft'])
        ->name('sales.draft.destroy');
    Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])
        ->name('sales.receipt');
    Route::post('sales/{sale}/void', [SaleController::class, 'void'])
        ->name('sales.void');
    Route::post('sales/{sale}/return', [SaleController::class, 'return'])
        ->name('sales.return');

    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/cash-up', [ReportController::class, 'cashUp'])->name('reports.cash-up');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock-card', [ReportController::class, 'stockCard'])->name('reports.stock-card');
    Route::get('reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.cash-flow');
});

require __DIR__.'/auth.php';
