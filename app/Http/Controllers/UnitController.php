<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $units = Unit::query()
            ->orderBy('name')
            ->paginate(10);

        return view('units.index', compact('units'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        Unit::create($data);

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil dibuat.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $unit->update($data);

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('status', 'Satuan berhasil dihapus.');
    }
}
