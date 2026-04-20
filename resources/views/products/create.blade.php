@extends('layouts.app')

@section('title', 'Tambah Produk Baru')
@section('page-title', 'Produk')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-ui.card icon="plus" title="Informasi Produk Baru">
            <x-slot name="actions">
                <a href="{{ route('products.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">Batal & Kembali</a>
            </x-slot>

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
                        <label for="name" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">Nama Produk</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap produk" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all" required>
                        @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section: Klasifikasi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pb-8 border-b border-slate-50">
                    <div class="space-y-2">
                        <label for="category_id" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">Kategori</label>
                        <select id="category_id" name="category_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer">
                            <option value="">Pilih Kategori...</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (int) old('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="unit_id" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">Satuan Dasar</label>
                        <select id="unit_id" name="unit_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer" required>
                            <option value="" disabled {{ old('unit_id') ? '' : 'selected' }}>Pilih Satuan...</option>
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
                        <label for="sale_price" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">Harga Jual (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</span>
                            <input type="number" id="sale_price" name="sale_price" value="{{ old('sale_price', 0) }}" min="0" step="0.01" 
                                   class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 transition-all" required>
                        </div>
                        @error('sale_price') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="cost_price" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">HPP / Modal (Rp)</label>
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
                            <p class="text-xs font-bold text-slate-800">Kena PPN</p>
                            <p class="text-[10px] text-slate-400">Pajak Pertambahan Nilai</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer group hover:bg-amber-50 transition-colors">
                        <input type="checkbox" name="block_when_out_of_stock" value="1" {{ old('block_when_out_of_stock') ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-800">Blokir Stok Nol</p>
                            <p class="text-[10px] text-slate-400">Cegah jual saat stok habis</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer group hover:bg-brand/5 transition-colors">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-800">Produk Aktif</p>
                            <p class="text-[10px] text-slate-400">Tampilkan di daftar POS</p>
                        </div>
                    </label>
                </div>

                <div class="pt-8 flex justify-end gap-3">
                    <button type="submit" class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-bold text-sm shadow-xl hover:bg-slate-800 transition-all active:scale-[0.98]">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
