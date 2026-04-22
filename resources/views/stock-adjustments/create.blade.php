@extends('layouts.app')

@section('title', __('adjustment.create_title'))
@section('page-title', __('adjustment.page_title'))

@section('content')
    <div class="space-y-8">
        <a href="{{ route('stock-adjustments.index') }}"
            class="inline-flex items-center gap-2 text-xs font-black text-slate-400 hover:text-brand transition-all uppercase tracking-[0.2em] mb-2 group">
            <i class="fa fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> {{ __('adjustment.back_to_list') }}
        </a>

        <form method="POST" action="{{ route('stock-adjustments.store') }}" enctype="multipart/form-data" id="adj-form">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Left: Master Info -->
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card icon="info-circle" title="{{ __('adjustment.info_card') }}">
                        <div class="space-y-6">
                            @if ($canManageAll)
                                <div class="space-y-2">
                                    <label for="location_id"
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.location_inventory') }}
                                        <span class="text-rose-500">*</span></label>
                                    <select id="location_id" name="location_id"
                                        class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all select2-basic"
                                        required>
                                        <option value="">{{ __('adjustment.select_loc') }}</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" {{ (int) old('location_id') === $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">
                                    {{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.location_inventory') }}</label>
                                    <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white text-xs">
                                            <i class="fa fa-map-marker"></i>
                                        </div>
                                        <span class="text-sm font-bold text-indigo-900">{{ $activeLocation->name }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label for="reason"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('adjustment.adj_reason') }}
                                    <span class="text-rose-500">*</span></label>
                                <textarea id="reason" name="reason" rows="3"
                                    placeholder="{{ __('adjustment.reason_placeholder') }}" required
                                    class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">{{ old('reason') }}</textarea>
                                @error('reason') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">
                                {{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="evidence"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('adjustment.supporting_doc') }}</label>
                                <label
                                    class="group relative flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 hover:bg-slate-50 hover:border-brand/30 transition-all cursor-pointer overflow-hidden">
                                    <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf"
                                        class="hidden"
                                        onchange="document.getElementById('file-chosen').textContent = this.files[0].name.substring(0, 20) + (this.files[0].name.length > 20 ? '...' : '')">
                                    <div class="flex flex-col items-center text-center">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 group-hover:text-brand transition-all mb-3">
                                            <i class="fa fa-cloud-upload text-xl"></i>
                                        </div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors"
                                            id="file-chosen">
                                            {{ __('report.select_file') ?? 'Select Evidence File' }}
                                        </p>
                                        <p class="text-[8px] text-slate-300 font-bold mt-1 uppercase italic">JPG, PNG, or
                                            PDF (Max 2MB)</p>
                                    </div>
                                </label>
                                @error('evidence') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">
                                {{ $message }}</p> @enderror
                            </div>
                        </div>
                    </x-ui.card>

                    <div class="p-6 bg-brand rounded-[2.5rem] shadow-2xl shadow-brand/20">
                        <button type="submit"
                            class="w-full py-5 bg-white text-brand rounded-2xl font-black text-xs uppercase tracking-[0.3em] hover:scale-[1.02] active:scale-95 transition-all">
                            {{ __('adjustment.save_adj') }}
                        </button>
                        <p class="text-[9px] text-white/60 font-medium text-center mt-4 uppercase tracking-widest">
                            {{ __('adjustment.save_confirmation') }}</p>
                    </div>
                </div>

                <!-- Right: Product Selector & List -->
                <div class="lg:col-span-2 space-y-8">
                    <x-ui.card icon="plus-circle" title="{{ __('adjustment.selection_card') }}">
                        <div class="space-y-6">
                            <!-- Product Hybrid Entry Grid (Ultra-Precise Alignment) -->
                            <div class="premium-entry-grid">
                                <!-- Row 1: Labels -->
                                <div class="grid-label-1">{{ __('adjustment.scan_label') }}</div>
                                <div class="grid-label-2">{{ __('adjustment.manual_label') }}</div>
                                <div class="grid-label-empty"></div>

                                <!-- Row 2: Inputs & Button -->
                                <div class="grid-input-1">
                                    <div class="premium-input-wrapper">
                                        <i class="fa fa-barcode premium-icon"></i>
                                        <input type="text" id="scan_input" autofocus autocomplete="off" 
                                               placeholder="{{ __('adjustment.scan_placeholder') }}" 
                                               class="premium-input">
                                        <div class="premium-badge">ENTER</div>

                                        <div id="search_results" class="search-results-overlay hidden">
                                            <div class="p-2 space-y-1" id="results_container"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid-input-2">
                                    <select id="product_selector" class="w-full select2-basic">
                                        <option value="">{{ __('adjustment.select_manual_placeholder') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" 
                                                    data-name="{{ $product->name }}" 
                                                    data-sku="{{ $product->sku }}"
                                                    data-barcode="{{ $product->barcode }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid-button">
                                    <button type="button" id="add-product-btn" class="premium-btn-add">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                                <div class="relative overflow-hidden rounded-2xl border border-slate-100">
                                    <div class="overflow-x-auto overflow-y-auto scrollbar-custom" style="max-height: 480px;">
                                        <table class="w-full border-collapse min-w-[500px]">
                                            <thead class="sticky top-0 z-10 bg-white">
                                                <tr class="border-b border-slate-100 shadow-sm">
                                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-left bg-white/95 backdrop-blur-sm">{{ __('adjustment.product') }}</th>
                                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">{{ __('adjustment.status') }}</th>
                                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">{{ __('adjustment.qty_delta') }}</th>
                                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-16 bg-white/95 backdrop-blur-sm"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="adj-items-body" class="divide-y divide-slate-50 italic text-slate-400">
                                                <tr id="empty-state">
                                                    <td colspan="4" class="px-8 py-20 text-center">
                                                        <div class="opacity-20 flex flex-col items-center">
                                                            <div class="w-16 h-16 border-4 border-slate-300 rounded-3xl flex items-center justify-center text-3xl mb-4 italic">📦</div>
                                                            <p class="text-sm font-black uppercase">{{ __('adjustment.empty_product_state') }}</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </x-ui.card>
                    </div>
                </div>
            </form>
        </div>

        @push('styles')
            <style>
                /* Premium Grid System for Absolute Alignment */
        .premium-entry-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr 56px !important;
            grid-template-rows: auto auto !important;
            gap: 12px 16px !important;
            width: 100% !important;
            margin-bottom: 2rem !important;
        }
        .grid-label-1, .grid-label-2 {
            font-size: 9px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            color: #64748b !important;
            padding-left: 2px !important;
            align-self: end !important;
        }
        .premium-input-wrapper {
            position: relative !important;
            height: 56px !important;
        }
        .premium-input {
            width: 100% !important;
            height: 56px !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 20px !important;
            padding: 0 80px 0 60px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            outline: none !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            line-height: 54px !important;
        }
        .premium-input:focus {
            background-color: #ffffff !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.05) !important;
        }
        .premium-icon {
            position: absolute !important;
            left: 24px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #10b981 !important;
            opacity: 0.5 !important;
            font-size: 20px !important;
            z-index: 10 !important;
        }
        .premium-badge {
            position: absolute !important;
            right: 16px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background-color: #e2e8f0 !important;
            color: #64748b !important;
            font-size: 8px !important;
            font-weight: 900 !important;
            padding: 6px 10px !important;
            border-radius: 8px !important;
            text-transform: uppercase !important;
            z-index: 10 !important;
            pointer-events: none !important;
        }
        .search-results-overlay {
            position: absolute !important;
            z-index: 50 !important;
            left: 0 !important;
            right: 0 !important;
            margin-top: 12px !important;
            background-color: #ffffff !important;
            border-radius: 20px !important;
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
            overflow: hidden !important;
            padding: 4px !important;
        }

        .premium-btn-add {
            width: 56px !important;
            height: 56px !important;
            background-color: #10b981 !important;
            color: #ffffff !important;
            border-radius: 20px !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 26px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2) !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
            font-weight: 900 !important;
        }
        .premium-btn-add:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.3) !important;
        }

        /* Select2 Overrides - Ultra-Precise Height Parity */
        .select2-container {
            height: 56px !important;
        }
        .select2-container--default .select2-selection--single {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 20px !important;
            height: 56px !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 16px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #334155 !important;
            transition: all 0.2s ease !important;
            outline: none !important;
            box-sizing: border-box !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            background-color: #ffffff !important;
            border-color: #10b981 !important;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.05) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #94a3b8 !important;
            padding-left: 8px !important;
            text-transform: none !important; /* Forces normal case */
            font-size: 14px !important;
            line-height: 54px !important;
            margin: 0 !important;
            font-weight: 700 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 54px !important;
            right: 16px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #94a3b8 transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
            position: relative !important;
            top: auto !important;
            margin: 0 !important;
            left: auto !important;
        }
        .select2-dropdown {
            border-radius: 20px !important;
            border: 1px solid #f1f5f9 !important;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
            overflow: hidden !important;
            margin-top: 8px !important;
            z-index: 9999 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: #10b981 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            text-transform: none !important;
        }
            </style>
        @endpush

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Init Select2
                    $('.select2-basic').select2({
                        width: '100%',
                        placeholder: '{{ __('adjustment.select_manual_placeholder') }}'
                    });

                    const itemsBody = document.getElementById('adj-items-body');
                    const emptyState = document.getElementById('empty-state');
                    const productSelector = document.getElementById('product_selector');
                    const scanInput = document.getElementById('scan_input');
                    const searchResults = document.getElementById('search_results');
                    const resultsContainer = document.getElementById('results_container');
                    const addBtn = document.getElementById('add-product-btn');

                    let itemIndex = 0;
                    const items = new Set();
                    const productMap = new Map();

                    // Initialize product map
                    Array.from(productSelector.options).forEach(opt => {
                        if (opt.value) {
                            productMap.set(opt.value, {
                                id: opt.value,
                                name: opt.dataset.name,
                                sku: opt.dataset.sku,
                                barcode: opt.dataset.barcode
                            });
                        }
                    });

                    // Search Logic
                    let searchTimeout;
                    if (scanInput) {
                        scanInput.addEventListener('input', function() {
                            clearTimeout(searchTimeout);
                            const query = this.value.toLowerCase().trim();
                            if (query.length < 2) { searchResults.classList.add('hidden'); return; }

                            searchTimeout = setTimeout(() => {
                                const matches = Array.from(productMap.values()).filter(p => 
                                    p.name.toLowerCase().includes(query) || 
                                    p.sku.toLowerCase().includes(query) || 
                                    (p.barcode && p.barcode.toLowerCase().includes(query))
                                ).slice(0, 5);
                                renderResults(matches);
                            }, 200);
                        });

                        function renderResults(matches) {
                            resultsContainer.innerHTML = '';
                            if (matches.length === 0) { searchResults.classList.add('hidden'); return; }
                            matches.forEach(p => {
                                const div = document.createElement('div');
                                div.className = "flex items-center justify-between p-4 hover:bg-slate-50 cursor-pointer rounded-2xl transition-all group text-left";
                                div.innerHTML = `
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-all">
                                            <i class="fa fa-cube"></i>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-bold text-slate-700">${p.name}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase leading-none mt-1">${p.sku}</p>
                                        </div>
                                    </div>
                                    <i class="fa fa-plus-circle text-slate-200 group-hover:text-brand text-xl transition-all"></i>
                                `;
                                div.onclick = () => { addProductToList(p.id); scanInput.value = ''; searchResults.classList.add('hidden'); scanInput.focus(); };
                                resultsContainer.appendChild(div);
                            });
                            searchResults.classList.remove('hidden');
                        }

                        scanInput.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const firstResult = resultsContainer.querySelector('div');
                                if (firstResult) firstResult.click();
                            }
                        });
                    }

                    addBtn.addEventListener('click', function() {
                        const productId = productSelector.value;
                        if (productId) {
                            addProductToList(productId);
                            $(productSelector).val(null).trigger('change');
                        }
                    });

                    function addProductToList(productId) {
                        if (!productId || items.has(productId)) return;

                        const p = productMap.get(productId);
                        if (!p) return;
                        if (emptyState && itemsBody.contains(emptyState)) emptyState.remove();

                        const row = document.createElement('tr');
                        row.className = "hover:bg-slate-50/50 transition-colors group";
                        row.dataset.id = productId;
                        row.innerHTML = `
                            <td class="px-4 py-8">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400 border border-slate-100 group-hover:bg-brand/5 group-hover:text-brand transition-all">
                                        <i class="fa fa-cube text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800 leading-tight">${p.name}</p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1">${p.sku}</p>
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-8 text-center">
                                <div class="flex items-center justify-center p-1 bg-slate-100/50 rounded-2xl w-fit mx-auto border border-slate-100 shadow-inner">
                                     <button type="button" class="type-toggle h-11 px-6 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-emerald-500 text-white shadow-lg shadow-emerald-500/20" data-type="plus">IN</button>
                                     <button type="button" class="type-toggle h-11 px-6 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-400" data-type="minus">OUT</button>
                                </div>
                            </td>
                            <td class="px-4 py-8">
                                <div class="max-w-[120px] mx-auto">
                                    <input type="number" step="1" name="items[${itemIndex}][quantity_delta]" class="qty-field w-full h-11 bg-slate-50 border-none rounded-xl px-4 text-center text-sm font-black text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all shadow-inner" value="1" required>
                                </div>
                            </td>
                            <td class="px-4 py-8 text-center">
                                <button type="button" class="remove-item w-11 h-11 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center border-none shadow-sm">
                                    <i class="fa fa-trash text-sm"></i>
                                </button>
                            </td>
                        `;

                        itemsBody.appendChild(row);
                        items.add(productId);
                        itemIndex++;

                        // Toggle Logic
                        const toggles = row.querySelectorAll('.type-toggle');
                        const qtyInput = row.querySelector('.qty-field');

                        toggles.forEach(t => t.addEventListener('click', function() {
                            toggles.forEach(b => {
                                b.classList.remove('bg-emerald-500', 'bg-rose-500', 'text-white', 'shadow-lg', 'shadow-emerald-500/20', 'shadow-rose-500/20');
                                b.classList.add('text-slate-400');
                            });

                            const type = this.dataset.type;
                            this.classList.remove('text-slate-400');
                            if (type === 'plus') {
                                this.classList.add('bg-emerald-500', 'text-white', 'shadow-lg', 'shadow-emerald-500/20');
                                if (parseFloat(qtyInput.value) < 0) qtyInput.value = Math.abs(parseFloat(qtyInput.value));
                            } else {
                                this.classList.add('bg-rose-500', 'text-white', 'shadow-lg', 'shadow-rose-500/20');
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
                            if (itemsBody.children.length === 0) itemsBody.appendChild(emptyState);
                        });
                    }
                });
            </script>
        @endpush
@endsection
