<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Location;
use App\Support\AccountingService;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
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
        $locationId = $this->resolveLocationId($request, $allowedLocationIds, $canViewAll);

        $query = $canViewAll
            ? Expense::withoutGlobalScope('active_location')
            : Expense::query();

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->string('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->string('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($sub) use ($search): void {
                $sub->where('reference_no', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $expenses = $query
            ->with(['account', 'paymentAccount', 'location', 'creator'])
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();

        $locations = $canViewAll
            ? Location::active()->orderBy('name')->get()
            : Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get();

        $todayTotal = (clone $query)->whereDate('expense_date', now())->sum('amount');
        $monthTotal = (clone $query)->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'locations' => $locations,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'location_id', 'all_locations']),
            'canViewAll' => $canViewAll,
            'locationId' => $locationId,
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
        ]);
    }

    public function create(): View
    {
        $expenseAccounts = Account::query()
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $paymentAccounts = Account::query()
            ->where('is_cash', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $referenceNo = $this->generateReferenceNo();

        return view('expenses.create', compact('expenseAccounts', 'paymentAccounts', 'referenceNo'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $locationId = ActiveLocation::id();
        if (! $locationId) {
            return redirect()
                ->route('expenses.index')
                ->withErrors(['location_id' => 'Lokasi aktif wajib dipilih.']);
        }

        $data = $request->validated();

        $expense = Expense::create([
            'location_id' => $locationId,
            'account_id' => $data['account_id'],
            'payment_account_id' => $data['payment_account_id'],
            'reference_no' => $data['reference_no'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'created_by' => $request->user()?->id,
        ]);

        app(AccountingService::class)->recordExpense($expense);

        return redirect()
            ->route('expenses.index')
            ->with('status', 'Biaya operasional berhasil disimpan.');
    }

    private function generateReferenceNo(): string
    {
        return 'EXP-'.now()->format('Ymd-His').'-'.random_int(100, 999);
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
