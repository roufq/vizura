<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager');
    }

    public function index(): View
    {
        $search = trim(request()->string('search')->toString());
        $role = request()->string('role')->toString();
        $viewer = request()->user();
        $allowedLocationIds = $this->allowedLocationIds($viewer);

        $users = User::query()
            ->when(! $viewer?->hasRole('Owner') && $allowedLocationIds !== [], function ($query) use ($allowedLocationIds): void {
                $query->whereIn('active_location_id', $allowedLocationIds);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($role !== '', function ($query) use ($role): void {
                $query->role($role);
            })
            ->with(['activeLocation', 'roles'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        $user = request()->user();
        $locations = $this->allowedLocations($user);

        return view('users.create', [
            'locations' => $locations,
            'roles' => $this->availableRoles($user),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $creator = $request->user();

        $this->ensureRoleAllowed($creator, $data['role']);
        $locationIds = $this->filterLocationIds($creator, $data['location_ids'] ?? []);
        $activeLocationId = (int) $data['active_location_id'];

        if (! in_array($activeLocationId, $this->allowedLocationIds($creator), true)) {
            return redirect()
                ->route('users.create')
                ->withErrors(['active_location_id' => 'Location not authorized.'])
                ->withInput();
        }

        if ($data['role'] === 'Manager' && $locationIds === []) {
            return redirect()
                ->route('users.create')
                ->withErrors(['location_ids' => 'Select at least one location for manager.'])
                ->withInput();
        }

        if ($data['role'] === 'Manager' && ! in_array($activeLocationId, $locationIds, true)) {
            $locationIds[] = $activeLocationId;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'active_location_id' => $activeLocationId,
        ]);

        $user->syncRoles([$data['role']]);

        if ($data['role'] === 'Manager') {
            $user->locations()->sync($locationIds);
        } else {
            $user->locations()->sync([]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $viewer = request()->user();
        $this->ensureUserAccessible($viewer, $user);
        $locations = $this->allowedLocations($viewer);
        $selectedLocations = $user->locations()->pluck('locations.id')->all();

        return view('users.edit', [
            'user' => $user,
            'locations' => $locations,
            'roles' => $this->availableRoles($viewer),
            'selectedLocations' => $selectedLocations,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $editor = $request->user();
        $this->ensureUserAccessible($editor, $user);

        $this->ensureRoleAllowed($editor, $data['role']);
        $locationIds = $this->filterLocationIds($editor, $data['location_ids'] ?? []);
        $activeLocationId = (int) $data['active_location_id'];

        if (! in_array($activeLocationId, $this->allowedLocationIds($editor), true)) {
            return redirect()
                ->route('users.edit', $user)
                ->withErrors(['active_location_id' => 'Location not authorized.'])
                ->withInput();
        }

        if ($data['role'] === 'Manager' && $locationIds === []) {
            return redirect()
                ->route('users.edit', $user)
                ->withErrors(['location_ids' => 'Select at least one location for manager.'])
                ->withInput();
        }

        if ($data['role'] === 'Manager' && ! in_array($activeLocationId, $locationIds, true)) {
            $locationIds[] = $activeLocationId;
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'active_location_id' => $activeLocationId,
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->syncRoles([$data['role']]);

        if ($data['role'] === 'Manager') {
            $user->locations()->sync($locationIds);
        } else {
            $user->locations()->sync([]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === request()->user()?->id) {
            return redirect()
                ->route('users.index')
                ->withErrors(['user' => 'Cannot delete your own account.']);
        }

        $this->ensureUserAccessible(request()->user(), $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted successfully.');
    }

    /**
     * @return array<int, string>
     */
    private function availableRoles($user): array
    {
        if ($user?->hasRole('Owner')) {
            return ['Owner', 'Manager', 'HeadStore', 'Cashier'];
        }

        return ['HeadStore', 'Cashier'];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Location>
     */
    private function allowedLocations($user)
    {
        if ($user?->hasRole('Owner')) {
            return Location::active()->orderBy('name')->get();
        }

        $ids = $this->allowedLocationIds($user);

        return Location::active()
            ->when($ids !== [], function ($query) use ($ids): void {
                $query->whereIn('id', $ids);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int>
     */
    private function allowedLocationIds($user): array
    {
        if (! $user) {
            return [];
        }

        return $user->accessibleLocationIds();
    }

    /**
     * @param array<int> $locationIds
     * @return array<int>
     */
    private function filterLocationIds($user, array $locationIds): array
    {
        $allowed = $this->allowedLocationIds($user);

        return array_values(array_filter($locationIds, fn ($id): bool => in_array((int) $id, $allowed, true)));
    }

    private function ensureRoleAllowed($user, string $role): void
    {
        $roles = $this->availableRoles($user);

        if (! in_array($role, $roles, true)) {
            abort(403);
        }
    }

    private function ensureUserAccessible($viewer, User $user): void
    {
        if ($viewer?->hasRole('Owner')) {
            return;
        }

        $allowed = $this->allowedLocationIds($viewer);
        if ($allowed === [] || ! in_array((int) $user->active_location_id, $allowed, true)) {
            abort(403);
        }
    }
}
