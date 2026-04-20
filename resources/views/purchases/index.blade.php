@extends('layouts.app')

@section('title', __('purchase.index_title'))
@section('page-title', __('purchase.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="shopping-basket" title="{{ __('purchase.card_title') }}">
            <x-slot name="actions">
                <a href="{{ route('purchases.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                    <i class="fa fa-plus-circle mr-2"></i>{{ __('purchase.create_btn') }}
                </a>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('purchase.doc_no') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('purchase.supplier_receiver') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('purchase.total') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('purchase.status_item') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('purchase.status_pay') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($purchases as $p)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $p->reference_no }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 italic">{{ $p->created_at?->format('d/m/y H:i') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs font-bold text-slate-700">{{ $p->supplier?->name ?? 'None' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">{{ __('purchase.received_by') }}: <span class="text-slate-500 font-bold uppercase">{{ $p->receiver?->name ?? '-' }}</span></p>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-slate-800">
                                    Rp {{ number_format((float) $p->total, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 {{ $p->status === 'received' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @php
                                        $payColor = match($p->payment_status) {
                                            'paid' => 'bg-blue-50 text-blue-600',
                                            'partial' => 'bg-amber-50 text-amber-600',
                                            default => 'bg-rose-50 text-rose-600'
                                        };
                                        $payLabel = match($p->payment_status) {
                                            'paid' => __('purchase.status_paid'),
                                            'partial' => __('purchase.status_partial'),
                                            default => __('purchase.status_unpaid')
                                        };
                                    @endphp
                                    <span class="px-3 py-1 {{ $payColor }} rounded-full text-[9px] font-black uppercase shadow-sm border border-black/5">
                                        {{ $payLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('purchase.no_data') }}</p>
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
