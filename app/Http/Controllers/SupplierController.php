<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Supplier::create($data);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier berhasil dibuat.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier berhasil dihapus.');
    }
}
