@extends('layouts.app')

@section('title', __('adjustment.create_title'))
@section('page-title', __('adjustment.page_title'))

@section('content')
    <div class="max-w-6xl mx-auto space-y-8">
        <a href="{{ route('stock-adjustments.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-2 px-4 shadow-sm py-2 bg-white rounded-full">
            <i class="fa fa-arrow-left"></i> {{ __('adjustment.back_to_list') }}
        </a>

        <form method="POST" action="{{ route('stock-adjustments.store') }}" enctype="multipart/form-data" id="adj-form">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Left: Master Info -->
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card icon="info-circle" title="Informasi Penyesuaian">
                        <div class="space-y-6">
                            @if ($canManageAll)
                                <div class="space-y-2">
                                    <label for="location_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.location_inventory') }} <span class="text-rose-500">*</span></label>
                                    <select id="location_id" name="location_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all select2-basic" required>
                                        <option value="">{{ __('adjustment.select_loc') }}</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" {{ (int) old('location_id') === $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.location_inventory') }}</label>
                                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-xs">
                                            <i class="fa fa-map-marker"></i>
                                        </div>
                                        <span class="text-sm font-bold text-indigo-900">{{ $activeLocation->name }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label for="reason" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.adj_reason') }} <span class="text-rose-500">*</span></label>
                                <textarea id="reason" name="reason" rows="3" placeholder="{{ __('adjustment.reason_placeholder') }}" required
                                          class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">{{ old('reason') }}</textarea>
                                @error('reason') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="evidence" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('adjustment.supporting_doc') }}</label>
                                <label class="group relative flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 hover:bg-slate-50 hover:border-brand/30 transition-all cursor-pointer overflow-hidden">
                                    <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf" class="hidden" 
                                           onchange="document.getElementById('file-chosen').textContent = this.files[0].name.substring(0, 20) + (this.files[0].name.length > 20 ? '...' : '')">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-brand transition-all mb-3">
                                            <i class="fa fa-cloud-upload text-xl"></i>
                                        </div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors" id="file-chosen">
                                            {{ __('report.select_file') ?? 'Pilih File Bukti' }}
                                        </p>
                                        <p class="text-[8px] text-slate-300 font-bold mt-1 uppercase italic">JPG, PNG, atau PDF (Max 2MB)</p>
                                    </div>
                                </label>
                                @error('evidence') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </x-ui.card>

                    <div class="p-6 bg-brand rounded-[2.5rem] shadow-2xl shadow-brand/20">
                        <button type="submit" class="w-full py-5 bg-white text-brand rounded-2xl font-black text-xs uppercase tracking-[0.3em] hover:scale-[1.02] active:scale-95 transition-all">
                            {{ __('adjustment.save_adj') }}
                        </button>
                        <p class="text-[9px] text-white/60 font-medium text-center mt-4 uppercase tracking-widest">Pastikan data stok sudah sesuai sebelum disimpan.</p>
                    </div>
                </div>

                <!-- Right: Product Selector & List -->
                <div class="lg:col-span-2 space-y-8">
                    <x-ui.card icon="plus-circle" title="Pilih Produk & Detail Penyesuaian">
                        <div class="space-y-6">
                            <div class="flex gap-4 items-end">
                                <div class="flex-1 space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Pencarian Produk</label>
                                    <select id="product_selector" class="w-full select2-basic">
                                        <option value="">{{ __('adjustment.search_prod_sku') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-sku="{{ $product->sku }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="add-product-btn" class="px-8 py-3.5 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-brand-dark transition-all">
                                    Tambah
                                </button>
                            </div>

                            <div class="relative overflow-hidden rounded-2xl border border-slate-100">
                                <div class="overflow-x-auto overflow-y-auto scrollbar-custom" style="max-height: 480px;">
                                    <table class="w-full border-collapse min-w-[500px]">
                                        <thead class="sticky top-0 z-10 bg-white">
                                            <tr class="border-b border-slate-100 shadow-sm">
                                                <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-left bg-white/95 backdrop-blur-sm">Produk</th>
                                                <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">Status</th>
                                                <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">Jumlah</th>
                                                <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-16 bg-white/95 backdrop-blur-sm"></th>
                                            </tr>
                                        </thead>
                                    <tbody id="adj-items-body" class="divide-y divide-slate-50 italic text-slate-400">
                                        <tr id="empty-state">
                                            <td colspan="4" class="px-8 py-20 text-center">
                                                <div class="opacity-20 flex flex-col items-center">
                                                    <div class="w-16 h-16 border-4 border-slate-300 rounded-3xl flex items-center justify-center text-3xl mb-4 italic">📦</div>
                                                    <p class="text-sm font-black uppercase">Belum ada produk dipilih</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsBody = document.getElementById('adj-items-body');
            const emptyState = document.getElementById('empty-state');
            const productSelector = document.getElementById('product_selector');
            const addBtn = document.getElementById('add-product-btn');
            
            let itemIndex = 0;
            const items = new Set();

            addBtn.addEventListener('click', function() {
                const productId = productSelector.value;
                if (!productId) return;
                
                if (items.has(productId)) {
                    alert('Produk sudah ada di daftar.');
                    return;
                }

                const option = productSelector.options[productSelector.selectedIndex];
                const name = option.dataset.name;
                const sku = option.dataset.sku;

                if (emptyState) emptyState.remove();

                const row = document.createElement('tr');
                row.className = "hover:bg-slate-50/50 transition-colors group";
                row.dataset.id = productId;
                row.innerHTML = `
                    <td class="px-4 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-all">
                                <i class="fa fa-cube text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 leading-tight">${name}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">${sku}</p>
                                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-6 text-center">
                        <div class="flex items-center justify-center gap-2 p-1 bg-slate-50 rounded-xl w-fit mx-auto border border-slate-100">
                             <button type="button" class="type-toggle px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all bg-emerald-500 text-white" data-type="plus">+ In</button>
                             <button type="button" class="type-toggle px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600" data-type="minus">- Out</button>
                        </div>
                    </td>
                    <td class="px-4 py-6">
                        <div class="relative">
                            <input type="number" step="0.01" name="items[${itemIndex}][quantity_delta]" class="qty-field w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-center text-sm font-black text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all" value="1" required>
                        </div>
                    </td>
                    <td class="px-4 py-6 text-center">
                        <button type="button" class="remove-item text-slate-300 hover:text-rose-500 transition-colors">
                            <i class="fa fa-times-circle text-lg"></i>
                        </button>
                    </td>
                `;

                itemsBody.appendChild(row);
                items.add(productId);
                itemIndex++;
                productSelector.value = '';
                if (window.jQuery && $(productSelector).data('select2')) {
                    $(productSelector).trigger('change');
                }

                // Toggle logic
                const toggles = row.querySelectorAll('.type-toggle');
                const qtyInput = row.querySelector('.qty-field');
                
                toggles.forEach(t => t.addEventListener('click', function() {
                    toggles.forEach(b => {
                        b.classList.remove('bg-emerald-500', 'bg-rose-500', 'text-white');
                        b.classList.add('text-slate-400');
                    });
                    
                    const type = this.dataset.type;
                    this.classList.remove('text-slate-400');
                    if (type === 'plus') {
                        this.classList.add('bg-emerald-500', 'text-white');
                        if (parseFloat(qtyInput.value) < 0) qtyInput.value = Math.abs(parseFloat(qtyInput.value));
                    } else {
                        this.classList.add('bg-rose-500', 'text-white');
                        if (parseFloat(qtyInput.value) > 0) qtyInput.value = -Math.abs(parseFloat(qtyInput.value));
                    }
                }));

                qtyInput.addEventListener('input', function() {
                    const isMinus = row.querySelector('.type-toggle[data-type="minus"]').classList.contains('bg-rose-500');
                    let val = parseFloat(this.value || 0);
                    if (isMinus && val > 0) this.value = -val;
                    if (!isMinus && val < 0) this.value = Math.abs(val);
                });

                row.querySelector('.remove-item').addEventListener('click', function() {
                    row.remove();
                    items.delete(productId);
                    if (itemsBody.children.length === 0) {
                        itemsBody.appendChild(emptyState);
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
