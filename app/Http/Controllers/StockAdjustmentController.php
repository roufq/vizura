<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockItem;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|HeadStore');
    }

    public function index(): View
    {
        $user = request()->user();
        $canManageAll = ($user?->hasRole('Owner') ?? false) || ($user?->hasRole('Manager') ?? false);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $locationId = ActiveLocation::id();
        $search = trim(request()->string('search')->toString());
        $status = request()->string('status')->toString();
        $startDate = request()->string('start_date')->toString();
        $endDate = request()->string('end_date')->toString();

        $adjustmentsQuery = $canManageAll
            ? StockAdjustment::withoutGlobalScope('active_location')
            : StockAdjustment::query();

        if ($canManageAll && request()->integer('location_id')) {
            $requestedLocationId = request()->integer('location_id');
            if ($allowedLocationIds === [] || in_array($requestedLocationId, $allowedLocationIds, true)) {
                $locationId = $requestedLocationId;
            }
        }

        if ($locationId) {
            $adjustmentsQuery->where('location_id', $locationId);
        }

        if ($search !== '') {
            $adjustmentsQuery->whereHas('product', function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        if ($status !== '') {
            $adjustmentsQuery->where('status', $status);
        }

        if ($startDate !== '') {
            $adjustmentsQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate !== '') {
            $adjustmentsQuery->whereDate('created_at', '<=', $endDate);
        }

        $adjustments = $adjustmentsQuery
            ->with(['location', 'product', 'requester', 'approver'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $locations = $canManageAll
            ? Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get()
            : collect();

        return view('stock-adjustments.index', compact(
            'adjustments',
            'canManageAll',
            'locations',
            'locationId',
            'search',
            'status',
            'startDate',
            'endDate'
        ));
    }

    public function create(): View
    {
        $user = request()->user();
        $canManageAll = ($user?->hasRole('Owner') ?? false) || ($user?->hasRole('Manager') ?? false);
        $locationId = ActiveLocation::id();
        $activeLocation = $locationId ? Location::query()->find($locationId) : null;

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $locations = $canManageAll
            ? Location::active()->whereIn('id', $allowedLocationIds)->orderBy('name')->get()
            : collect();

        return view('stock-adjustments.create', compact('products', 'locations', 'canManageAll', 'activeLocation'));
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $locationId = ActiveLocation::id();
        $user = $request->user();
        $canManageAll = ($user?->hasRole('Owner') ?? false) || ($user?->hasRole('Manager') ?? false);
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];

        if ($canManageAll && ! empty($data['location_id'])) {
            $requestedLocationId = (int) $data['location_id'];
            if ($allowedLocationIds === [] || in_array($requestedLocationId, $allowedLocationIds, true)) {
                $locationId = $requestedLocationId;
            }
        }

        if (! $locationId) {
            return redirect()
                ->route('stock-adjustments.index')
                ->withErrors(['location_id' => 'Active location must be selected.']);
        }

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('stock-adjustments', 'public');
        }

        $referenceNo = 'ADJ-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(2)));

        DB::transaction(function () use ($data, $locationId, $request, $evidencePath, $referenceNo): void {
            foreach ($data['items'] as $item) {
                StockAdjustment::create([
                    'location_id' => $locationId,
                    'reference_no' => $referenceNo,
                    'product_id' => $item['product_id'],
                    'requested_by' => $request->user()?->id,
                    'quantity_delta' => $item['quantity_delta'],
                    'reason' => $data['reason'],
                    'evidence_path' => $evidencePath,
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()
            ->route('stock-adjustments.index')
            ->with('status', 'Successfully created '.count($data['items']).' stock adjustments.');
    }

    public function show(int $stockAdjustment): View
    {
        $stockAdjustment = $this->resolveAdjustment($stockAdjustment);
        $stockAdjustment->load(['location', 'product', 'requester', 'approver']);

        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function approve(int $stockAdjustment): RedirectResponse
    {
        $stockAdjustment = $this->resolveAdjustment($stockAdjustment);

        if ($stockAdjustment->status !== 'pending') {
            return redirect()
                ->route('stock-adjustments.index')
                ->withErrors(['status' => 'Stock adjustment has been processed.']);
        }

        try {
            DB::transaction(function () use ($stockAdjustment): void {
                $this->processApproval($stockAdjustment);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('stock-adjustments.index')
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('stock-adjustments.index')
            ->with('status', 'Stock adjustment approved.');
    }

    public function bulkApprove(): RedirectResponse
    {
        $user = request()->user();
        $canManageAll = ($user?->hasRole('Owner') ?? false) || ($user?->hasRole('Manager') ?? false);
        $locationId = ActiveLocation::id();

        $query = $canManageAll
            ? StockAdjustment::withoutGlobalScope('active_location')
            : StockAdjustment::query();

        if ($locationId && ! $canManageAll) {
            $query->where('location_id', $locationId);
        }

        $pendingAdjustments = $query->where('status', 'pending')->get();

        if ($pendingAdjustments->isEmpty()) {
            return redirect()
                ->route('stock-adjustments.index')
                ->with('warning', 'No pending stock adjustments found.');
        }

        $count = 0;
        try {
            DB::transaction(function () use ($pendingAdjustments, &$count): void {
                /** @var \App\Models\StockAdjustment $adj */
                foreach ($pendingAdjustments as $adj) {
                    $this->processApproval($adj);
                    $count++;
                }
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('stock-adjustments.index')
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('stock-adjustments.index')
            ->with('status', "Successfully approved $count stock adjustments globally.");
    }

    private function processApproval(StockAdjustment $stockAdjustment): void
    {
        $stockItem = StockItem::withoutGlobalScope('active_location')->firstOrCreate([
            'location_id' => $stockAdjustment->location_id,
            'product_id' => $stockAdjustment->product_id,
        ], [
            'quantity_on_hand' => 0,
        ]);

        $stockItem->adjustQuantity((float) $stockAdjustment->quantity_delta);

        $stockAdjustment->update([
            'status' => 'approved',
            'approved_by' => request()->user()?->id,
            'approved_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'location_id' => $stockAdjustment->location_id,
            'action' => 'stock_adjustment_approved',
            'metadata' => [
                'stock_adjustment_id' => $stockAdjustment->id,
                'product_id' => $stockAdjustment->product_id,
                'quantity_delta' => $stockAdjustment->quantity_delta,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'occurred_at' => now(),
        ]);
    }

    public function destroy(int $stockAdjustment): RedirectResponse
    {
        $stockAdjustment = $this->resolveAdjustment($stockAdjustment);

        if ($stockAdjustment->status !== 'pending') {
            return redirect()
                ->route('stock-adjustments.index')
                ->withErrors(['status' => 'Stock adjustment that has been processed cannot be deleted.']);
        }

        if ($stockAdjustment->evidence_path) {
            Storage::disk('public')->delete($stockAdjustment->evidence_path);
        }

        $stockAdjustment->delete();

        return redirect()
            ->route('stock-adjustments.index')
            ->with('status', 'Stock adjustment deleted.');
    }

    private function resolveAdjustment(int $stockAdjustmentId): StockAdjustment
    {
        $user = request()->user();
        $canManageAll = ($user?->hasRole('Owner') ?? false) || ($user?->hasRole('Manager') ?? false);

        $query = $canManageAll
            ? StockAdjustment::withoutGlobalScope('active_location')
            : StockAdjustment::query();

        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];

        return $query->whereKey($stockAdjustmentId)
            ->when(! ($user?->hasRole('Owner') ?? false) && $allowedLocationIds !== [], function ($query) use ($allowedLocationIds): void {
                $query->whereIn('location_id', $allowedLocationIds);
            })
            ->firstOrFail();
    }
}
