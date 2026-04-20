@extends('layouts.app')

@section('title', __('receivable.index_title'))
@section('page-title', __('receivable.page_title'))

@section('content')
    <div class="space-y-8">
        @php
            $pageTotal = $sales->getCollection()->sum('total');
            $pagePaidAmount = $sales->getCollection()->sum('paid_total');
            $pageBalanceAmount = $sales->getCollection()->sum('receivable_balance');
        @endphp

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card icon="hand-holding-usd" title="{{ __('receivable.total_receivable') }}">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format($pageTotal, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('receivable.total_receivable_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="check-square" title="{{ __('receivable.paid_total') }}">
                <h3 class="text-2xl font-black text-emerald-500 tracking-tighter mt-2">Rp {{ number_format($pagePaidAmount, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('receivable.paid_total_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="hourglass-end" title="{{ __('receivable.balance_total') }}">
                <h3 class="text-3xl font-black text-rose-500 tracking-tighter mt-2">Rp {{ number_format($pageBalanceAmount, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-black text-rose-600/50 uppercase tracking-[0.2em] mt-1 italic italic">{{ __('receivable.balance_total_sub') }}</p>
            </x-ui.card>
        </div>

        <x-ui.card icon="filter" title="{{ __('receivable.filter_title') }}">
            <form method="GET" action="{{ route('receivables.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.search_label') }}</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('receivable.search_placeholder') }}" 
                           class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.payment_status') }}</label>
                    <select name="status" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                        <option value="">{{ __('receivable.all_status') }}</option>
                        <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('receivable.unpaid') }}</option>
                        <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>{{ __('receivable.partial') }}</option>
                        <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('receivable.paid') }}</option>
                    </select>
                </div>

                @if ($canSelectLocations)
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.location') }}</label>
                        <select name="location_id" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                            <option value="">{{ __('receivable.active_location') }}</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ (int) ($filters['location_id'] ?? 0) === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 px-8 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">{{ __('receivable.filter_btn') }}</button>
                    <a href="{{ route('receivables.index') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center">{{ __('receivable.reset_btn') }}</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card icon="credit-card" title="{{ __('receivable.list_title') }}">
            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.inv_loc') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.customer_info') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('receivable.total_amount') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('receivable.remaining_balance') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('receivable.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('receivable.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $sale->reference_no }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 italic">{{ $sale->location?->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs font-bold text-slate-700 uppercase">{{ $sale->customer_name ?? __('receivable.general_customer') }}</p>
                                    @if($sale->customer_phone)
                                        <p class="text-[9px] text-slate-400 font-medium">{{ $sale->customer_phone }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right font-medium text-xs text-slate-400">
                                    Rp {{ number_format((float) $sale->total, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-rose-500 italic">
                                    Rp {{ number_format((float) $sale->receivable_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusClass = match($sale->payment_status) {
                                            'paid' => 'bg-emerald-50 text-emerald-600',
                                            'partial' => 'bg-amber-50 text-amber-600',
                                            default => 'bg-slate-100 text-slate-500'
                                        };
                                        $statusLabel = match($sale->payment_status) {
                                            'paid' => __('receivable.status_paid'),
                                            'partial' => __('receivable.status_partial'),
                                            default => __('receivable.status_unpaid')
                                        };
                                    @endphp
                                    <span class="px-3 py-1 {{ $statusClass }} rounded-full text-[9px] font-black uppercase tracking-wider">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <a href="{{ route('receivables.show', $sale) }}" class="px-4 py-2 bg-brand/10 text-brand rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-brand hover:text-white transition-all">{{ __('receivable.detail_pay') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('receivable.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $sales->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
