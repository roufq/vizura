<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Support\AccountingService;
use App\Support\LocationResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchasePayableController extends Controller
{
    public function __construct(public LocationResolver $locationResolver)
    {
        $this->middleware('role:Owner|Manager|HeadStore');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewAll = $user?->hasRole('Owner') ?? false;
        $isManager = $user?->hasRole('Manager') ?? false;
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canSelectLocations = $canViewAll || $isManager;
        $locationId = $this->locationResolver->resolveFromRequest($request, $allowedLocationIds, $canViewAll);

        $query = ($canViewAll || $isManager)
            ? Purchase::withoutGlobalScope('active_location')
            : Purchase::query();

        $query->where('payment_method', 'payable');

        if (! $canViewAll) {
            if ($allowedLocationIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('location_id', $allowedLocationIds);
            }
        }

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

    public function show(int $purchase, Request $request): View|RedirectResponse
    {
        $purchase = Purchase::withoutGlobalScope('active_location')->whereKey($purchase)->firstOrFail();

        $user = $request->user();
        if (! $this->canAccessPurchase($user, $purchase)) {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'You do not have access to this data.']);
        }

        if ($purchase->payment_method !== 'payable') {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'This purchase does not have any debt.']);
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

    public function store(StorePurchasePaymentRequest $request, int $purchase): RedirectResponse
    {
        $purchase = Purchase::withoutGlobalScope('active_location')->whereKey($purchase)->firstOrFail();

        $user = $request->user();
        if (! $this->canAccessPurchase($user, $purchase)) {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'You do not have access to this data.']);
        }

        if ($purchase->payment_method !== 'payable') {
            return redirect()
                ->route('purchases.payables.index')
                ->withErrors(['purchase' => 'This purchase does not have any debt.']);
        }

        $data = $request->validated();
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return redirect()
                ->route('purchases.payables.show', $purchase)
                ->withErrors(['amount' => 'Payment amount must be greater than 0.']);
        }

        try {
            DB::transaction(function () use ($purchase, $data, $amount, $request): void {
                $lockedPurchase = Purchase::withoutGlobalScope('active_location')
                    ->whereKey($purchase->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedPurchase->payment_method !== 'payable') {
                    throw ValidationException::withMessages([
                        'purchase' => 'This purchase does not have any debt.',
                    ]);
                }

                if ($amount > (float) $lockedPurchase->payable_balance) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds remaining debt.',
                    ]);
                }

                $payment = PurchasePayment::create([
                    'purchase_id' => $lockedPurchase->id,
                    'location_id' => $lockedPurchase->location_id,
                    'method' => $data['method'],
                    'amount' => $amount,
                    'reference_no' => $data['reference_no'] ?? null,
                    'paid_at' => $data['paid_at'] ?? now(),
                    'created_by' => $request->user()?->id,
                ]);

                $newPaid = (float) $lockedPurchase->paid_total + $amount;
                $newBalance = max(0, (float) $lockedPurchase->total - $newPaid);
                $status = $newBalance <= 0.0 ? 'paid' : 'partial';

                $lockedPurchase->update([
                    'paid_total' => $newPaid,
                    'payable_balance' => $newBalance,
                    'payment_status' => $status,
                ]);

                app(AccountingService::class)->recordPurchasePayment($payment);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('purchases.payables.show', $purchase)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('purchases.payables.show', $purchase)
            ->with('status', 'Debt payment saved successfully.');
    }

    private function canAccessPurchase($user, Purchase $purchase): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Owner')) {
            return true;
        }

        $allowedLocationIds = $user->accessibleLocationIds();

        return $allowedLocationIds !== [] && in_array((int) $purchase->location_id, $allowedLocationIds, true);
    }
}
