<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockTransferRequest;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockTransfer;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|HeadStore');
    }

    public function index(): View
    {
        $user = request()->user();
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canViewAll = $user?->hasRole('Owner') ?? false;

        $search = request('search');
        $status = request('status');
        $locationId = request('location_id');

        $transfersQuery = StockTransfer::query()
            ->with(['sourceLocation', 'destinationLocation', 'requester', 'sender', 'receiver'])
            ->latest();

        if ($search) {
            $transfersQuery->where('reference_no', 'like', "%{$search}%");
        }

        if ($status) {
            $transfersQuery->where('status', $status);
        }

        if ($locationId) {
            $transfersQuery->where(function ($query) use ($locationId): void {
                $query->where('source_location_id', $locationId)
                    ->orWhere('destination_location_id', $locationId);
            });
        }

        if (! $canViewAll) {
            if ($allowedLocationIds === []) {
                $transfersQuery->whereRaw('1 = 0');
            } else {
                $transfersQuery->where(function ($query) use ($allowedLocationIds): void {
                    $query->whereIn('source_location_id', $allowedLocationIds)
                        ->orWhereIn('destination_location_id', $allowedLocationIds);
                });
            }
        }

        $transfers = $transfersQuery->paginate(10);
        $locations = Location::active()->orderBy('name')->get();

        return view('stock-transfers.index', compact('transfers', 'search', 'status', 'locationId', 'locations'));
    }

    public function create(): View
    {
        $user = request()->user();
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canViewAll = $user?->hasRole('Owner') ?? false;
        $sourceLocationId = ActiveLocation::id();
        $sourceLocation = $sourceLocationId
            ? Location::query()->find($sourceLocationId)
            : null;

        $locationsQuery = Location::active()
            ->when(! $canViewAll, function ($query) use ($allowedLocationIds): void {
                if ($allowedLocationIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $allowedLocationIds);
                }
            })
            ->when($sourceLocationId, function ($query) use ($sourceLocationId): void {
                $query->whereKeyNot($sourceLocationId);
            })
            ->orderBy('name');

        $locations = $locationsQuery->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $referenceNo = $this->generateReferenceNo();

        return view('stock-transfers.create', compact('locations', 'products', 'referenceNo', 'sourceLocation'));
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load(['items.product', 'sourceLocation', 'destinationLocation', 'requester', 'sender', 'receiver']);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $sourceLocationId = ActiveLocation::id();
        $user = $request->user();
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canViewAll = $user?->hasRole('Owner') ?? false;

        if (! $sourceLocationId) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'Active location must be selected.']);
        }

        if ((int) $data['source_location_id'] !== $sourceLocationId) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['source_location_id' => 'Source location must match active location.'])
                ->withInput();
        }

        if (! $canViewAll && ! in_array((int) $data['destination_location_id'], $allowedLocationIds, true)) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['destination_location_id' => 'You do not have access to the destination location.'])
                ->withInput();
        }

        $items = $this->normalizeItems($data['items'] ?? []);
        if ($items === []) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['items' => 'Transfer items are required.'])
                ->withInput();
        }

        $transfer = StockTransfer::create([
            'reference_no' => $data['reference_no'],
            'source_location_id' => $sourceLocationId,
            'destination_location_id' => $data['destination_location_id'],
            'status' => 'draft',
            'requested_by' => $request->user()?->id,
        ]);

        $transfer->items()->createMany($items);

        return redirect()
            ->route('stock-transfers.index')
            ->with('status', 'Stock transfer created successfully.');
    }

    public function send(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Stock transfer has been processed.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->source_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'You do not have access to the source location.']);
        }

        try {
            DB::transaction(function () use ($stockTransfer): void {
                $stockTransfer->load('items');
                $lockedStockItems = $this->lockStockItemsForLocation(
                    (int) $stockTransfer->source_location_id,
                    $stockTransfer->items->all()
                );

                foreach ($stockTransfer->items as $item) {
                    $stockItem = $lockedStockItems[$item->product_id]
                        ?? StockItem::withoutGlobalScope('active_location')->firstOrCreate([
                            'location_id' => $stockTransfer->source_location_id,
                            'product_id' => $item->product_id,
                        ], [
                            'quantity_on_hand' => 0,
                        ]);

                    $stockItem->adjustQuantity(-1 * (float) $item->quantity);
                }

                $stockTransfer->update([
                    'status' => 'sent',
                    'sent_by' => request()->user()?->id,
                    'sent_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => request()->user()?->id,
                    'location_id' => $stockTransfer->source_location_id,
                    'action' => 'stock_transfer_sent',
                    'metadata' => [
                        'stock_transfer_id' => $stockTransfer->id,
                        'reference_no' => $stockTransfer->reference_no,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'occurred_at' => now(),
                ]);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('stock-transfers.index')
            ->with('status', 'Stock transfer sent.');
    }

    public function receive(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'sent') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Stock transfer has not been sent yet.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->destination_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'You do not have access to the destination location.']);
        }

        if ($stockTransfer->sent_by === request()->user()?->id) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['received_by' => 'The receiver must be different from the sender.']);
        }

        DB::transaction(function () use ($stockTransfer): void {
            $stockTransfer->load('items');
            $lockedStockItems = $this->lockStockItemsForLocation(
                (int) $stockTransfer->destination_location_id,
                $stockTransfer->items->all()
            );

            foreach ($stockTransfer->items as $item) {
                $stockItem = $lockedStockItems[$item->product_id]
                    ?? StockItem::withoutGlobalScope('active_location')->firstOrCreate([
                        'location_id' => $stockTransfer->destination_location_id,
                        'product_id' => $item->product_id,
                    ], [
                        'quantity_on_hand' => 0,
                    ]);

                $stockItem->adjustQuantity((float) $item->quantity);
            }

            $stockTransfer->update([
                'status' => 'received',
                'received_by' => request()->user()?->id,
                'received_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => request()->user()?->id,
                'location_id' => $stockTransfer->destination_location_id,
                'action' => 'stock_transfer_received',
                'metadata' => [
                    'stock_transfer_id' => $stockTransfer->id,
                    'reference_no' => $stockTransfer->reference_no,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);
        });

        return redirect()
            ->route('stock-transfers.index')
            ->with('status', 'Stock transfer received.');
    }

    public function destroy(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Stock transfer that has been processed cannot be deleted.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->source_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'You do not have access to the source location.']);
        }

        $stockTransfer->delete();

        return redirect()
            ->route('stock-transfers.index')
            ->with('status', 'Stock transfer deleted.');
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item): bool => ! empty($item['product_id']) && (float) ($item['quantity'] ?? 0) > 0)
            ->map(fn ($item): array => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (float) $item['quantity'],
            ])
            ->values()
            ->all();
    }

    private function generateReferenceNo(): string
    {
        return 'TRF-'.now()->format('Ymd-His').'-'.random_int(100, 999);
    }

    /**
     * @param  array<int, \App\Models\StockTransferItem>  $items
     * @return array<int, \App\Models\StockItem>
     */
    private function lockStockItemsForLocation(int $locationId, array $items): array
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->unique()
            ->sort()
            ->values();

        if ($productIds->isEmpty()) {
            return [];
        }

        $locked = StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $locationId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        $missingIds = $productIds->diff($locked->keys());
        foreach ($missingIds as $productId) {
            StockItem::withoutGlobalScope('active_location')->firstOrCreate([
                'location_id' => $locationId,
                'product_id' => $productId,
            ], [
                'quantity_on_hand' => 0,
            ]);
        }

        return StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $locationId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id')
            ->all();
    }

    private function canAccessLocation($user, int $locationId): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Owner')) {
            return true;
        }

        $allowedLocationIds = $user->accessibleLocationIds();

        return $allowedLocationIds !== [] && in_array($locationId, $allowedLocationIds, true);
    }
}
