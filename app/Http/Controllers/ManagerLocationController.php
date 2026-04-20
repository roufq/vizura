<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateManagerLocationsRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManagerLocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner');
    }

    public function index(): View
    {
        $search = trim(request()->string('search')->toString());

        $managers = User::role('Manager')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->with('locations')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('manager-locations.index', compact('managers', 'search'));
    }

    public function edit(User $manager): View
    {
        if (! $manager->hasRole('Manager')) {
            abort(404);
        }

        $locations = Location::active()->orderBy('name')->get();
        $selected = $manager->locations()->pluck('locations.id')->all();

        return view('manager-locations.edit', compact('manager', 'locations', 'selected'));
    }

    public function update(UpdateManagerLocationsRequest $request, User $manager): RedirectResponse
    {
        if (! $manager->hasRole('Manager')) {
            abort(404);
        }

        $locationIds = $request->validated()['location_ids'] ?? [];

        $manager->locations()->sync($locationIds);

        return redirect()
            ->route('manager-locations.index')
            ->with('status', 'Manager location access updated.');
    }
}
