<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\User;
use App\Support\ActiveLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $search = trim(request()->string('search')->toString());
        $user = request()->user();
        $canSync = $this->canSyncLocations($user);

        $locations = Location::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('locations.index', compact('locations', 'search', 'canSync'));
    }

    public function create(): View
    {
        return view('locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['toko_pusat'] = (bool) ($data['toko_pusat'] ?? false);

        $location = Location::create($data);

        if ($location->toko_pusat) {
            Location::query()
                ->whereKeyNot($location->id)
                ->update(['toko_pusat' => false]);
        }

        return redirect()
            ->route('locations.index')
            ->with('status', 'Lokasi berhasil dibuat.');
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['toko_pusat'] = (bool) ($data['toko_pusat'] ?? false);

        $location->update($data);

        if ($location->toko_pusat) {
            Location::query()
                ->whereKeyNot($location->id)
                ->update(['toko_pusat' => false]);
        }

        return redirect()
            ->route('locations.index')
            ->with('status', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with('status', 'Lokasi berhasil dihapus.');
    }

    public function syncStock(Location $location): RedirectResponse
    {
        if (! $this->canSyncLocations(request()->user())) {
            abort(403);
        }

        $productIds = Product::query()->pluck('id');
        if ($productIds->isEmpty()) {
            return redirect()
                ->route('locations.index')
                ->with('status', 'Tidak ada produk untuk disinkronkan.');
        }

        $existingProductIds = StockItem::withoutGlobalScope('active_location')
            ->where('location_id', $location->id)
            ->pluck('product_id');

        $missingProductIds = $productIds->diff($existingProductIds);
        if ($missingProductIds->isEmpty()) {
            return redirect()
                ->route('locations.index')
                ->with('status', 'Produk lokasi sudah sinkron.');
        }

        $now = now();
        $payload = $missingProductIds
            ->map(fn (int $productId): array => [
                'location_id' => $location->id,
                'product_id' => $productId,
                'quantity_on_hand' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        StockItem::query()->insertOrIgnore($payload);

        return redirect()
            ->route('locations.index')
            ->with('status', 'Produk berhasil disinkronkan ke lokasi.');
    }

    private function canSyncLocations(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('Owner')) {
            return true;
        }

        if (! $user->hasRole('Manager') && ! $user->hasRole('KepalaToko')) {
            return false;
        }

        $locationId = ActiveLocation::id();
        if (! $locationId) {
            return false;
        }

        return Location::query()
            ->whereKey($locationId)
            ->where('toko_pusat', true)
            ->exists();
    }
}
