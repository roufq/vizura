@extends('layouts.app')

@section('title', __('pos.terminal_title'))
@section('page-title', __('pos.terminal_title'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 -mt-2">
        <!-- Input & Cart Area -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Quick Input Bar -->
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col md:flex-row gap-6 items-end">
                <div class="flex-1 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('pos.scan_placeholder') }}</label>
                    <div class="relative group">
                        <input type="text" id="scan_input" autofocus autocomplete="off" 
                               placeholder="{{ __('pos.scan_placeholder') }}" 
                               class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
                            <i class="fa fa-barcode text-xl"></i>
                        </div>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 flex items-center gap-2">
                            <span class="text-[10px] bg-slate-200 text-slate-500 px-2 py-1 rounded font-black">ENTER</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-64 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('pos.manual_product') }}</label>
                    <select id="product_select" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 cursor-pointer">
                        <option value="">{{ __('pos.select_product') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                
                <button type="button" id="add_manual" class="h-[60px] w-[60px] bg-brand text-white rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                    <i class="fa fa-plus"></i>
                </button>
            </div>

            <!-- Extra Options Toolbar (Improved) -->
            <div class="bg-white/50 backdrop-blur px-8 py-4 rounded-[1.5rem] border border-slate-100 shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-8">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('pos.order_type') }}</span>
                        <div class="flex bg-slate-100/50 p-1.5 rounded-2xl border border-slate-100">
                            <button type="button" class="order-type-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all bg-white shadow-sm text-brand border border-slate-100" data-value="retail">{{ __('pos.retail') }}</button>
                            <button type="button" class="order-type-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all text-slate-400 hover:text-slate-600" data-value="wholesale">{{ __('pos.wholesale') }}</button>
                            <button type="button" class="order-type-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all text-slate-400 hover:text-slate-600" data-value="online">{{ __('pos.online') }}</button>
                        </div>
                    </div>

                    <div class="hidden md:block w-px h-10 bg-slate-100"></div>

                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('pos.tax_mode') }}</span>
                        <div class="flex bg-slate-100/50 p-1.5 rounded-2xl border border-slate-100">
                            <button type="button" class="tax-mode-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all bg-white shadow-sm text-brand border border-slate-100" data-inc="0" data-rate="0">{{ __('pos.tax_none') }}</button>
                            <button type="button" class="tax-mode-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all text-slate-400 hover:text-slate-600" data-inc="1" data-rate="11">{{ __('pos.tax_inc') }}</button>
                            <button type="button" class="tax-mode-btn px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all text-slate-400 hover:text-slate-600" data-inc="0" data-rate="11">{{ __('pos.tax_exc') }}</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                     <div class="flex flex-col items-end gap-1">
                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-300 mr-1">{{ __('pos.view_mode') }}</span>
                        <div class="flex bg-slate-50 p-1 rounded-xl border border-slate-100">
                            <button type="button" id="toggle-list-view" class="p-2 rounded-lg text-brand bg-white shadow-sm transition-all"><i class="fa fa-list"></i></button>
                            <button type="button" id="toggle-grid-view" class="p-2 rounded-lg text-slate-300 hover:text-brand transition-all"><i class="fa fa-th"></i></button>
                        </div>
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
                    <button type="button" onclick="location.reload()" class="px-8 py-4 bg-rose-50 text-rose-600 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-100 transition-all">
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

            <!-- Payment Methods -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-black uppercase tracking-widest text-slate-800">{{ __('pos.payment') }}</h4>
                    <button type="button" id="set_exact_payment" class="text-[10px] font-black text-brand uppercase tracking-widest hover:underline">{{ __('pos.exact_payment') }}</button>
                </div>

                <div id="payment-rows" class="space-y-4">
                    <!-- JS Inject -->
                </div>

                <button type="button" id="add_payment_row" class="w-full py-3 bg-slate-50 text-slate-400 rounded-2xl border-2 border-dashed border-slate-100 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-slate-100 transition-all">
                    {{ __('pos.add_payment_method') }}
                </button>

                <div class="space-y-3 pt-4 border-t border-slate-50">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">{{ __('pos.paid') }}</span>
                        <span class="text-sm font-black text-slate-700" id="paid_display">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-brand">
                        <span class="text-[10px] font-black uppercase tracking-widest italic">{{ __('pos.change_due') }}</span>
                        <span class="text-xl font-black" id="change_display">Rp 0</span>
                    </div>
                </div>

                <div id="payment-warning" class="p-3 bg-rose-50 text-rose-600 rounded-xl text-[10px] font-bold hidden text-center uppercase tracking-widest">
                    ⚠️ {{ __('pos.payment_warning') }}
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
                                            <div class="bg-rose-500 text-white px-2 py-1 rounded text-[8px] font-black uppercase tracking-tighter shadow-sm">Low Stock</div>
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

@push('scripts')
    <script>
        jQuery(function($) {
            // 1. Core State
            const cart = new Map();
            const products = @json($productsForJs);
            const productMap = new Map(products.map(p => [String(p.id), p]));
            let currentTotal = 0;

            // 2. Element Selectors
            const scanInput = document.getElementById('scan_input');
            const addManualButton = document.getElementById('add_manual');
            const productSelect = $('#product_select');
            const cartBody = document.getElementById('cart-body');
            const emptyState = document.getElementById('cart-empty-state');
            const stockWarning = document.getElementById('stock-warning');
            const payButton = document.getElementById('pay_button');
            const subtotalDisplay = document.getElementById('subtotal_display');
            const totalDisplay = document.getElementById('total_display');
            const paidDisplay = document.getElementById('paid_display');
            const changeDisplay = document.getElementById('change_display');
            const orderDiscountInput = document.getElementById('order_discount');
            const taxRateInput = document.getElementById('tax_rate');
            const taxRateLabel = document.getElementById('tax_rate_val');
            const taxInclusiveInput = document.getElementById('is_tax_inclusive');
            const paymentRows = document.getElementById('payment-rows');
            const paymentWarning = document.getElementById('payment-warning');
            const searchResults = document.createElement('div');

            // 3. Helper Functions
            const moneyFormat = v => 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(v);

            function addToCart(p, q, up, ld) {
                const k = String(p.id);
                if (cart.has(k)) {
                    cart.get(k).quantity += parseFloat(q);
                } else {
                    cart.set(k, { 
                        productId: p.id, 
                        name: p.name, 
                        sku: p.sku, 
                        price: p.price, 
                        stock: p.stock, 
                        block: p.block_when_out_of_stock, 
                        quantity: parseFloat(q), 
                        unitPrice: parseFloat(up), 
                        lineDiscount: parseFloat(ld) 
                    });
                }
            }

            function updateCartTable() {
                if (!cartBody) return;
                cartBody.innerHTML = '';
                if (emptyState) emptyState.classList.toggle('hidden', cart.size > 0);
                
                let idx = 0;
                cart.forEach(item => {
                    const row = document.createElement('tr');
                    const lineTotal = Math.max(0, (item.quantity * item.unitPrice) - item.lineDiscount);
                    row.className = "hover:bg-slate-50/50 transition-all border-b border-slate-50 last:border-0";
                    row.innerHTML = `
                        <td class="px-8 py-4">
                            <p class="text-sm font-bold text-slate-800 leading-tight">${item.name}</p>
                            <p class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-tighter">SKU: ${item.sku} | Stok: ${item.stock}</p>
                            <input type="hidden" name="items[${idx}][product_id]" value="${item.productId}" form="pos-form">
                            <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}" form="pos-form">
                            <input type="hidden" name="items[${idx}][unit_price]" value="${item.unitPrice}" form="pos-form">
                            <input type="hidden" name="items[${idx}][line_discount]" value="${item.lineDiscount}" form="pos-form">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" step="1" class="w-16 bg-slate-50 border-none rounded-xl px-2 py-2 text-center text-sm font-black text-slate-700 qty-input" data-id="${item.productId}" value="${item.quantity}">
                        </td>
                        <td class="px-6 py-4 text-right">
                            <input type="number" step="0.01" class="w-24 bg-transparent border-none text-right text-xs font-bold text-slate-400 price-input" data-id="${item.productId}" value="${item.unitPrice}">
                        </td>
                        <td class="px-6 py-4 text-right">
                            <input type="number" step="0.01" class="w-20 bg-transparent border-none text-right text-xs font-bold text-rose-400 discount-input" data-id="${item.productId}" value="${item.lineDiscount}">
                        </td>
                        <td class="px-6 py-4 text-right font-black text-sm text-slate-700 italic">${moneyFormat(lineTotal)}</td>
                        <td class="px-8 py-4 text-center">
                            <button type="button" class="text-slate-300 hover:text-rose-600 remove-item" data-id="${item.productId}"><i class="fa fa-times-circle text-lg"></i></button>
                        </td>
                    `;
                    cartBody.appendChild(row);
                    idx++;
                });

                // Re-bind listeners
                cartBody.querySelectorAll('.qty-input, .price-input, .discount-input').forEach(i => i.addEventListener('input', e => {
                    const item = cart.get(String(e.target.dataset.id));
                    if (item) {
                        if (e.target.classList.contains('qty-input')) item.quantity = parseFloat(e.target.value || 0);
                        if (e.target.classList.contains('price-input')) item.unitPrice = parseFloat(e.target.value || 0);
                        if (e.target.classList.contains('discount-input')) item.lineDiscount = parseFloat(e.target.value || 0);
                        updateTotals();
                        const subtotalCell = e.target.closest('tr').querySelector('td:nth-last-child(2)');
                        const lt = Math.max(0, (item.quantity * item.unitPrice) - item.lineDiscount);
                        subtotalCell.textContent = moneyFormat(lt);
                    }
                }));

                cartBody.querySelectorAll('.remove-item').forEach(b => b.addEventListener('click', e => { 
                    cart.delete(String(e.currentTarget.dataset.id)); 
                    updateCartTable();
                }));
                syncBrowserBadges();
                updateTotals();
            }

            function syncBrowserBadges() {
                // Reset all to default
                $('.qty-badge').addClass('hidden').find('span:last-child').text('0');
                $('.grid-minus-container').addClass('hidden');
                $('.product-item').removeClass('border-brand ring-2 ring-brand/10 shadow-xl shadow-brand/10').addClass('border-slate-100 shadow-sm');
                $('.btn-grid-add').removeClass('bg-brand text-white border-brand').addClass('bg-slate-50 text-slate-400 border-slate-100');
                
                cart.forEach((item, id) => {
                    const badge = $(`.qty-badge[data-id="${id}"]`);
                    const card = $(`.product-item[data-id="${id}"]`);
                    const minusContainer = $(`.grid-minus-container[data-id="${id}"]`);
                    const plusBtn = $(`.btn-grid-add[data-id="${id}"]`);
                    
                    if (badge.length) {
                        badge.removeClass('hidden').find('span:last-child').text(item.quantity);
                    }
                    if (card.length) {
                        card.removeClass('border-slate-100 shadow-sm').addClass('border-brand ring-2 ring-brand/10 shadow-xl shadow-brand/10');
                    }
                    if (minusContainer.length && item.quantity > 0) {
                        minusContainer.removeClass('hidden');
                    }
                    if (plusBtn.length) {
                        plusBtn.removeClass('bg-slate-50 text-slate-400 border-slate-100').addClass('bg-brand text-white border-brand');
                    }
                });
            }

            function updateTotals() {
                let st = 0; let si = false;
                cart.forEach(i => { 
                    st += Math.max(0, (i.quantity * i.unitPrice) - i.lineDiscount); 
                    if (i.block && i.quantity > i.stock) si = true; 
                });

                const od = parseFloat(orderDiscountInput?.value || 0);
                const tr = parseFloat(taxRateInput?.value || 0);
                const inc = taxInclusiveInput?.checked;
                
                currentTotal = Math.max(0, st - od);
                const tx = tr > 0 ? (inc ? (currentTotal - (currentTotal / (1 + (tr/100)))) : (currentTotal * (tr/100))) : 0;
                if (!inc) currentTotal += tx;

                let paid = 0;
                document.querySelectorAll('.payment-row').forEach(row => {
                    const ai = row.querySelector('.payment-amount');
                    if (ai) paid += parseFloat(ai.value || 0);
                });

                if (subtotalDisplay) subtotalDisplay.textContent = moneyFormat(st);
                if (totalDisplay) totalDisplay.textContent = moneyFormat(currentTotal);
                if (paidDisplay) paidDisplay.textContent = moneyFormat(paid);
                if (changeDisplay) changeDisplay.textContent = moneyFormat(Math.max(0, paid - currentTotal));
                if (stockWarning) stockWarning.classList.toggle('hidden', !si);
                if (paymentWarning) paymentWarning.classList.toggle('hidden', paid >= currentTotal || cart.size === 0);
                if (payButton) payButton.disabled = si || od > st || paid < currentTotal || cart.size === 0;
            }

            function handleScan(val) {
                const kw = (val || '').trim();
                if (!kw) return;

                // Priority 1: Search in loaded product map
                let p = Array.from(productMap.values()).find(item => item.barcode === kw || item.sku === kw);
                if (p) {
                    addToCart(p, 1, p.price, 0);
                    updateCartTable();
                    if (scanInput) scanInput.value = '';
                    return;
                }

                // Priority 2: Fetch from server
                fetch(`{{ route('products.search') }}?query=${encodeURIComponent(kw)}`)
                    .then(res => res.json())
                    .then(data => {
                        const found = data.find(item => item.barcode === kw || item.sku === kw);
                        if (!found) {
                            if (window.swal) swal("Oops!", "{{ __('pos.product_not_found') }}", "error");
                            else alert("{{ __('pos.product_not_found') }}");
                            if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                            return;
                        }
                        productMap.set(String(found.id), found);
                        addToCart(found, 1, found.price, 0);
                        updateCartTable();
                        if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                    });
            }

            // 4. Component Initializations
            function initSelect2() {
                if (typeof $.fn === 'undefined' || typeof $.fn.select2 !== 'function') {
                    console.warn('Select2 not yet ready or loaded, retrying...');
                    setTimeout(initSelect2, 200);
                    return;
                }

                productSelect.select2({ 
                    placeholder: '{{ __('pos.select_product') }}', 
                    width: '100%',
                    containerCssClass: 'vizura-select2-container'
                });

                $('.select2-customer').select2({
                    placeholder: '{{ __('pos.customer_select_placeholder') }}',
                    tags: true,
                    width: '100%',
                    allowClear: true,
                    containerCssClass: 'vizura-select2-container',
                    createTag: function (params) {
                        var term = $.trim(params.term);
                        if (term === '') { return null; }
                        return { id: term, text: term, newTag: true };
                    }
                }).on('change', function(e) {
                    const selected = $(this).select2('data')[0];
                    const phoneInput = document.getElementById('customer-phone');
                    const idInput = document.getElementById('customer-id-input');
                    
                    if (selected && selected.element) {
                        const phone = $(selected.element).data('phone');
                        const id = $(selected.element).data('id');
                        if (phoneInput) phoneInput.value = phone || '';
                        if (idInput) idInput.value = id || '';
                    } else if (selected && selected.newTag) {
                        if (idInput) idInput.value = '';
                    } else {
                        if (idInput) idInput.value = '';
                    }
                });
            }

            initSelect2();

            // Custom Styling for Select2
            const style = document.createElement('style');
            style.innerHTML = `
                .select2-container--default .select2-selection--single { background-color: #f8fafc; border: none; border-radius: 1rem; height: 56px; padding-left: 12px; display: flex; align-items: center; font-size: 13px; font-weight: 700; color: #334155; }
                .select2-container--default .select2-selection--single .select2-selection__arrow { height: 56px; right: 15px; }
                .select2-container--default .select2-selection--single .select2-selection__rendered { color: #334155; }
                .select2-dropdown { border-radius: 1rem; border: 1px solid #f1f5f9; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); overflow: hidden; }
                .select2-search__field { border-radius: 0.5rem !important; border: 1px solid #f1f5f9 !important; }
            `;
            document.head.appendChild(style);

            // Search Results Overlay setup
            searchResults.className = 'absolute z-50 left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-100 hidden overflow-hidden';
            if (scanInput?.parentElement) scanInput.parentElement.appendChild(searchResults);

            let searchTimeout = null;
            if (scanInput) {
                scanInput.addEventListener('input', e => {
                    clearTimeout(searchTimeout);
                    const val = e.target.value.trim();
                    if (val.length < 2) { searchResults.classList.add('hidden'); return; }
                    searchTimeout = setTimeout(() => {
                        fetch(`{{ route('products.search') }}?query=${encodeURIComponent(val)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (!data.length) { searchResults.classList.add('hidden'); return; }
                                renderSearchResults(data);
                            });
                    }, 300);
                });

                scanInput.addEventListener('keydown', e => { 
                    if (e.key === 'Enter') { 
                        e.preventDefault(); 
                        const firstResult = searchResults.querySelector('div:first-child');
                        if (firstResult && !searchResults.classList.contains('hidden')) firstResult.click();
                        else handleScan(scanInput.value); 
                    } 
                });
            }

            function renderSearchResults(data) {
                searchResults.innerHTML = '';
                searchResults.classList.remove('hidden');
                data.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'px-6 py-4 hover:bg-slate-50 cursor-pointer flex justify-between items-center border-b border-slate-50 last:border-0';
                    item.innerHTML = `
                        <div><p class="text-sm font-bold text-slate-700">${p.name}</p><p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">${p.sku} | Stok: ${p.stock}</p></div>
                        <div class="text-right"><p class="text-sm font-black text-brand">${moneyFormat(p.price)}</p></div>
                    `;
                    item.onclick = () => {
                        productMap.set(String(p.id), p);
                        addToCart(p, 1, p.price, 0); 
                        updateCartTable();
                        searchResults.classList.add('hidden');
                        if (scanInput) { scanInput.value = ''; scanInput.focus(); }
                    };
                    searchResults.appendChild(item);
                });
            }

            // 5. Custom UI Actions (Toggles, Browser)
            $('.order-type-btn').on('click', function() {
                $('.order-type-btn').removeClass('bg-white shadow-sm text-brand border border-slate-100').addClass('text-slate-400 hover:text-slate-600');
                $(this).addClass('bg-white shadow-sm text-brand border border-slate-100').removeClass('text-slate-400 hover:text-slate-600');
                $('#order_type_input').val($(this).data('value'));
            });

            $('.tax-mode-btn').on('click', function() {
                $('.tax-mode-btn').removeClass('bg-white shadow-sm text-brand border border-slate-100').addClass('text-slate-400 hover:text-slate-600');
                $(this).addClass('bg-white shadow-sm text-brand border border-slate-100').removeClass('text-slate-400 hover:text-slate-600');
                if (taxRateInput) taxRateInput.value = $(this).data('rate');
                if (taxInclusiveInput) taxInclusiveInput.checked = $(this).data('inc') === 1;
                if (taxRateLabel) taxRateLabel.textContent = $(this).data('rate');
                updateTotals();
            });

            const browserContainer = document.getElementById('product-browser-container');
            if (document.getElementById('toggle-grid-view')) {
                document.getElementById('toggle-grid-view').onclick = () => {
                    browserContainer?.classList.remove('hidden');
                    syncBrowserBadges();
                };
            }
            if (document.getElementById('close-browser-btn')) {
                document.getElementById('close-browser-btn').onclick = () => browserContainer?.classList.add('hidden');
            }
            if (document.getElementById('finish-selection-btn')) {
                document.getElementById('finish-selection-btn').onclick = () => browserContainer?.classList.add('hidden');
            }
            if (document.getElementById('close-browser')) {
                document.getElementById('close-browser').onclick = () => browserContainer?.classList.add('hidden');
            }

            $('.cat-filter-btn').on('click', function() {
                $('.cat-filter-btn').removeClass('bg-brand text-white shadow-xl shadow-brand/20').addClass('text-slate-500 hover:bg-white hover:text-brand border border-transparent hover:border-slate-200 hover:shadow-sm');
                $(this).addClass('bg-brand text-white shadow-xl shadow-brand/20').removeClass('text-slate-500 hover:bg-white hover:text-brand border border-transparent hover:border-slate-200 hover:shadow-sm');
                filterGrid();
            });

            const browserSearchInput = document.getElementById('browser-search');
            if (browserSearchInput) {
                browserSearchInput.addEventListener('input', filterGrid);
            }

            function filterGrid() {
                const catId = $('.cat-filter-btn.bg-brand').data('id');
                const query = (browserSearchInput?.value || '').toLowerCase().trim();
                
                $('.product-item').each(function() {
                    const itemCat = $(this).data('category');
                    const itemName = String($(this).data('name') || '').toLowerCase();
                    
                    const matchesCat = (catId === 'all' || itemCat == catId);
                    const matchesQuery = !query || itemName.includes(query);
                    
                    if (matchesCat && matchesQuery) {
                        $(this).removeClass('hidden').addClass('flex');
                    } else {
                        $(this).addClass('hidden').removeClass('flex');
                    }
                });
            }

            // Handle Grid Add (+)
            $(document).on('click', '.btn-grid-add', function(e) {
                e.stopPropagation();
                const p = productMap.get(String($(this).data('id')));
                if (p) {
                    addToCart(p, 1, p.price, 0);
                    updateCartTable();
                }
            });

            // Handle Grid Reduce (-)
            $(document).on('click', '.btn-grid-reduce', function(e) {
                e.stopPropagation();
                const pid = String($(this).data('id'));
                const item = cart.get(pid);
                if (item) {
                    if (item.quantity > 1) {
                        item.quantity -= 1;
                    } else {
                        cart.delete(pid);
                    }
                    updateCartTable();
                }
            });

            $('.product-item').on('click', function(e) {
                // Prevent if clicked on buttons
                if ($(e.target).closest('button').length) return;
                
                const p = productMap.get(String($(this).data('id')));
                if (p) { 
                    addToCart(p, 1, p.price, 0); 
                    updateCartTable();
                    
                    const $card = $(this);
                    $card.addClass('scale-95 opacity-80');
                    setTimeout(() => $card.removeClass('scale-95 opacity-80'), 100);
                }
            });

            // 6. Final Bindings & Init
            if (addManualButton) {
                addManualButton.addEventListener('click', () => {
                    const pid = productSelect.val();
                    if (!pid) return;
                    const p = productMap.get(String(pid));
                    if (p) { addToCart(p, 1, p.price, 0); updateCartTable(); productSelect.val(null).trigger('change'); }
                    if (scanInput) scanInput.focus();
                });
            }

            if (orderDiscountInput) orderDiscountInput.addEventListener('input', updateTotals);
            if (taxRateInput) taxRateInput.addEventListener('input', () => { if (taxRateLabel) taxRateLabel.textContent = taxRateInput.value; updateTotals(); });
            
            // Payment Rows
            function addPaymentRowFields(m, a, r) {
                if (!paymentRows) return;
                const idx = paymentRows.querySelectorAll('.payment-row').length;
                const row = document.createElement('div');
                row.className = 'payment-row flex items-center gap-2 mb-2';
                row.innerHTML = `
                    <select class="payment-method bg-slate-50 border-none rounded-xl px-3 py-3 text-xs font-bold text-slate-600 focus:ring-1 focus:ring-brand" name="payments[${idx}][method]" form="pos-form">
                        <option value="cash" ${m === 'cash' ? 'selected' : ''}>CASH</option>
                        <option value="card" ${m === 'card' ? 'selected' : ''}>CARD</option>
                        <option value="transfer" ${m === 'transfer' ? 'selected' : ''}>TRF</option>
                        <option value="qris" ${m === 'qris' ? 'selected' : ''}>QRIS</option>
                        <option value="piutang" ${m === 'piutang' ? 'selected' : ''}>RECEIVABLE</option>
                    </select>
                    <input type="number" step="0.01" class="payment-amount flex-1 bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-black text-slate-900 focus:ring-1 focus:ring-brand" name="payments[${idx}][amount]" value="${a}" placeholder="Amount" form="pos-form">
                    <input type="text" class="w-16 bg-slate-50 border-none rounded-xl px-2 py-3 text-[10px] font-bold text-slate-400 focus:ring-1 focus:ring-brand" name="payments[${idx}][reference_no]" value="${r}" placeholder="Ref" form="pos-form">
                `;
                paymentRows.appendChild(row);
                row.querySelectorAll('input, select').forEach(i => i.addEventListener('input', updateTotals));
            }

            $('#add_payment_row').on('click', () => { addPaymentRowFields('', '', ''); updateTotals(); });
            $('#set_exact_payment').on('click', () => {
                const firstRow = paymentRows?.querySelector('.payment-row');
                if (!firstRow) addPaymentRowFields('cash', currentTotal, '');
                else {
                    const ai = firstRow.querySelector('.payment-amount');
                    if (ai) ai.value = currentTotal.toFixed(2);
                }
                updateTotals();
            });

            // Initial Data Load
            const initialItems = @json(old('items', $draftItems ?? []));
            initialItems.forEach(item => {
                const p = productMap.get(String(item.product_id));
                if (p) addToCart(p, item.quantity, item.unit_price || p.price, item.line_discount || 0);
            });
            const initialPayments = @json(old('payments', []));
            if (initialPayments.length) initialPayments.forEach(p => addPaymentRowFields(p.method, p.amount, p.reference_no));
            else addPaymentRowFields('cash', '', '');

            updateCartTable();
        });
    </script>
@endpush
