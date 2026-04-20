@extends('layouts.app')

@section('title', __('purchase.create_new_title'))
@section('page-title', __('purchase.page_title'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            <x-ui.card icon="plus" title="{{ __('purchase.entry_title') }}">
                <x-slot name="actions">
                    <a href="{{ route('purchases.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest px-4">{{ __('purchase.batal') }}</a>
                </x-slot>

                <form method="POST" action="{{ route('purchases.store') }}" id="purchase-form" class="space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="reference_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('purchase.po_invoice') }}</label>
                            <input type="text" id="reference_no" name="reference_no" value="{{ old('reference_no', $referenceNo) }}" 
                                   class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all" required>
                            @error('reference_no') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="supplier_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('purchase.supplier') }}</label>
                            <select id="supplier_id" name="supplier_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                                <option value="">{{ __('purchase.select_supplier') }} ({{ __('report.optional') }})</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (int) old('supplier_id') === $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="payment_method" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('purchase.payment_method') }}</label>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach(['payable' => __('purchase.payable'), 'cash' => __('purchase.cash'), 'bank' => __('purchase.bank')] as $val => $lbl)
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $val }}" {{ old('payment_method', 'payable') === $val ? 'checked' : '' }} class="peer hidden">
                                    <div class="p-4 rounded-2xl border-2 border-slate-50 bg-slate-50 text-slate-400 font-bold text-xs text-center peer-checked:border-brand peer-checked:bg-white peer-checked:text-brand transition-all">
                                        {{ $lbl }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 overflow-x-auto -mx-8">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-y border-slate-100">
                                <tr>
                                    <th class="px-8 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('purchase.product') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center" style="width:120px">{{ __('purchase.qty') }}</th>
                                    <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right" style="width:180px">{{ __('purchase.cost') }}</th>
                                    <th class="px-8 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center" style="width:80px"></th>
                                </tr>
                            </thead>
                            <tbody id="purchase-items" class="divide-y divide-slate-50">
                                @php $items = old('items', [ [] ]); @endphp
                                @foreach ($items as $index => $item)
                                    <tr data-index="{{ $index }}" class="hover:bg-slate-50/50 transition-all">
                                        <td class="px-8 py-4">
                                            <select name="items[{{ $index }}][product_id]" class="w-full bg-transparent border-none text-sm font-bold text-slate-700 js__select2">
                                                <option value="">{{ __('purchase.search_product') }}</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" {{ (int) ($item['product_id'] ?? 0) === $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} ({{ $product->sku }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <input type="number" step="0.01" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" 
                                                   class="w-full bg-slate-50 border-none rounded-xl px-2 py-2 text-center text-sm font-black text-slate-700">
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="number" step="0.01" name="items[{{ $index }}][unit_cost]" value="{{ $item['unit_cost'] ?? '' }}" 
                                                   class="w-full bg-slate-50 border-none rounded-xl px-4 py-2 text-right text-sm font-black text-brand italic">
                                        </td>
                                        <td class="px-8 py-4 text-center">
                                            <button type="button" class="text-slate-300 hover:text-rose-600 js-remove-row transition-colors">
                                                <i class="fa fa-times-circle text-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button type="button" id="add-item-row" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200">
                            + {{ __('purchase.add_row') }}
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="lg:col-span-4 space-y-6">
            <x-ui.card icon="calculator" title="{{ __('purchase.summary_title') }}">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label for="discount_amount" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('purchase.discount_label') }}</label>
                        <input type="number" id="discount_amount" name="discount_amount" form="purchase-form" value="{{ old('discount_amount', 0) }}" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all">
                    </div>

                    <div class="space-y-2">
                        <label for="tax_amount" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('purchase.tax_label') }}</label>
                        <input type="number" id="tax_amount" name="tax_amount" form="purchase-form" value="{{ old('tax_amount', 0) }}" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                    </div>

                    <div class="pt-6 border-t border-slate-50 space-y-4">
                        <div class="flex justify-between items-center opacity-50">
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ __('purchase.subtotal_label') }}</span>
                            <span class="text-sm font-bold" id="purchase-subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center text-brand">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] italic">{{ __('purchase.total_billing') }}</p>
                            <h2 class="text-3xl font-black tracking-tighter" id="purchase-total">Rp 0</h2>
                        </div>
                    </div>

                    <button type="submit" form="purchase-form" class="w-full py-5 bg-brand text-white rounded-3xl font-black text-sm uppercase tracking-widest shadow-2xl shadow-brand/40 hover:scale-[1.02] active:scale-95 transition-all">
                        {{ __('purchase.save_purchase') }}
                    </button>
                </div>
            </x-ui.card>

            <div class="bg-indigo-50 p-6 rounded-[2rem] border border-indigo-100 flex items-start gap-4">
                <div class="p-3 bg-white rounded-xl shadow-sm"><i class="fa fa-info-circle text-indigo-500"></i></div>
                <div>
                    <h5 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-1">{{ __('purchase.stock_impact_title') }}</h5>
                    <p class="text-[10px] text-indigo-600 font-bold leading-relaxed">{{ __('purchase.stock_impact_desc') }}</p>
                </div>
            </div>
        </div>
    </div>

    <template id="purchase-item-template">
        <tr data-index="__INDEX__" class="hover:bg-slate-50/50 transition-all">
            <td class="px-8 py-4">
                <select name="items[__INDEX__][product_id]" class="w-full bg-transparent border-none text-sm font-bold text-slate-700 js__select1">
                    <option value="">{{ __('purchase.search_product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
            </td>
            <td class="px-6 py-4 text-center">
                <input type="number" step="0.01" name="items[__INDEX__][quantity]" class="w-full bg-slate-50 border-none rounded-xl px-2 py-2 text-center text-sm font-black text-slate-700">
            </td>
            <td class="px-6 py-4 text-right">
                <input type="number" step="0.01" name="items[__INDEX__][unit_cost]" class="w-full bg-slate-50 border-none rounded-xl px-4 py-2 text-right text-sm font-black text-brand italic">
            </td>
            <td class="px-8 py-4 text-center">
                <button type="button" class="text-slate-300 hover:text-rose-600 js-remove-row transition-colors">
                    <i class="fa fa-times-circle text-lg"></i>
                </button>
            </td>
        </tr>
    </template>

    <script>
        (function () {
            const tableBody = document.getElementById('purchase-items');
            const addButton = document.getElementById('add-item-row');
            const template = document.getElementById('purchase-item-template');
            const subtotalDisp = document.getElementById('purchase-subtotal');
            const totalDisp = document.getElementById('purchase-total');
            const discInput = document.getElementById('discount_amount');
            const taxInput = document.getElementById('tax_amount');

            let nextIndex = tableBody.querySelectorAll('tr').length;
            const fmt = v => 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(v);

            function update() {
                let st = 0;
                tableBody.querySelectorAll('tr').forEach(r => {
                    const q = parseFloat(r.querySelector('input[name*="[quantity]"]').value || 0);
                    const c = parseFloat(r.querySelector('input[name*="[unit_cost]"]').value || 0);
                    st += q * c;
                });
                const d = parseFloat(discInput.value || 0);
                const t = parseFloat(taxInput.value || 0);
                const tot = Math.max(0, st - d + t);
                subtotalDisp.textContent = fmt(st);
                totalDisp.textContent = fmt(tot);
            }

            addButton.addEventListener('click', () => {
                const html = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
                const div = document.createElement('tbody');
                div.innerHTML = html.trim();
                tableBody.appendChild(div.firstChild);
                update();
            });

            tableBody.addEventListener('input', e => { if (e.target.tagName === 'INPUT') update(); });
            tableBody.addEventListener('click', e => {
                const btn = e.target.closest('.js-remove-row');
                if (btn && tableBody.querySelectorAll('tr').length > 1) { btn.closest('tr').remove(); update(); }
            });
            discInput.addEventListener('input', update);
            taxInput.addEventListener('input', update);
            update();
        })();
    </script>
@endsection
