<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('users')->withCount(['users', 'locations'])->get();

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('super-admin.tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan' => 'required|in:starter,business,enterprise',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 1. Create the Tenant
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name),
            'plan' => $request->plan,
            'status' => 'active',
        ]);

        // 2. Create the Owner User
        $user = \App\Models\User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->owner_name,
            'email' => $request->owner_email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // 3. Assign Role OWNER
        $user->assignRole('Owner');

        // 4. Update tenant owner_id
        $tenant->update(['owner_id' => $user->id]);

        return redirect()->route('super-admin.tenants.index')
            ->with('status', 'New Owner and Tenant created successfully!');
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load(['users', 'locations']);
        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function updateStatus(Request $request, Tenant $tenant)
    {
        $request->validate([
            'status' => 'nullable|in:active,suspended',
            'plan' => 'nullable|in:starter,business,enterprise',
        ]);

        $tenant->update($request->only(['status', 'plan']));

        return back()->with('status', 'Tenant updated successfully.');
    }
}
