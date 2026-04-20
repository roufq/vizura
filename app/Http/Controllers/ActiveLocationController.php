<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateActiveLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiveLocationController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $locationIds = $user?->accessibleLocationIds() ?? [];

        $locations = Location::active()
            ->when($locationIds !== [], function ($query) use ($locationIds): void {
                $query->whereIn('id', $locationIds);
            })
            ->orderBy('name')
            ->get();

        return view('locations.active', [
            'locations' => $locations,
            'current' => $request->user()?->active_location_id,
        ]);
    }

    public function update(UpdateActiveLocationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = $request->user();
        $locationIds = $user?->accessibleLocationIds() ?? [];

        $location = Location::active()
            ->when($locationIds !== [], function ($query) use ($locationIds): void {
                $query->whereIn('id', $locationIds);
            })
            ->findOrFail($data['location_id']);

        $request->user()->update([
            'active_location_id' => $location->id,
        ]);

        session(['active_location_id' => $location->id]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Active location updated.');
    }
}
