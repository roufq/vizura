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

    public function impersonate(Tenant $tenant)
    {
        $adminId = auth()->id();
        $owner = \App\Models\User::find($tenant->owner_id);

        if (!$owner) {
            return back()->with('error', 'Owner not found for this tenant.');
        }

        session(['impersonated_by' => $adminId]);
        \Illuminate\Support\Facades\Auth::login($owner);

        return redirect()->route('dashboard')->with('status', 'You are now impersonating ' . $owner->name);
    }
    public function stopImpersonate()
    {
        $adminId = session('impersonated_by');
        if (!$adminId) {
            return redirect()->route('dashboard');
        }

        $admin = \App\Models\User::withoutGlobalScope('tenant')->find($adminId);

        if (!$admin) {
            session()->forget('impersonated_by');
            return redirect()->route('login')->withErrors(['email' => 'Super Admin account not found.']);
        }

        session()->forget('impersonated_by');
        \Illuminate\Support\Facades\Auth::login($admin);

        return redirect()->route('super-admin.tenants.index')->with('status', 'Back to Super Admin panel.');
    }

    public function updateQuota(Request $request, Tenant $tenant)
    {
        $type = $request->input('type'); // 'locations' or 'users'
        $action = $request->input('action'); // 'inc' or 'dec'

        $column = $type === 'locations' ? 'extra_locations' : 'extra_users';
        $current = $tenant->$column;

        if ($action === 'inc') {
            $tenant->update([$column => $current + 1]);
        } else {
            $tenant->update([$column => max(0, $current - 1)]);
        }

        return back()->with('status', 'Quota updated successfully!');
    }

    public function import(Request $request, Tenant $tenant)
    {
        $request->validate(['file' => 'required|file']);
        
        // Switch to tenant context
        app(\App\Support\TenantManager::class)->setTenantId($tenant->id);

        // Simple mock of import logic for a CSV
        // In reality, move_uploaded_file and fgetcsv would go here
        
        return back()->with('status', 'Data import initiated for ' . $tenant->name);
    }

    public function addUser(Tenant $tenant)
    {
        $this->impersonate($tenant);
        return redirect()->route('users.create');
    }

    public function addLocation(Tenant $tenant)
    {
        $this->impersonate($tenant);
        return redirect()->route('locations.create');
    }

    public function export(Tenant $tenant)
    {
        // Simple CSV Export for Sales & Products
        $filename = "export_{$tenant->slug}_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($tenant) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['TYPE', 'DATA_1', 'DATA_2', 'DATA_3', 'DATA_4']);
            
            // Switch to tenant to fetch data
            app(\App\Support\TenantManager::class)->setTenantId($tenant->id);

            // Products
            fputcsv($file, ['--- PRODUCTS ---']);
            \App\Models\Product::all()->each(function($p) use ($file) {
                fputcsv($file, ['PRODUCT', $p->code, $p->name, $p->buy_price, $p->sell_price]);
            });

            // Sales (Limited to last 100 for safety in this demo)
            fputcsv($file, ['--- SALES ---']);
            \App\Models\Sale::latest()->limit(100)->get()->each(function($s) use ($file) {
                fputcsv($file, ['SALE', $s->invoice_no, $s->grand_total, $s->created_at]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
