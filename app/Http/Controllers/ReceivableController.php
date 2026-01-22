<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReceivablePaymentRequest;
use App\Models\Location;
use App\Models\Sale;
use App\Models\SalePaymentSettlement;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivableController extends Controller
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

        $baseQuery = $canViewAll
            ? Sale::withoutGlobalScope('active_location')
            : Sale::query();

        $baseQuery->where('type', 'sale')
            ->where('status', 'posted')
            ->whereHas('payments', function ($paymentQuery): void {
                $paymentQuery->where('method', 'piutang');
            });

        $normalizeQuery = clone $baseQuery;
        if ($locationId) {
            $normalizeQuery->where('location_id', $locationId);
        }
        $normalizeQuery
            ->where('payment_status', '!=', 'paid')
            ->where('receivable_balance', '<=', 0)
            ->update(['payment_status' => 'paid']);

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

    public function show(Sale $sale, Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $this->canAccessSale($user, $sale)) {
            return redirect()
                ->route('receivables.index')
                ->withErrors(['sale' => 'Anda tidak memiliki akses ke data ini.']);
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

    public function store(StoreReceivablePaymentRequest $request, Sale $sale): RedirectResponse
    {
        $user = $request->user();
        if (! $this->canAccessSale($user, $sale)) {
            return redirect()
                ->route('receivables.index')
                ->withErrors(['sale' => 'Anda tidak memiliki akses ke data ini.']);
        }

        if ($sale->payment_status === 'paid') {
            return redirect()
                ->route('receivables.index')
                ->withErrors(['sale' => 'Transaksi ini sudah lunas.']);
        }

        $data = $request->validated();
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return redirect()
                ->route('receivables.show', $sale)
                ->withErrors(['amount' => 'Jumlah bayar harus lebih dari 0.']);
        }

        if ($amount > (float) $sale->receivable_balance) {
            return redirect()
                ->route('receivables.show', $sale)
                ->withErrors(['amount' => 'Jumlah bayar melebihi sisa piutang.']);
        }

        DB::transaction(function () use ($sale, $data, $amount, $request): void {
            $payment = SalePaymentSettlement::create([
                'sale_id' => $sale->id,
                'location_id' => $sale->location_id,
                'method' => $data['method'],
                'amount' => $amount,
                'reference_no' => $data['reference_no'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'created_by' => $request->user()?->id,
            ]);

            $newPaid = min((float) $sale->total, (float) $sale->paid_total + $amount);
            $newPaid = round($newPaid, 2);
            $newBalance = max(0, round((float) $sale->total - $newPaid, 2));
            $status = $newBalance <= 0.0 ? 'paid' : 'partial';

            $sale->update([
                'paid_total' => $newPaid,
                'receivable_balance' => $newBalance,
                'payment_status' => $status,
            ]);

            app(AccountingService::class)->recordReceivablePayment($payment);
        });

        return redirect()
            ->route('receivables.show', $sale)
            ->with('status', 'Pembayaran piutang berhasil disimpan.');
    }

    private function canAccessSale($user, Sale $sale): bool
    {
        if ($user?->hasRole('Owner')) {
            return true;
        }

        return (int) $sale->location_id === (int) ActiveLocation::id();
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
