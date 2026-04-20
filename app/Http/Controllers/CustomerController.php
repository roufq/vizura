<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|Kasir');
    }

    public function index(): View
    {
        $search = trim(request()->string('search')->toString());

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        Customer::create($data);

        return redirect()
            ->route('customers.index')
            ->with('status', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $sales = $customer->sales()->with(['cashier'])->latest()->paginate(10);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $customer->update($data);

        return redirect()
            ->route('customers.index')
            ->with('status', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        // Optional: Check if customer has transactions
        if ($customer->sales()->exists()) {
            return redirect()
                ->route('customers.index')
                ->withErrors(['customer' => 'Pelanggan tidak bisa dihapus karena memiliki riwayat transaksi.']);
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('status', 'Data pelanggan berhasil dihapus.');
    }
}
