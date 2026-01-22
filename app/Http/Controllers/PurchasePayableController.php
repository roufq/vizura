<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchasePayableController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewAll = $user?->hasRole('Owner') ?? false;
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || ($user?->hasRole('Manager') ?? false);
        $locationId = $this->resolveLocationId($request, $allowedLocationIds, $canViewAll);

        $query = $canViewAll
            ? Purchase::withoutGlobalScope('active_location')
            : Purchase::query();

        $query->where('payment_method', 'payable');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($sub) use ($search): void {
                $sub->where('reference_no', 'like', '%'.$search.'%')
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search): void {
                        $supplierQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $purchases = $query
            ->with(['supplier', 'location'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        return view('purchases.payables.index', [
            'purchases' => $purchases,
            'locations' => $locations,
            'filters' => $request->only(['search', 'status', 'location_id', 'all_locations']),
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function show(Purchase $purchase, Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $this->canAccessPurchase($user, $purchase)) {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'Anda tidak memiliki akses ke data ini.']);
        }

        if ($purchase->payment_method !== 'payable') {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'Pembelian ini tidak memiliki hutang.']);
        }

        $purchase->load([
            'supplier',
            'location',
            'payments' => function ($query) use ($purchase): void {
                $query->withoutGlobalScope('active_location')
                    ->where('location_id', $purchase->location_id)
                    ->orderBy('paid_at');
            },
            'payments.creator',
        ]);

        $paymentMethods = [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'transfer' => 'Transfer',
            'card' => 'Card',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
        ];

        return view('purchases.payables.show', compact('purchase', 'paymentMethods'));
    }

    public function store(StorePurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        $user = $request->user();
        if (! $this->canAccessPurchase($user, $purchase)) {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'Anda tidak memiliki akses ke data ini.']);
        }

        if ($purchase->payment_method !== 'payable') {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'Pembelian ini tidak memiliki hutang.']);
        }

        $data = $request->validated();
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return redirect()
                ->route('purchases.payables.show', $purchase)
                ->withErrors(['amount' => 'Jumlah bayar harus lebih dari 0.']);
        }

        if ($amount > (float) $purchase->payable_balance) {
            return redirect()
                ->route('purchases.payables.show', $purchase)
                ->withErrors(['amount' => 'Jumlah bayar melebihi sisa hutang.']);
        }

        DB::transaction(function () use ($purchase, $data, $amount, $request): void {
            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'location_id' => $purchase->location_id,
                'method' => $data['method'],
                'amount' => $amount,
                'reference_no' => $data['reference_no'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'created_by' => $request->user()?->id,
            ]);

            $newPaid = (float) $purchase->paid_total + $amount;
            $newBalance = max(0, (float) $purchase->total - $newPaid);
            $status = $newBalance <= 0.0 ? 'paid' : 'partial';

            $purchase->update([
                'paid_total' => $newPaid,
                'payable_balance' => $newBalance,
                'payment_status' => $status,
            ]);

            app(AccountingService::class)->recordPurchasePayment($payment);
        });

        return redirect()
            ->route('purchases.payables.show', $purchase)
            ->with('status', 'Pembayaran hutang berhasil disimpan.');
    }

    private function canAccessPurchase($user, Purchase $purchase): bool
    {
        if ($user?->hasRole('Owner')) {
            return true;
        }

        return (int) $purchase->location_id === (int) ActiveLocation::id();
    }

    private function resolveLocationId(Request $request, array $allowedLocationIds, bool $canViewAll): ?int
    {
        if ($canViewAll && $request->boolean('all_locations')) {
            return null;
        }

        if ($request->filled('location_id')) {
            $requested = (int) $request->input('location_id');
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
}
