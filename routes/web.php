<?php

use App\Http\Controllers\ActiveLocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerLocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePayableController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReceiptSettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::get('lang/{locale}', [LanguageController::class, 'update'])->name('lang.update');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return 'Cache cleared successfully!';
});



Route::middleware('auth')->group(function () {
    Route::get('/locations/active', [ActiveLocationController::class, 'show'])->name('locations.active');
    Route::post('/locations/active', [ActiveLocationController::class, 'update'])->name('locations.active.update');
});

Route::middleware(['auth', 'active_location'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

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
    Route::get('products/search', [ProductSearchController::class, 'search'])->name('products.search');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('stock-adjustments', StockAdjustmentController::class);
    Route::post('stock-adjustments/bulk-approve', [StockAdjustmentController::class, 'bulkApprove'])
        ->name('stock-adjustments.bulk-approve');
    Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])
        ->name('stock-adjustments.approve');
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('customers', CustomerController::class);
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
    Route::resource('stock-transfers', StockTransferController::class)->except(['edit', 'update']);
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

    Route::get('settings/receipt', [ReceiptSettingController::class, 'edit'])->name('settings.receipt.edit');
    Route::put('settings/receipt', [ReceiptSettingController::class, 'update'])->name('settings.receipt.update');

    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('reports/cash-up', [ReportController::class, 'cashUp'])->name('reports.cash-up');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/stock/export', [ReportController::class, 'exportStock'])->name('reports.stock.export');
    Route::get('reports/stock-card', [ReportController::class, 'stockCard'])->name('reports.stock-card');
    Route::get('reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
    Route::get('reports/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.cash-flow');

    // Billing / Upgrade
    Route::get('billing/upgrade', function () {
        return view('billing.upgrade');
    })->name('billing.upgrade');

    // Super Admin Routes
    Route::middleware('role:Super Admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::patch('tenants/{tenant}/status', [App\Http\Controllers\SuperAdmin\TenantController::class, 'updateStatus'])->name('tenants.status');
        Route::resource('tenants', App\Http\Controllers\SuperAdmin\TenantController::class);
        
        Route::get('settings', [App\Http\Controllers\SuperAdmin\SaaSConfigController::class, 'index'])->name('settings.index');
        Route::post('settings', [App\Http\Controllers\SuperAdmin\SaaSConfigController::class, 'update'])->name('settings.update');
    });
});

Route::get('/ui-preview', function () {
    return view('ui-preview');
})->name('ui-preview');

require __DIR__.'/auth.php';
