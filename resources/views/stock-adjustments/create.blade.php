@extends('layouts.app')

@section('title', __('adjustment.create_title'))
@section('page-title', __('adjustment.page_title'))

@section('content')
    <div class="space-y-8">
        <a href="{{ route('stock-adjustments.index') }}"
            class="inline-flex items-center gap-2 text-xs font-black text-slate-400 hover:text-brand transition-all uppercase tracking-[0.2em] mb-2 group">
            <i class="fa fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            {{ __('adjustment.back_to_list') }}
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
                                        {{ $message }}
                                    </p> @enderror
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
                                    {{ $message }}
                                </p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="evidence"
                                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('adjustment.supporting_doc') }}</label>
                                <label
                                    class="group relative flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-slate-200 rounded-3xl bg-slate-50/50 hover:bg-slate-50 hover:border-brand/30 transition-all cursor-pointer overflow-hidden">
                                    <input type="file" id="evidence" name="evidence" accept=".jpg,.jpeg,.png,.pdf"
                                        class="hidden">
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
                                    {{ $message }}
                                </p> @enderror
                            </div>
                        </div>
                    </x-ui.card>

                    <div class="p-6 bg-brand rounded-[2.5rem] shadow-2xl shadow-brand/20">
                        <button type="submit"
                            class="w-full py-5 bg-white text-brand rounded-2xl font-black text-xs uppercase tracking-[0.3em] hover:scale-[1.02] active:scale-95 transition-all">
                            {{ __('adjustment.save_adj') }}
                        </button>
                        <p class="text-[9px] text-white/60 font-medium text-center mt-4 uppercase tracking-widest">
                            {{ __('adjustment.save_confirmation') }}
                        </p>
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
                                            placeholder="{{ __('adjustment.scan_placeholder') }}" class="premium-input">
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
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-sku="{{ $product->sku }}" data-barcode="{{ $product->barcode }}">
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
                                                <th
                                                    class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-left bg-white/95 backdrop-blur-sm">
                                                    {{ __('adjustment.product') }}</th>
                                                <th
                                                    class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">
                                                    {{ __('adjustment.status') }}</th>
                                                <th
                                                    class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-32 bg-white/95 backdrop-blur-sm">
                                                    {{ __('adjustment.qty_delta') }}</th>
                                                <th
                                                    class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase text-center w-16 bg-white/95 backdrop-blur-sm">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="adj-items-body" class="divide-y divide-slate-50 italic text-slate-400">
                                            <tr id="empty-state">
                                                <td colspan="4" class="px-8 py-20 text-center">
                                                    <div class="opacity-20 flex flex-col items-center">
                                                        <div
                                                            class="w-16 h-16 border-4 border-slate-300 rounded-3xl flex items-center justify-center text-3xl mb-4 italic">
                                                            📦</div>
                                                        <p class="text-sm font-black uppercase">
                                                            {{ __('adjustment.empty_product_state') }}</p>
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
        <link href="{{ asset('assets/css/pages/stock-adjustment-create.css') }}" rel="stylesheet">
    @endpush

    @push('scripts')
        <script>
            /**
             * Page-specific translations for Stock Adjustment
             */
            window.VizuraConfig.translations.select_manual = "{{ __('adjustment.select_manual_placeholder') }}";
        </script>
        <script src="{{ asset('assets/js/pages/stock-adjustment-create.js') }}"></script>
    @endpush
@endsection
