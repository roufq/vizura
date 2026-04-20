@extends('layouts.app')

@section('title', __('customer.show_title'))
@section('page-title', __('customer.summary'))

@section('content')
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest px-4">
                <i class="fa fa-arrow-left"></i> {{ __('customer.back') }}
            </a>
            <div class="flex gap-3">
                 <a href="{{ route('customers.edit', $customer) }}" class="px-6 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                    <i class="fa fa-pencil mr-2"></i>{{ __('customer.edit_profile') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Customer Info Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div class="flex flex-col items-center text-center mb-8">
                        <div class="w-24 h-24 rounded-3xl bg-brand/5 flex items-center justify-center text-brand text-4xl font-black mb-4">
                            {{ substr($customer->name, 0, 1) }}
                        </div>
                        <h3 class="text-xl font-black text-slate-900 leading-tight">{{ $customer->name }}</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ __('customer.since_date') }} {{ $customer->created_at->format('M Y') }}</p>
                    </div>

                    <div class="space-y-4 pt-6 border-t border-slate-50 text-sm">
                        <div class="flex justify-between items-center group">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('customer.status') }}</span>
                            <span class="px-3 py-1 {{ $customer->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                {{ $customer->is_active ? __('customer.active') : __('customer.inactive') }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 font-bold mb-1">{{ __('customer.phone_label') }}</span>
                            <p class="font-bold text-slate-700">{{ $customer->phone ?? '-' }}</p>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('customer.email_label') }}</span>
                            <p class="font-bold text-slate-700">{{ $customer->email ?? '-' }}</p>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('customer.address_label') }}</span>
                            <p class="font-bold text-slate-700 leading-relaxed">{{ $customer->address ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand/20 rounded-full blur-2xl"></div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1">{{ __('customer.loyalty_points_card') }}</p>
                        <h4 class="text-4xl font-black text-white tracking-tight">{{ number_format($customer->loyalty_points) }}</h4>
                        <div class="mt-8 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-1 text-slate-400">{{ __('customer.total_spent_label') }}</p>
                                <p class="text-lg font-black italic">Rp {{ number_format($customer->sales()->where('status', 'posted')->sum('total'), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card icon="history" title="{{ __('customer.history_card_title') }}">
                    <div class="overflow-x-auto -mx-8">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-y border-slate-100">
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('customer.transaction') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('app.date') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('app.total') }}</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('app.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                @forelse ($sales as $sale)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5">
                                            <p class="font-bold text-slate-900">{{ $sale->reference_no }}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1">{{ __('app.cashier') }}: {{ $sale->cashier->name }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-slate-500 font-medium">
                                            {{ $sale->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-5 text-right font-black text-slate-900">
                                            Rp {{ number_format($sale->total, 0, ',', '.') }}
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-[9px] font-black uppercase">
                                                {{ $sale->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-20 text-center text-slate-400 italic font-bold">{{ __('customer.transaction_history_empty') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                        {{ $sales->links() }}
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
