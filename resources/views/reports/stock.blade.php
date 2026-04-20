@extends('layouts.app')

@section('title', __('report.stock_report'))
@section('page-title', __('report.stock_report'))

@section('content')
    <div class="space-y-8">
        <!-- Dashboard Metrics (Simplified & Neater) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-brand/40 transition-all duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-brand text-white flex items-center justify-center text-xl shadow-lg shadow-brand/20">
                        <i class="fa fa-archive"></i>
                    </div>
                    <div class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase tracking-widest">
                        {{ __('report.asset_value') }}
                    </div>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('report.total_inventory_value') }}</p>
                    <h4 class="text-2xl font-black text-slate-900 tracking-tighter">
                        Rp {{ number_format($totalValue, 0, ',', '.') }}
                    </h4>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 italic">
                        {{ $locationId ? ($locations->firstWhere('id', $locationId)?->name ?? 'Lokasi Terpilih') : __('report.all_units') }}
                    </p>
                </div>
            </div>

            <!-- Additional Quick Stats could go here (e.g. Total SKU, Stock Out Items) -->
             <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-slate-200 transition-all duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl">
                        <i class="fa fa-cubes"></i>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('report.total_product_types') }}</p>
                    <h4 class="text-2xl font-black text-slate-900 tracking-tighter">
                        {{ number_format($stockItems->total(), 0, ',', '.') }}
                    </h4>
                    <p class="text-[9px] font-bold text-slate-400 mt-1 italic">{{ __('report.registered_sku') }}</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Card (More Compact) -->
        <x-ui.card icon="filter" title="{{ __('report.report_parameters') }}">
             <form method="GET" action="{{ route('reports.stock') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Search -->
                <div class="space-y-1.5">
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('report.search') }}</span>
                    <div class="relative group">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                               class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all placeholder:text-slate-300"
                               placeholder="{{ __('report.search_placeholder') }}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 group-hover:text-brand transition-colors">
                            <i class="fa fa-search text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Location Selector -->
                @if ($canSelectLocations)
                    <div class="space-y-1.5">
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('report.work_unit') }}</span>
                        <select name="location_id" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer appearance-none">
                            <option value="">{{ __('report.use_active_location') }}</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($canViewAll)
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-4 py-3 rounded-xl border border-transparent hover:bg-slate-100 transition-all w-full">
                                <input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-wider">{{ __('report.combine_data') }}</span>
                            </label>
                        </div>
                    @endif
                @endif

                <!-- Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-3 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:scale-[1.02] active:scale-100 transition-all">{{ __('report.filter') }}</button>
                    <a href="{{ route('reports.stock') }}" class="px-4 py-3 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase text-center hover:bg-slate-200 transition-colors">
                        <i class="fa fa-refresh"></i>
                    </a>
                </div>
            </form>
        </x-ui.card>

        <!-- Main Stock Table -->
        <x-ui.card icon="archive" title="{{ __('report.complete_inventory_data') }}">
            <x-slot name="actions">
                <a href="{{ route('reports.stock.export', request()->all()) }}" class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">
                    <i class="fa fa-file-excel"></i>
                    {{ __('report.export_csv') }}
                </a>
            </x-slot>
            <div class="overflow-x-auto -mx-8 -mt-4">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('report.product_detail') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('report.storage_location') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('report.stock') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('report.hpp') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('report.value') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($stockItems as $item)
                            @php
                                $qty = (float) $item->quantity_on_hand;
                                $cost = (float) ($item->product?->cost_price ?? 0);
                                $lineTotal = $qty * $cost;
                            @endphp
                            <tr class="hover:bg-slate-50/30 transition-colors group">
                                <td class="px-8 py-4">
                                    <p class="text-[9px] font-black text-slate-400 uppercase group-hover:text-brand transition-colors">{{ $item->product?->sku ?? '-' }}</p>
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $item->product?->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-700">{{ $item->location?->name ?? '-' }}</p>
                                    <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ $item->product?->category?->name ?? 'Inventory' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-black {{ $qty <= 5 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                        {{ number_format($qty, 0, ',', '.') }}
                                        <span class="ml-1 text-[9px] opacity-40 font-bold uppercase">{{ $item->product?->unit?->name ?? 'pcs' }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-xs text-slate-400 italic">
                                    {{ number_format($cost, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-4 text-right font-black text-sm text-slate-800 italic">
                                    {{ number_format($lineTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('report.no_stock_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $stockItems->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
