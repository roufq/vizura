<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Models\Location;
use App\Models\Sale;
use App\Models\SalePaymentSettlement;
use App\Support\AccountingService;
use App\Support\LocationResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReceivableController extends Controller
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

        $baseQuery = ($canViewAll || $isManager)
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        $baseQuery->where('type', 'sale')
            ->where('status', 'posted')
            ->whereHas('payments', function ($paymentQuery): void {
                $paymentQuery->where('method', 'piutang');
            });

        if (! $canViewAll) {
            if ($allowedLocationIds === []) {
                $baseQuery->whereRaw('1 = 0');
            } else {
                $baseQuery->whereIn('location_id', $allowedLocationIds);
            }
        }

        $query = $baseQuery;

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($sub) use ($search): void {
                $sub->where('reference_no', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->string('status'));
        }

        $sales = $query
            ->with(['location'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        return view('receivables.index', [
            'sales' => $sales,
            'locations' => $locations,
            'filters' => $request->only(['search', 'status', 'location_id', 'all_locations']),
            'canViewAll' => $canViewAll,
            'canSelectLocations' => $canSelectLocations,
            'locationId' => $locationId,
        ]);
    }

    public function show(int $sale, Request $request): View|RedirectResponse
    {
        $sale = Sale::withoutGlobalScope('active_location')->whereKey($sale)->firstOrFail();

        $user = $request->user();
        if (! $this->canAccessSale($user, $sale)) {
            return redirect()
                ->route('receivables.index')
                ->withErrors(['sale' => 'You do not have access to this data.']);
        }

        $sale->load([
            'location',
            'settlements' => function ($query) use ($sale): void {
                $query->withoutGlobalScope('active_location')
                    ->where('location_id', $sale->location_id)
                    ->orderBy('paid_at');
            },
            'settlements.creator',
        ]);

        $paymentMethods = [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'transfer' => 'Transfer',
            'card' => 'Card',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
        ];

        return view('receivables.show', compact('sale', 'paymentMethods'));
    }

    public function store(StoreReceivablePaymentRequest $request, int $sale): RedirectResponse
    {
        $sale = Sale::withoutGlobalScope('active_location')->whereKey($sale)->firstOrFail();

        $user = $request->user();
        if (! $this->canAccessSale($user, $sale)) {
            return redirect()
                ->route('receivables.index')
                ->withErrors(['sale' => 'You do not have access to this data.']);
        }

        $data = $request->validated();
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return redirect()
                ->route('receivables.show', $sale)
                ->withErrors(['amount' => 'Payment amount must be greater than 0.']);
        }

        try {
            DB::transaction(function () use ($sale, $data, $amount, $request): void {
                $lockedSale = Sale::withoutGlobalScope('active_location')
                    ->whereKey($sale->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedSale->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        'sale' => 'This transaction is already fully paid.',
                    ]);
                }

                if ($amount > (float) $lockedSale->receivable_balance) {
                    throw ValidationException::withMessages([
                        'amount' => 'Payment amount exceeds remaining receivable balance.',
                    ]);
                }

                $payment = SalePaymentSettlement::create([
                    'sale_id' => $lockedSale->id,
                    'location_id' => $lockedSale->location_id,
                    'method' => $data['method'],
                    'amount' => $amount,
                    'reference_no' => $data['reference_no'] ?? null,
                    'paid_at' => $data['paid_at'] ?? now(),
                    'created_by' => $request->user()?->id,
                ]);

                $newPaid = min((float) $lockedSale->total, (float) $lockedSale->paid_total + $amount);
                $newPaid = round($newPaid, 2);
                $newBalance = max(0, round((float) $lockedSale->total - $newPaid, 2));
                $status = $newBalance <= 0.0 ? 'paid' : 'partial';

                $lockedSale->update([
                    'paid_total' => $newPaid,
                    'receivable_balance' => $newBalance,
                    'payment_status' => $status,
                ]);

                app(AccountingService::class)->recordReceivablePayment($payment);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('receivables.show', $sale)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('receivables.show', $sale)
            ->with('status', 'Receivable payment saved successfully.');
    }

    private function canAccessSale($user, Sale $sale): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Owner')) {
            return true;
        }

        $allowedLocationIds = $user->accessibleLocationIds();

        return $allowedLocationIds !== [] && in_array((int) $sale->location_id, $allowedLocationIds, true);
    }
}
