@extends('layouts.app')

@section('title', __('transfer.index_title'))
@section('page-title', __('transfer.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="truck" title="{{ __('transfer.card_title') }}">
            <x-slot name="actions">
                <a href="{{ route('stock-transfers.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                    <i class="fa fa-plus-circle mr-2"></i>{{ __('transfer.create_btn') }}
                </a>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('transfer.ref_date') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('transfer.flow') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('transfer.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('transfer.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($transfers as $t)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $t->reference_no }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 italic">{{ $t->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="text-center">
                                            <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">{{ __('transfer.source') }}</p>
                                            <p class="text-xs font-bold text-slate-700">{{ $t->sourceLocation?->name }}</p>
                                        </div>
                                        <div class="px-2 text-slate-200">
                                            <i class="fa fa-long-arrow-right"></i>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">{{ __('transfer.destination') }}</p>
                                            <p class="text-xs font-bold text-indigo-600">{{ $t->destinationLocation?->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusColor = match($t->status) {
                                            'received' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'sent' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            default => 'bg-amber-50 text-amber-600 border-amber-100'
                                        };
                                        $statusLabel = match($t->status) {
                                            'received' => __('transfer.received'),
                                            'sent' => __('transfer.sent'),
                                            default => __('transfer.draft')
                                        };
                                    @endphp
                                    <span class="px-3 py-1 {{ $statusColor }} border rounded-full text-[9px] font-black uppercase tracking-wider">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if ($t->status === 'draft')
                                            <form method="POST" action="{{ route('stock-transfers.send', $t) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_send') }}')" 
                                                        class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:scale-105 transition-all">
                                                    {{ __('transfer.btn_send') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('stock-transfers.destroy', $t) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_delete') }}')" class="p-1.5 text-slate-300 hover:text-rose-500">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @elseif ($t->status === 'sent')
                                            <form method="POST" action="{{ route('stock-transfers.receive', $t) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_receive') }}')" 
                                                        class="px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:scale-105 transition-all">
                                                    {{ __('transfer.btn_receive') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="flex items-center justify-center text-emerald-500 gap-1">
                                                <i class="fa fa-check-circle"></i>
                                                <span class="text-[9px] font-black uppercase">{{ __('transfer.finished') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('transfer.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $transfers->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
