@extends('layouts.app')

@section('title', __('payable.index_title'))
@section('page-title', __('payable.page_title'))

@section('content')
    <div class="space-y-8">
        @php
            $pageTotal = $purchases->getCollection()->sum('total');
            $pagePaidAmount = $purchases->getCollection()->sum('paid_total');
            $pageBalanceAmount = $purchases->getCollection()->sum('payable_balance');
        @endphp

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card icon="shopping-basket" title="{{ __('payable.total_purchase_invoice') }}">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format($pageTotal, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('payable.total_purchase_invoice_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="credit-card" title="{{ __('payable.already_paid') }}">
                <h3 class="text-2xl font-black text-emerald-500 tracking-tighter mt-2">Rp {{ number_format($pagePaidAmount, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('payable.already_paid_sub') }}</p>
            </x-ui.card>
            <x-ui.card icon="exclamation-triangle" title="{{ __('payable.remaining_payable') }}">
                <h3 class="text-3xl font-black text-rose-500 tracking-tighter mt-2">Rp {{ number_format($pageBalanceAmount, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-black text-rose-600/50 uppercase tracking-[0.2em] mt-1 italic">{{ __('payable.remaining_payable_sub') }}</p>
            </x-ui.card>
        </div>

        <x-ui.card icon="filter" title="{{ __('payable.filter_search') }}">
            <form method="GET" action="{{ route('purchases.payables.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('payable.ref_supplier') }}</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('app.search') }}..." 
                           class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('payable.payment_status') }}</label>
                    <select name="status" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                        <option value="">{{ __('payable.all_status') }}</option>
                        <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('payable.unpaid') }}</option>
                        <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>{{ __('payable.partial') }}</option>
                        <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('payable.paid') }}</option>
                    </select>
                </div>

                @if ($canSelectLocations)
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('app.location') }}</label>
                        <select name="location_id" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                            <option value="">{{ __('payable.active_location') }}</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ (int) ($filters['location_id'] ?? 0) === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 px-8 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">{{ __('report.filter') }}</button>
                    <a href="{{ route('purchases.payables.index') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center flex items-center justify-center">{{ __('report.reset') }}</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card icon="list-alt" title="{{ __('payable.payable_log') }}">
            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('payable.doc_location') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('payable.supplier') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('payable.total_bill') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('payable.balance') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('payable.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('payable.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($purchases as $p)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $p->reference_no }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 italic">{{ $p->location?->name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs font-bold text-slate-700 uppercase">{{ $p->supplier?->name ?? '-' }}</p>
                                    <p class="text-[9px] text-slate-400 font-medium italic uppercase tracking-widest">{{ __('payable.payable_log') }}</p>
                                </td>
                                <td class="px-6 py-5 text-right font-medium text-xs text-slate-400">
                                    Rp {{ number_format((float) $p->total, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-rose-500 italic">
                                    Rp {{ number_format((float) $p->payable_balance, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusClass = match($p->payment_status) {
                                            'paid' => 'bg-emerald-50 text-emerald-600',
                                            'partial' => 'bg-amber-50 text-amber-600',
                                            default => 'bg-slate-100 text-slate-500'
                                        };
                                        $statusLabel = match($p->payment_status) {
                                            'paid' => __('payable.paid_status'),
                                            'partial' => __('payable.partial_status'),
                                            default => __('payable.unpaid_status')
                                        };
                                    @endphp
                                    <span class="px-3 py-1 {{ $statusClass }} rounded-full text-[9px] font-black uppercase tracking-wider">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <a href="{{ route('purchases.payables.show', $p) }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">{{ __('payable.detail_pay') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('payable.no_debt') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $purchases->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
