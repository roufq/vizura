@extends('layouts.app')

@section('title', __('pos.terminal_title'))
@section('page-title', __('pos.terminal_title'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 -mt-2">
        <!-- Input & Cart Area -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- POS Hybrid Entry Grid (Executive Soft) -->
            <div class="premium-pos-grid">
                <!-- Barcode & SKU Entry -->
                <div class="premium-input-wrapper group">
                    <i class="fa fa-barcode premium-icon"></i>
                    <input type="text" id="scan_input" autofocus autocomplete="off" 
                           placeholder="{{ __('pos.scan_placeholder') }}" 
                           class="premium-input">
                    <div class="premium-badge">ENTER</div>
                    
                    <div id="search_results" class="search-results-overlay hidden">
                        <div class="p-2 space-y-1" id="results_container"></div>
                    </div>
                </div>

                <!-- Manual Product Search -->
                <div class="manual-search-wrapper">
                    <select id="product_select" class="w-full select2-pos">
                        <option value="">{{ __('pos.select_product') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Dynamic Action Button -->
                <button type="button" id="add_manual" class="premium-btn-add">
                    <i class="fa fa-plus"></i>
                </button>
            </div>

            <!-- High-Precision Context Toolbar -->
            <div class="bg-white/40 backdrop-blur-xl px-10 py-6 rounded-[2.5rem] border border-slate-100/50 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex flex-wrap items-center gap-12">
                    <!-- Order Type Selector -->
                    <div class="flex flex-col gap-3">
                        <span class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-400 ml-5 block">{{ __('pos.order_type') }}</span>
                        <div class="bg-slate-100/50 p-1 rounded-full flex items-center gap-1 border border-slate-100">
                            <button type="button" class="order-type-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all bg-white shadow-xl shadow-slate-200/40 text-brand ring-1 ring-slate-100" data-value="retail">{{ __('pos.retail') }}</button>
                            <button type="button" class="order-type-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600" data-value="wholesale">{{ __('pos.wholesale') }}</button>
                            <button type="button" class="order-type-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600" data-value="online">{{ __('pos.online') }}</button>
                        </div>
                    </div>

                    <!-- Separator -->
                    <div class="hidden md:block w-px h-12 bg-slate-100"></div>

                    <!-- Tax Configuration -->
                    <div class="flex flex-col gap-3">
                        <span class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-400 ml-5 block">{{ __('pos.tax_mode') }}</span>
                        <div class="bg-slate-100/50 p-1 rounded-full flex items-center gap-1 border border-slate-100">
                            <button type="button" class="tax-mode-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all bg-white shadow-xl shadow-slate-200/40 text-brand ring-1 ring-slate-100" data-inc="0" data-rate="0">{{ __('pos.tax_none') }}</button>
                            <button type="button" class="tax-mode-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600" data-inc="1" data-rate="11">{{ __('pos.tax_inc') }}</button>
                            <button type="button" class="tax-mode-btn px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600" data-inc="0" data-rate="11">{{ __('pos.tax_exc') }}</button>
                        </div>
                    </div>
                </div>

                <!-- View Preferences -->
                <div class="flex flex-col items-center md:items-end gap-3">
                    <span class="text-[9px] font-black uppercase tracking-[0.4em] text-slate-300 mr-5 block">{{ __('pos.view_mode') }}</span>
                    <div class="bg-slate-100/50 p-1.5 rounded-2xl flex items-center gap-1 border border-slate-100">
                        <button type="button" id="toggle-list-view" class="w-12 h-12 flex items-center justify-center rounded-xl text-brand bg-white shadow-lg shadow-slate-200/30 transition-all"><i class="fa fa-list"></i></button>
                        <button type="button" id="toggle-grid-view" class="w-12 h-12 flex items-center justify-center rounded-xl text-slate-300 hover:text-brand transition-all"><i class="fa fa-th"></i></button>
                    </div>
                </div>
            </div>

            <!-- Cart Table -->
            <x-ui.card icon="shopping-basket" title="{{ __('pos.cart_title') }}">
                <x-slot name="actions">
                     <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Lokasi: {{ $location->name }}</span>
                </x-slot>

                <div class="overflow-x-auto -mx-8">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-y border-slate-100">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('app.product') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('pos.qty') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('pos.price') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('app.discount') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('pos.subtotal') }}</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="cart-body" class="divide-y divide-slate-50">
                            <!-- JS Inject -->
                        </tbody>
                    </table>
                </div>

                <div id="cart-empty-state" class="py-24 text-center">
                    <div class="text-6xl mb-6 grayscale">🛒</div>
                    <h4 class="text-lg font-black text-slate-300 uppercase tracking-widest">{{ __('pos.empty_cart') }}</h4>
                    <p class="text-sm text-slate-400 font-medium">{{ __('pos.empty_cart_sub') }}</p>
                </div>

                <div id="stock-warning" class="mt-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl hidden items-center gap-3 text-rose-700">
                    <i class="fa fa-exclamation-triangle"></i>
                    <p class="text-xs font-bold">{{ __('pos.insufficient_stock_warning') }}</p>
                </div>
            </x-ui.card>

            <!-- Bottom Actions -->
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest px-4">{{ __('pos.cancel_back') }}</a>
                <div class="flex items-center gap-3">
                    <button type="button" class="js-reset-pos px-8 py-4 bg-rose-50 text-rose-600 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-100 transition-all">
                        {{ __('pos.reset_pos') }}
                    </button>
                    <button type="submit" name="action" value="draft" form="pos-form" class="px-8 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">
                        {{ __('pos.save_draft') }}
                    </button>
                </div>
            </div>

            <!-- Transaction Notes (Repositioned) -->
            <div id="pos-extra" class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3">
                    <i class="fa fa-comment-dots text-slate-300"></i>
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('pos.transaction_notes') }}</label>
                </div>
                <textarea name="notes" form="pos-form" rows="2" placeholder="{{ __('pos.notes_placeholder') }}" 
                          class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all">{{ $draft?->notes }}</textarea>
            </div>
        </div>

        <!-- Sidebar Summary & Payment Area -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Customer Card (Improved with Select2) -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand/10 flex items-center justify-center text-brand">
                        <i class="fa fa-user"></i>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('pos.customer_info') }}</h4>
                        <p class="text-xs font-bold text-slate-700">{{ __('pos.select_or_add_customer') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <input type="hidden" name="customer_id" id="customer-id-input" form="pos-form" value="{{ $draft?->customer_id }}">
                    
                    <div class="relative group">
                        <select name="customer_name" id="customer-select" form="pos-form" 
                                class="w-full select2-customer">
                            <option value="">{{ __('pos.general_customer') }}</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->name }}" 
                                        data-id="{{ $c->id }}" 
                                        data-phone="{{ $c->phone }}"
                                        {{ (string) $draft?->customer_name === (string) $c->name ? 'selected' : '' }}>
                                    {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
                                </option>
                            @endforeach
                            @if($draft?->customer_name && !$customers->contains('name', $draft->customer_name))
                                <option value="{{ $draft->customer_name }}" selected>{{ $draft->customer_name }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400">
                            <i class="fa fa-phone text-xs"></i>
                        </div>
                        <input type="text" name="customer_phone" id="customer-phone" form="pos-form" 
                            placeholder="{{ __('pos.customer_phone') }}" 
                            value="{{ $draft?->customer_phone }}"
                            class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-14 pr-6 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all">
                    </div>
                    
                    <div class="p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50">
                        <p class="text-[10px] text-blue-600 leading-relaxed font-medium">
                            <i class="fa fa-info-circle mr-1"></i>
                            {{ __('pos.customer_info_hint') }}
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Reference Info -->
            <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand/20 rounded-full blur-2xl"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2">{{ __('pos.invoice_no') }}</p>
                <h2 class="text-2xl font-black tracking-tight mb-6">{{ $referenceNo }}</h2>
                
                <div class="space-y-4 pt-6 border-t border-slate-800">
                    <div class="flex justify-between items-center opacity-60">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('pos.subtotal') }}</span>
                        <span class="text-sm font-bold" id="subtotal_display">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-rose-400">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('pos.order_discount') }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold">Rp</span>
                            <input type="number" id="order_discount" name="order_discount" form="pos-form" value="{{ $draft?->order_discount ?? 0 }}" 
                                   class="w-24 bg-slate-800/50 border-none rounded-lg px-2 py-1 text-right text-xs font-bold text-rose-400 focus:ring-1 focus:ring-rose-500">
                        </div>
                    </div>
                    <div class="flex justify-between items-center opacity-60">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ __('pos.tax') }} (<span id="tax_rate_val">0</span>%)</span>
                        <div class="flex items-center gap-2">
                            <input type="number" id="tax_rate" name="tax_rate" form="pos-form" value="{{ $draftTaxRate ?? 0 }}" 
                                   class="w-16 bg-slate-800/50 border-none rounded-lg px-2 py-1 text-right text-xs font-bold text-white focus:ring-1 focus:ring-brand">
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-brand/20">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-brand">{{ __('pos.total_to_pay') }}</p>
                    <h1 class="text-5xl font-black tracking-tighter mt-1" id="total_display">Rp 0</h1>
                </div>
            </div>

            <!-- Payment Matrix -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">{{ __('pos.payment') }}</h4>
                    <button type="button" id="set_exact_payment" class="text-[10px] font-black text-brand uppercase tracking-[0.2em] px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-all">{{ __('pos.exact_payment') }}</button>
                </div>

                <div id="payment-rows" class="space-y-4">
                    <!-- JS Inject -->
                </div>

                <button type="button" id="add_payment_row" class="w-full py-4 bg-slate-50 text-slate-400 rounded-2xl border-2 border-dashed border-slate-100/80 text-[10px] font-black uppercase tracking-[0.3em] hover:bg-slate-100 hover:text-slate-600 transition-all flex items-center justify-center gap-2">
                    <i class="fa fa-plus-circle opacity-30"></i>
                    {{ __('pos.add_payment_method') }}
                </button>

                <div class="space-y-4 pt-6 border-t border-slate-50">
                    <div class="flex justify-between items-center px-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">{{ __('pos.paid') ?? 'PAID' }}</span>
                        <span class="text-sm font-black text-slate-600" id="paid_display">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100/50 text-brand">
                        <span class="text-[10px] font-black uppercase tracking-[0.5em]">{{ __('pos.change_due') ?? 'CHANGE DUE' }}</span>
                        <span class="text-2xl font-black" id="change_display">Rp 0</span>
                    </div>
                </div>

                <div id="payment-warning" class="p-4 bg-rose-50 text-rose-600 rounded-2xl text-[9px] font-black hidden text-center uppercase tracking-[0.2em] border border-rose-100">
                    <i class="fa fa-exclamation-circle mr-1"></i>
                    {{ __('pos.payment_warning') }}
                </div>

                <form method="POST" action="{{ route('sales.store') }}" id="pos-form">
                    @csrf
                    @if (isset($draft) && $draft)
                        <input type="hidden" name="draft_id" value="{{ $draft->id }}">
                    @endif
                    <input type="hidden" name="type" id="order_type_input" value="sale">
                    <input type="hidden" name="reference_no" value="{{ $referenceNo }}">
                    
                    <button type="submit" name="action" value="post" id="pay_button" class="w-full py-6 bg-brand text-white rounded-3xl font-black text-lg shadow-2xl shadow-brand/40 hover:scale-[1.02] active:scale-100 transition-all disabled:opacity-30 disabled:grayscale disabled:scale-100">
                        {{ __('pos.process_payment') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Browser (Overlay Mode) -->
    <div id="product-browser-container" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" id="close-browser"></div>
        <div class="relative w-full max-w-6xl h-full bg-slate-50 rounded-[3rem] shadow-2xl overflow-hidden flex flex-col">
            <!-- Browser Header -->
            <div class="bg-white px-10 py-8 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('pos.product_catalog') }}</h3>
                    <p class="text-sm font-medium text-slate-400">{{ __('pos.catalog_sub') }}</p>
                </div>
                <div class="flex-1 px-8 py-2">
                    <div class="relative group">
                        <input type="text" id="browser-search" placeholder="{{ __('pos.search_product') }}" class="w-full bg-white border-slate-100 rounded-2xl pl-12 pr-6 py-3 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all border shadow-sm">
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                </div>
                <button type="button" id="close-browser-btn" class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="fa fa-times text-xl"></i>
                </button>
            </div>

            <!-- Category Sidebar & Grid Content -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Categories -->
                <div class="w-64 bg-slate-50 border-r border-slate-100 overflow-y-auto p-6 space-y-3 no-scrollbar">
                    <h6 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">{{ __('pos.categories') }}</h6>
                    <button type="button" class="cat-filter-btn w-full text-left px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all bg-brand text-white shadow-xl shadow-brand/20 active:scale-95" data-id="all">
                        {{ __('pos.all_products') }}
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" class="cat-filter-btn w-full text-left px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:bg-white hover:text-brand border border-transparent hover:border-slate-200 hover:shadow-sm active:scale-95" data-id="{{ $cat->id }}">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Grid -->
                <div class="flex-1 overflow-y-auto p-6 no-scrollbar bg-white">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="product-grid-container">
                        @foreach($products as $p)
                            <div class="product-item group bg-white p-4 rounded-3xl border border-slate-100 shadow-sm hover:border-brand hover:shadow-xl hover:shadow-brand/5 transition-all cursor-pointer flex flex-col active:scale-[0.98]" 
                                 data-id="{{ $p->id }}" 
                                 data-name="{{ strtolower($p->name) }}"
                                 data-category="{{ $p->category_id }}">
                                
                                <div class="aspect-square rounded-2xl bg-slate-50 mb-3 flex items-center justify-center text-slate-200 group-hover:bg-brand/5 group-hover:text-brand transition-all relative overflow-hidden">
                                     <i class="fa fa-cube text-3xl opacity-40 group-hover:scale-110 transition-all"></i>
                                     
                                     <!-- Status Badges -->
                                     <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
                                         @if($p->stock <= 5)
                                            <div class="bg-rose-500 text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-tighter shadow-sm">{{ __('pos.low_stock') }}</div>
                                         @endif
                                     </div>

                                     <div class="absolute bottom-2 right-2 bg-white/80 backdrop-blur-sm px-2 py-1 rounded text-[8px] font-bold text-slate-400 shadow-sm border border-slate-100/50">
                                        {{ $p->sku }}
                                     </div>
                                </div>

                                <div class="flex-1 flex flex-col min-w-0">
                                    <h5 class="text-sm font-bold text-slate-800 group-hover:text-brand transition-colors line-clamp-2 leading-snug">{{ $p->name }}</h5>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $p->category?->name ?? __('pos.uncategorized') }}</p>
                                </div>

                                <div class="mt-auto pt-3 border-t border-slate-100 flex flex-col gap-3">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">{{ __('pos.price') }}</span>
                                        <span class="text-[13px] font-black text-emerald-600 leading-none truncate block">Rp {{ number_format($p->sale_price, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 w-full h-9">
                                        <!-- Minus Button (Hidden by default) -->
                                        <div class="grid-minus-container flex-initial hidden" data-id="{{ $p->id }}">
                                            <button type="button" class="btn-grid-reduce w-9 h-9 rounded-xl bg-white text-rose-500 border border-rose-100 shadow-sm hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all flex items-center justify-center active:scale-90" data-id="{{ $p->id }}">
                                                <i class="fa fa-minus text-[9px]"></i>
                                            </button>
                                        </div>

                                        <!-- Quantity Badge -->
                                        <div class="qty-badge flex-1 bg-emerald-500 text-white rounded-xl h-9 flex flex-col items-center justify-center shadow-lg shadow-emerald-500/20 hidden" data-id="{{ $p->id }}">
                                            <span class="text-[7px] font-black leading-none uppercase tracking-tighter opacity-70">Qty</span>
                                            <span class="text-[12px] font-black leading-none mt-0.5">0</span>
                                        </div>

                                        <!-- Plus Button -->
                                        <div class="grid-plus-container flex-initial" data-id="{{ $p->id }}">
                                            <button type="button" class="btn-grid-add w-9 h-9 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 hover:bg-brand hover:text-white hover:border-brand transition-all flex items-center justify-center active:scale-95" data-id="{{ $p->id }}">
                                                <i class="fa fa-plus text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Finish Selection Button -->
                    <div class="mt-12 flex justify-center pb-8">
                        <button type="button" id="finish-selection-btn" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-brand/40 hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                            <i class="fa fa-check-circle text-lg text-white/50"></i>
                            {{ __('pos.finish_close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form Fields for Script Compatibility -->
    <div class="hidden">
        <input type="checkbox" id="is_tax_inclusive" {{ old('is_tax_inclusive', $draft?->is_tax_inclusive) ? 'checked' : '' }} form="pos-form">
    </div>

@endsection

@push('styles')
    <link href="{{ asset('assets/css/pages/pos-terminal.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script>
        /**
         * POS Terminal Configuration & Localization
         */
        window.VizuraConfig.pos = {
            products: @json($productsForJs),
            storageKey: 'vizura_pos_cache_' + '{{ auth()->id() }}' + '_' + '{{ $location->id }}',
            initialItems: @json(old('items', $draftItems ?? [])),
            initialPayments: @json(old('payments', [])),
            translations: {
                productNotFound: "{{ __('pos.product_not_found') }}",
                confirmReset: "{{ __('pos.confirm_reset') ?? 'Reset POS?' }}",
                selectProduct: "{{ __('pos.select_product') }}",
                customerPlaceholder: "{{ __('pos.customer_select_placeholder') }}",
                changeDue: "{{ __('pos.change_due') ?? 'CHANGE DUE' }}",
                paid: "{{ __('pos.paid') ?? 'PAID' }}",
                paymentWarning: "{{ __('pos.payment_warning') }}"
            },
            routes: {
                search: "{{ route('products.search') }}"
            }
        };
    </script>
    <script src="{{ asset('assets/js/pages/pos-terminal.js') }}"></script>
@endpush
