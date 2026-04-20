@extends('layouts.app')

@section('title', __('report.stock_card'))
@section('page-title', __('report.stock_card'))

@section('content')
    <div class="space-y-8">
        <!-- Filter Card -->
        <x-ui.card icon="filter" title="{{ __('report.filter_stock_search') }}">
            @if ($products->isEmpty())
                <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl text-amber-700 text-sm font-semibold flex items-center gap-3">
                    <i class="fa fa-exclamation-triangle"></i>
                    {{ __('report.no_products_title') }}
                </div>
            @else
                <form method="GET" action="{{ route('reports.stock-card') }}" class="space-y-6">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- Product Selector (Flexible Grow) -->
                        <div class="flex-1 space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('report.product_name') }}</label>
                            <div class="relative group">
                                <select name="product_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all cursor-pointer appearance-none" required>
                                    @foreach ($products as $item)
                                        <option value="{{ $item->id }}" {{ (int) ($filters['product_id'] ?? 0) === $item->id ? 'selected' : '' }}>
                                            📦 {{ $item->name }} ({{ $item->sku }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-brand transition-colors">
                                    <i class="fa fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Location Selector (Fixed Width on Lg) -->
                        @if ($canSelectLocations)
                            <div class="lg:w-72 space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('report.warehouse_location') }}</label>
                                <div class="relative group">
                                    <select name="location_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all cursor-pointer appearance-none">
                                        <option value="">📍 {{ __('report.all_locations') }}</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}" {{ (int) ($filters['location_id'] ?? 0) === $loc->id ? 'selected' : '' }}>
                                                {{ $loc->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-brand transition-colors">
                                        <i class="fa fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col lg:flex-row items-end gap-6 pt-4 border-t border-slate-50">
                        <!-- Date Range (Flexible) -->
                        <div class="flex-1 space-y-2 w-full">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1 italic">{{ __('report.period') }}</label>
                            <div class="flex items-center gap-4 bg-slate-50 rounded-2xl px-6 py-1 border border-slate-100 shadow-inner group focus-within:ring-4 focus-within:ring-brand/10 transition-all">
                                <div class="flex-1 flex flex-col pt-2 pb-1">
                                    <span class="text-[8px] font-black uppercase text-slate-300 mb-0.5 tracking-tighter">{{ __('report.start') }}</span>
                                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-black text-slate-600 focus:ring-0">
                                </div>
                                <div class="h-8 w-px bg-slate-200"></div>
                                <div class="flex-1 flex flex-col pt-2 pb-1">
                                    <span class="text-[8px] font-black uppercase text-slate-300 mb-0.5 tracking-tighter">{{ __('report.end') }}</span>
                                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-black text-slate-600 focus:ring-0">
                                </div>
                            </div>
                        </div>

                        <!-- Button (Fixed Width on Lg) -->
                        <div class="lg:w-56 w-full">
                            <button type="submit" class="w-full py-4 bg-brand text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-brand/20 hover:bg-brand-dark hover:scale-[1.02] active:scale-95 transition-all">
                                {{ __('report.show_button') }}
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </x-ui.card>

        @if ($product)
            <!-- Info Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <x-ui.card icon="cube" title="{{ __('report.selected_product') }}">
                    <div class="mt-2 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-brand/5 flex items-center justify-center text-brand">
                            <i class="fa fa-barcode text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none">{{ $product->name }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $product->sku }}</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card icon="map-marker" title="{{ __('report.location_title') }}">
                    <div class="mt-2 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fa fa-building text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none">
                                {{ $locationId ? ($locations->firstWhere('id', $locationId)?->name ?? __('report.active_location')) : __('report.all_locations') }}
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Inventory Point</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card icon="calendar" title="{{ __('report.report_period') }}">
                    <div class="mt-2 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fa fa-clock-o text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-700 leading-none">
                                {{ $filters['start_date'] ?? __('report.start') }} <span class="text-slate-300 mx-1">&rarr;</span> {{ $filters['end_date'] ?? __('report.end') }}
                            </p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Timeline History</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Table Card -->
            <x-ui.card icon="history" title="{{ __('report.stock_mutation_history') }}">
                <div class="overflow-x-auto -mx-8">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-brand/[0.02] border-y border-slate-100">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('report.transaction_time') }}</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('report.activity_type') }}</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('report.reference_doc') }}</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-center">{{ __('report.in') }}</th>
                                <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-center">{{ __('report.out') }}</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-right">{{ __('report.final_balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @php $runningBalance = 0; @endphp
                            @forelse ($entries as $entry)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <p class="text-[10px] font-black text-slate-400 uppercase mb-1">{{ \Carbon\Carbon::parse($entry['date'])->format('d/m/Y') }}</p>
                                        <p class="text-xs text-slate-900 font-bold tracking-tighter">{{ \Carbon\Carbon::parse($entry['date'])->format('H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        @php
                                            $type = $entry['type'];
                                            $colorClass = match(true) {
                                                str_contains($type, 'in') || str_contains($type, 'purchase') => 'bg-emerald-50 text-emerald-600',
                                                str_contains($type, 'out') || str_contains($type, 'sale') => 'bg-rose-50 text-rose-600',
                                                default => 'bg-slate-100 text-slate-600'
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 {{ $colorClass }} rounded text-[9px] font-black uppercase tracking-widest">
                                            {{ ucwords(str_replace('_', ' ', $type)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="text-xs font-bold text-slate-900 leading-tight">{{ $entry['reference'] }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium italic truncate max-w-[200px]">{{ $entry['note'] ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-center px-4">
                                        @if($entry['qty_in'] > 0)
                                            <span class="text-xs font-black text-emerald-500 font-mono tracking-tighter">+{{ number_format($entry['qty_in'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-xs text-slate-200">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center px-4">
                                        @if($entry['qty_out'] > 0)
                                            <span class="text-xs font-black text-rose-500 font-mono tracking-tighter">-{{ number_format($entry['qty_out'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-xs text-slate-200">-</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 text-right font-black text-sm text-slate-800 tabular-nums">
                                        {{ number_format($entry['balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-20">
                                            <div class="w-16 h-16 mb-4 border-4 border-slate-300 rounded-2xl flex items-center justify-center text-3xl">⏳</div>
                                            <p class="text-slate-500 font-bold text-sm">{{ __('report.no_mutation_history') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
@endsection
