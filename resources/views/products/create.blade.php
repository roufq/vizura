@extends('layouts.app')

@section('title', __('product.create_title'))
@section('page-title', __('product.page_title'))

@section('content')
    <div class="space-y-8">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-xs font-black text-slate-400 hover:text-brand transition-all uppercase tracking-[0.2em] mb-2 group">
            <i class="fa fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> {{ __('app.back_to_list') }}
        </a>

        <x-ui.card icon="plus" title="{{ __('product.info_header') }}">

            <form method="POST" action="{{ route('products.store') }}" class="space-y-8">
                @csrf

                <!-- Section: Identitas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-8 border-b border-slate-50">
                    <div class="space-y-2">
                        <label for="sku" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">SKU (Stock Keeping Unit)</label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku') }}" placeholder="Contoh: PRD-001" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all" required>
                        @error('sku') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="barcode" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">Barcode (Opsional)</label>
                        <input type="text" id="barcode" name="barcode" value="{{ old('barcode') }}" placeholder="Scan atau input barcode" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all">
                        @error('barcode') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label for="name" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('product.product_name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('product.product_name_placeholder') }}" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all" required>
                        @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Klasifikasi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-8 border-b border-slate-50">
                    <div class="space-y-2">
                        <label for="category_id" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('product.category_label') }}</label>
                        <select id="category_id" name="category_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer">
                            <option value="">{{ __('product.select_category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="unit_id" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('product.unit_label') }}</label>
                        <select id="unit_id" name="unit_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer" required>
                            <option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>{{ __('product.select_unit') }}</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ (int) old('unit_id') === $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}{{ $unit->abbreviation ? ' ('.$unit->abbreviation.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Harga -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-8 border-b border-slate-50">
                    <div class="space-y-2">
                        <label for="sale_price" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('product.sale_price') }} (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}" min="0" step="0.01" 
                                   class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 transition-all" required>
                        </div>
                        @error('sale_price') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="cost_price" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('product.cost_price_label') }} (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price', 0) }}" min="0" step="0.01" 
                                   class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-500 focus:ring-2 focus:ring-brand/20 transition-all" required>
                        </div>
                        @error('cost_price') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Kontrol & Switch -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer group hover:bg-emerald-50 transition-colors">
                        <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable') ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-800">{{ __('product.is_taxable') }}</p>
                            <p class="text-[10px] text-slate-400">{{ __('product.tax_desc') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer group hover:bg-amber-50 transition-colors">
                        <input type="checkbox" name="block_when_out_of_stock" value="1" {{ old('block_when_out_of_stock') ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-800">{{ __('product.block_out_of_stock') }}</p>
                            <p class="text-[10px] text-slate-400">{{ __('product.block_stock_desc') }}</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer group hover:bg-brand/5 transition-colors">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-800">{{ __('product.is_active_label') }}</p>
                            <p class="text-[10px] text-slate-400">{{ __('product.active_desc') }}</p>
                        </div>
                    </label>
                </div>

                <div class="pt-8 flex justify-end gap-3">
                    <button type="submit" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-bold text-sm shadow-xl hover:bg-slate-800 transition-all active:scale-[0.98]">
                        {{ __('product.save_product') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
