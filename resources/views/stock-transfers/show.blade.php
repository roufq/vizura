@extends('layouts.app')

@section('title', __('transfer.detail_title') ?? 'Transfer Detail')
@section('page-title', __('transfer.page_title'))

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 pb-12">
        {{-- Navigation Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('stock-transfers.index') }}" 
                   class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-slate-400 hover:text-brand hover:shadow-md transition-all border border-slate-100">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ __('transfer.detail_title') ?? 'Transfer Detail' }}</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Reference: {{ $stockTransfer->reference_no }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @if ($stockTransfer->status === 'draft')
                    <form method="POST" action="{{ route('stock-transfers.send', $stockTransfer) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('transfer.confirm_send') }}')" 
                                class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:scale-105 transition-all">
                            <i class="fa fa-paper-plane mr-2"></i>{{ __('transfer.btn_send') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('stock-transfers.destroy', $stockTransfer) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('{{ __('transfer.confirm_delete') }}')" 
                                class="px-6 py-2.5 bg-white text-slate-400 border border-slate-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                            <i class="fa fa-trash mr-2"></i>{{ __('transfer.cancel') }}
                        </button>
                    </form>
                @elseif ($stockTransfer->status === 'sent')
                    <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('transfer.confirm_receive') }}')" 
                                class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:scale-105 transition-all">
                            <i class="fa fa-check-circle mr-2"></i>{{ __('transfer.btn_receive') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left column: Route & Items --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Transfer Route --}}
                <x-ui.card>
                    <div class="relative flex items-center justify-between py-4">
                        {{-- Connection Line --}}
                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full px-20">
                            <div class="h-px bg-slate-100 border-b-2 border-dashed border-slate-100"></div>
                        </div>

                        {{-- Source --}}
                        <div class="relative z-10 flex flex-col items-center text-center gap-4">
                            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-slate-400 shadow-sm border border-slate-100/50">
                                <i class="fa fa-building fa-xl text-slate-300"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ __('transfer.source') }}</p>
                                <h3 class="text-sm font-black text-slate-800">{{ $stockTransfer->sourceLocation?->name }}</h3>
                            </div>
                        </div>

                        {{-- Icon --}}
                        <div class="relative z-10 w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-500 shadow-inner">
                            <i class="fa fa-truck animate-pulse"></i>
                        </div>

                        {{-- Destination --}}
                        <div class="relative z-10 flex flex-col items-center text-center gap-4">
                            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-brand shadow-sm border border-brand/10">
                                <i class="fa fa-map-marker fa-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-brand uppercase tracking-widest mb-1">{{ __('transfer.destination') }}</p>
                                <h3 class="text-sm font-black text-slate-800">{{ $stockTransfer->destinationLocation?->name }}</h3>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Item List --}}
                <x-ui.card icon="box" title="{{ __('transfer.item_detail') }}">
                    <div class="overflow-x-auto -mx-8">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-y border-slate-100">
                                <tr>
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('transfer.product') ?? 'Product' }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('transfer.qty') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($stockTransfer->items as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase">
                                                    {{ substr($item->product?->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $item->product?->name }}</p>
                                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1 italic">SKU: {{ $item->product?->sku }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-right font-black text-sm text-slate-700">
                                            {{ number_format($item->quantity, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50/50">
                                <tr>
                                    <td class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('transfer.total_items') ?? 'Total Items' }}</td>
                                    <td class="px-6 py-4 text-right font-black text-brand text-lg">
                                        {{ number_format($stockTransfer->items->sum('quantity'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            {{-- Right column: Meta Info --}}
            <div class="space-y-8">
                <x-ui.card icon="history" title="Log & Timeline">
                    <div class="space-y-6">
                        {{-- Requested --}}
                        <div class="relative pl-6 border-l-2 border-slate-50">
                            <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-300"></div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ __('transfer.requested_by') ?? 'Requested By' }}</label>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">{{ $stockTransfer->requester?->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-tighter italic">{{ $stockTransfer->created_at?->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Sent --}}
                        @if($stockTransfer->sent_at)
                            <div class="relative pl-6 border-l-2 border-indigo-50">
                                <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-indigo-500 shadow-sm shadow-indigo-100"></div>
                                <label class="text-[9px] font-black text-indigo-500 uppercase tracking-widest block mb-2">{{ __('transfer.sent_by') ?? 'Sent By' }}</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 text-xs shadow-sm">
                                        <i class="fa fa-truck"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">{{ $stockTransfer->sender?->name }}</p>
                                        <p class="text-[10px] font-bold text-indigo-500 mt-0.5 tracking-tighter italic">{{ $stockTransfer->sent_at?->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Received --}}
                        @if($stockTransfer->received_at)
                            <div class="relative pl-6 border-l-2 border-emerald-50">
                                <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/20"></div>
                                <label class="text-[9px] font-black text-emerald-500 uppercase tracking-widest block mb-2">{{ __('transfer.received_by') ?? 'Received By' }}</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs shadow-sm">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">{{ $stockTransfer->receiver?->name }}</p>
                                        <p class="text-[10px] font-bold text-emerald-500 mt-0.5 tracking-tighter italic">{{ $stockTransfer->received_at?->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-50 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ __('transfer.status') }}</span>
                                @php
                                    $statusColor = match($stockTransfer->status) {
                                        'received' => 'text-emerald-500',
                                        'sent' => 'text-indigo-500',
                                        default => 'text-amber-500'
                                    };
                                @endphp
                                <span class="text-[10px] font-black uppercase tracking-widest {{ $statusColor }}">{{ $stockTransfer->status }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Contextual Actions for User --}}
                @if ($stockTransfer->status === 'draft')
                    <div class="p-6 bg-brand/5 border border-brand/10 rounded-[2rem] space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand text-white flex items-center justify-center shadow-lg shadow-brand/20">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 tracking-tight">{{ __('transfer.action_needed_title') ?? 'Ready to Send?' }}</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed italic">
                            {{ __('transfer.action_needed_desc') ?? 'Check all items before sending. This will adjust the stock in the source location.' }}
                        </p>
                        <form method="POST" action="{{ route('stock-transfers.send', $stockTransfer) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('{{ __('transfer.confirm_send') }}')" 
                                    class="w-full py-3 bg-brand text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/30 hover:bg-brand-dark transition-all">
                                {{ __('transfer.btn_send_now') ?? 'Send Shipment Now' }}
                            </button>
                        </form>
                    </div>
                @elseif ($stockTransfer->status === 'sent')
                    <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem] space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                <i class="fa fa-download"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 tracking-tight">{{ __('transfer.receive_shipment') ?? 'Receive Shipment' }}</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed italic">
                            {{ __('transfer.receive_shipment_desc') ?? 'Verify all items upon arrival. This will increase stock in the destination location.' }}
                        </p>
                        <form method="POST" action="{{ route('stock-transfers.receive', $stockTransfer) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('{{ __('transfer.confirm_receive') }}')" 
                                    class="w-full py-3 bg-emerald-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-500/30 hover:bg-emerald-600 transition-all">
                                {{ __('transfer.btn_receive_now') ?? 'Confirm Receipt' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
