<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Owner|Manager|KepalaToko');
    }

    public function index(): View
    {
        $search = trim(request()->string('search')->toString());

        $products = Product::query()
            ->with(['category', 'unit'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('barcode', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $units = Unit::query()->orderBy('name')->get();

        return view('products.create', compact('categories', 'units'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['block_when_out_of_stock'] = (bool) ($data['block_when_out_of_stock'] ?? false);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $units = Unit::query()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['is_taxable'] = (bool) ($data['is_taxable'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['block_when_out_of_stock'] = (bool) ($data['block_when_out_of_stock'] ?? false);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }
}
