@extends('layouts.app')

@section('title', __('report.sales_analysis'))
@section('page-title', __('report.page_title'))

@section('content')
    <div class="space-y-8">
        <!-- Summary Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-ui.card icon="shopping-cart" title="{{ __('report.gross_sales') }}">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format((float) ($summary->gross_total ?? 0), 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('report.gross_total_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="tags" title="{{ __('report.discount_total') }}">
                <h3 class="text-2xl font-black text-rose-500 tracking-tighter mt-2">Rp {{ number_format((float) ($summary->discount_total ?? 0), 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('report.discount_total_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="calculator" title="{{ __('report.tax_total') }}">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format((float) ($summary->tax_total ?? 0), 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('report.tax_total_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="line-chart" title="{{ __('report.net_revenue') }}">
                <h3 class="text-3xl font-black text-emerald-500 tracking-tighter mt-2">Rp {{ number_format((float) ($summary->net_total ?? 0), 0, ',', '.') }}</h3>
                <p class="text-[10px] font-black text-emerald-600/50 uppercase tracking-[0.2em] mt-1 italic">{{ __('report.net_total_sub') }}</p>
            </x-ui.card>
        </div>

        <x-ui.card icon="filter" title="{{ __('report.filter_title') }}">
             <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('report.period') }}</label>
                    <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                        <span class="text-slate-300">-</span>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('report.location') }}</label>
                    <select name="location_id" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                        <option value="">{{ __('report.active_location') }}</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ (int) ($filters['location_id'] ?? 0) === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('report.cashier') }}</label>
                    <select name="cashier_id" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                        <option value="">{{ __('report.all_cashiers') }}</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->id }}" {{ (int) ($filters['cashier_id'] ?? 0) === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 px-6 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">{{ __('report.show') }}</button>
                    <a href="{{ route('reports.sales') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center">{{ __('report.reset') }}</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card icon="table" title="{{ __('report.transaction_detail') }}">
            <x-slot name="actions">
                <a href="{{ route('reports.sales.export', request()->all()) }}" class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">
                    <i class="fa fa-file-excel"></i>
                    {{ __('report.export_csv') }}
                </a>
            </x-slot>
            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('report.time_ref') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('report.cashier_info') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('report.method') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('report.gross') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('report.disc') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('report.nett') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('report.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">{{ ($sale->posted_at ?? $sale->created_at)?->format('d/m/y H:i') }}</p>
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $sale->reference_no }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs font-bold text-slate-700">{{ $sale->cashier?->name ?? 'System' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium italic">{{ $sale->location?->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $methods = $sale->payments->pluck('method')->unique()->map(fn ($m) => strtoupper($m))->values();
                                    @endphp
                                    <span class="text-[9px] font-black text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded tracking-widest">
                                        {{ $methods->isEmpty() ? 'CASH' : $methods->implode(', ') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right font-medium text-xs text-slate-400">
                                    {{ number_format((float) $sale->subtotal, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-right font-medium text-xs text-rose-400">
                                    {{ number_format((float) $sale->order_discount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-slate-800">
                                    {{ number_format((float) $sale->total, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="px-2 py-0.5 {{ $sale->status === 'posted' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} rounded text-[9px] font-black uppercase tracking-tighter">
                                        {{ $sale->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('report.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $sales->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
