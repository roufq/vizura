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
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $user = request()->user();
        $allowedLocationIds = $user?->accessibleLocationIds() ?? [];
        $canViewAll = $user?->hasRole('Owner') ?? false;

        $transfersQuery = StockTransfer::query()
            ->with(['sourceLocation', 'destinationLocation', 'requester', 'sender', 'receiver'])
            ->latest();

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

        return view('stock-transfers.index', compact('transfers'));
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
                ->withErrors(['location_id' => 'Lokasi aktif wajib dipilih.']);
        }

        if ((int) $data['source_location_id'] !== $sourceLocationId) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['source_location_id' => 'Lokasi sumber harus sesuai lokasi aktif.'])
                ->withInput();
        }

        if (! $canViewAll && ! in_array((int) $data['destination_location_id'], $allowedLocationIds, true)) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['destination_location_id' => 'Anda tidak memiliki akses ke lokasi tujuan.'])
                ->withInput();
        }

        $items = $this->normalizeItems($data['items'] ?? []);
        if ($items === []) {
            return redirect()
                ->route('stock-transfers.create')
                ->withErrors(['items' => 'Item transfer wajib diisi.'])
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
            ->with('status', 'Transfer stok berhasil dibuat.');
    }

    public function send(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Transfer stok sudah diproses.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->source_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'Anda tidak memiliki akses ke lokasi sumber.']);
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
            ->with('status', 'Transfer stok dikirim.');
    }

    public function receive(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'sent') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Transfer stok belum dikirim.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->destination_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'Anda tidak memiliki akses ke lokasi tujuan.']);
        }

        if ($stockTransfer->sent_by === request()->user()?->id) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['received_by' => 'Penerima harus berbeda dengan pengirim.']);
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
            ->with('status', 'Transfer stok diterima.');
    }

    public function destroy(StockTransfer $stockTransfer): RedirectResponse
    {
        if ($stockTransfer->status !== 'draft') {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['status' => 'Transfer stok yang sudah diproses tidak bisa dihapus.']);
        }

        if (! $this->canAccessLocation(request()->user(), (int) $stockTransfer->source_location_id)) {
            return redirect()
                ->route('stock-transfers.index')
                ->withErrors(['location_id' => 'Anda tidak memiliki akses ke lokasi sumber.']);
        }

        $stockTransfer->delete();

        return redirect()
            ->route('stock-transfers.index')
            ->with('status', 'Transfer stok dihapus.');
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
