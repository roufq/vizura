@extends('layouts.app')

@section('title', __('receivable.show_title', ['ref' => $sale->reference_no]))
@section('page-title', __('receivable.page_title'))

@section('content')
    <div class="max-w-6xl mx-auto space-y-8">
        <a href="{{ route('receivables.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-2 px-4 focus:outline-none">
            <i class="fa fa-arrow-left"></i> {{ __('receivable.back_to_list') }}
        </a>

        @if (session('status'))
            <x-ui.alert type="success" :message="session('status')" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Information & History -->
            <div class="lg:col-span-12">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Info Card -->
                    <x-ui.card icon="hand-holding-usd" title="{{ __('receivable.receivable_info') }}">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.trans_no') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sale->reference_no }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.customer_name') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sale->customer_name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.hp_no') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sale->customer_phone ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.location') }}</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sale->location?->name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.total') }}</span>
                                <span class="text-sm font-black text-slate-900 tracking-tight">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">{{ __('receivable.paid_label') }}</span>
                                <span class="text-sm font-black text-emerald-600 tracking-tight">Rp {{ number_format((float) $sale->paid_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">{{ __('receivable.balance_label') }}</span>
                                <span class="text-sm font-black text-rose-600 tracking-tight">Rp {{ number_format((float) $sale->receivable_balance, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('receivable.status') }}</span>
                                @php
                                    $statusClass = match($sale->payment_status) {
                                        'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'partial' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-slate-50 text-slate-500 border-slate-100'
                                    };
                                    $statusLabel = match($sale->payment_status) {
                                        'paid' => __('receivable.status_paid'),
                                        'partial' => __('receivable.status_partial'),
                                        default => __('receivable.status_unpaid')
                                    };
                                @endphp
                                <span class="px-4 py-1.5 {{ $statusClass }} border rounded-full text-[9px] font-black uppercase tracking-widest">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Payment Form -->
                    <x-ui.card icon="cash-register" title="{{ __('receivable.receive_payment') }}">
                        @php($isPaid = $sale->payment_status === 'paid')
                        @if ($isPaid)
                            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 mb-6">
                                <p class="text-xs font-bold text-indigo-700 leading-relaxed italic">
                                    <i class="fa fa-info-circle mr-2"></i> {{ __('receivable.paid_off') }}
                                </p>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('receivables.store', $sale) }}" class="space-y-5">
                            @csrf
                            <div class="space-y-2">
                                <label for="paid_at" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.pay_date') }}</label>
                                <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at', now()->toDateString()) }}" {{ $isPaid ? 'disabled' : '' }}
                                       class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                                @error('paid_at') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="method" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.method_label') }}</label>
                                <select id="method" name="method" required {{ $isPaid ? 'disabled' : '' }}
                                        class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                                    <option value="">{{ __('receivable.select_method') }}</option>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" {{ old('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('method') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="amount" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.amount_label') }}</label>
                                <div class="relative items-center flex">
                                    <span class="absolute left-5 text-xs font-black text-slate-400">Rp</span>
                                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $sale->receivable_balance) }}" required {{ $isPaid ? 'disabled' : '' }}
                                           class="w-full bg-slate-50 border-transparent rounded-xl pl-12 pr-5 py-4 text-sm font-black text-brand focus:ring-4 focus:ring-brand/10 transition-all">
                                </div>
                                @error('amount') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="reference_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('receivable.ref_label') }}</label>
                                <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no') }}" {{ $isPaid ? 'disabled' : '' }}
                                       class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all placeholder:text-slate-300" placeholder="...">
                                @error('reference_no') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="pt-2">
                                <button type="submit" {{ $isPaid ? 'disabled' : '' }}
                                        class="w-full px-8 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-brand/40 hover:bg-brand-dark transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ __('receivable.confirm_payment') }}
                                </button>
                            </div>
                        </form>
                    </x-ui.card>
                </div>
            </div>

            <!-- History Table -->
            <div class="lg:col-span-12">
                <x-ui.card icon="history" title="{{ __('receivable.payment_history') }}">
                    <div class="overflow-x-auto -mx-8 -my-2">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-y border-slate-100">
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.date') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.method_label') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('receivable.amount_label') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.creator') }}</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('receivable.ref_label') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($sale->settlements as $payment)
                                    <tr class="hover:bg-slate-50/30 transition-colors">
                                        <td class="px-8 py-4">
                                            <p class="text-xs font-bold text-slate-900 tracking-tight">{{ optional($payment->paid_at)->format('d M Y') }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase">{{ $payment->method }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <p class="text-xs font-black text-slate-900 tracking-tight">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-xs font-bold text-slate-700">{{ $payment->creator?->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-8 py-4">
                                            <p class="text-[10px] font-medium text-slate-400 italic">#{{ $payment->reference_no ?? '-' }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center">
                                            <p class="text-slate-400 font-bold italic">{{ __('receivable.no_payments_yet') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
