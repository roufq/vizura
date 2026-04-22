@extends('layouts.app')

@section('title', __('adjustment.index_title'))
@section('page-title', __('adjustment.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="sliders" title="{{ __('adjustment.card_title') }}">
            <x-slot name="actions">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
                    <form method="GET" action="{{ route('stock-adjustments.index') }}" class="flex flex-wrap items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative group">
                            <i class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-brand transition-colors"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('adjustment.search_placeholder') }}" 
                                   class="bg-slate-50 border-slate-100 rounded-xl pl-14 pr-4 py-2.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 w-44 sm:w-64 transition-all border outline-none">
                        </div>

                        <!-- Date Range -->
                        <div class="flex items-center gap-3 bg-slate-50 rounded-xl px-4 py-2 border border-slate-100 shadow-sm transition-all focus-within:border-brand/30">
                            <i class="fa fa-calendar text-[10px] text-slate-300"></i>
                            <div class="flex items-center gap-2">
                                <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent border-none p-0 text-[10px] font-black text-slate-600 focus:ring-0 w-24 h-4 uppercase tracking-tighter">
                                <span class="text-slate-300 font-bold">/</span>
                                <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent border-none p-0 text-[10px] font-black text-slate-600 focus:ring-0 w-24 h-4 uppercase tracking-tighter">
                            </div>
                        </div>

                        <!-- Status Selection -->
                        <div class="relative group">
                            <select name="status" class="bg-slate-50 border-slate-100 rounded-xl px-5 py-2.5 text-xs font-bold text-slate-500 focus:ring-4 focus:ring-brand/10 transition-all border outline-none cursor-pointer appearance-none pr-10 min-w-[140px]">
                                <option value="">{{ __('adjustment.all_status') }}</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>{{ __('adjustment.pending') }}</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>{{ __('adjustment.approved') }}</option>
                            </select>
                            <i class="fa fa-angle-down absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none group-hover:text-brand transition-colors"></i>
                        </div>

                        <!-- Submit Filter -->
                        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-brand transition-all shadow-lg shadow-slate-900/10 active:scale-90">
                            <i class="fa fa-filter text-xs"></i>
                        </button>
                    </form>

                    <div class="hidden lg:block w-px h-8 bg-slate-100 mx-1"></div>

                    <div class="flex items-center gap-3">
                        @if($adjustments->where('status', 'pending')->count() > 0)
                            <button type="button" onclick="if(confirm('{{ __('adjustment.bulk_approve_confirm') ?? 'Approve all pending data?' }}')) document.getElementById('bulk-approve-form').submit();" 
                                    class="px-5 py-2.5 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all">
                                <i class="fa fa-check-double mr-2"></i>{{ __('adjustment.bulk_approve') }}
                            </button>
                        @endif
                        
                        <a href="{{ route('stock-adjustments.create') }}" class="flex items-center gap-2 px-5 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                            <i class="fa fa-plus-circle text-xs"></i>
                            <span>{{ __('adjustment.create_btn') }}</span>
                        </a>
                    </div>
                </div>

                @if($adjustments->where('status', 'pending')->count() > 0)
                    <form id="bulk-approve-form" method="POST" action="{{ route('stock-adjustments.bulk-approve') }}" class="hidden">
                        @csrf
                    </form>
                @endif
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('adjustment.ref_no') }}</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('adjustment.product_location') }}</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-center">{{ __('adjustment.delta') }}</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-center">{{ __('adjustment.status') }}</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">{{ __('adjustment.pic_admin') }}</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-center">{{ __('adjustment.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($adjustments as $adj)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <span class="text-[9px] font-black text-slate-800 bg-slate-100 px-2 py-1 rounded-lg tracking-widest">{{ $adj->reference_no ?? 'LEGACY' }}</span>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-2 italic">{{ $adj->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-sm font-black text-slate-800 leading-tight">{{ $adj->product?->name ?? 'Unknown' }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">SKU: {{ $adj->product?->sku }}</span>
                                        <span class="text-[9px] font-bold text-indigo-500 uppercase italic"><i class="fa fa-map-marker mr-1"></i> {{ $adj->location?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php $delta = (float) $adj->quantity_delta; @endphp
                                    <span class="text-sm font-black {{ $delta > 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                        {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 {{ $adj->status === 'approved' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100' }} border rounded-full text-[9px] font-black uppercase tracking-wider">
                                        {{ $adj->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <p class="text-[10px] text-slate-500 font-bold"><i class="fa fa-user-o mr-1 flex-shrink-0"></i> {{ $adj->requester?->name }}</p>
                                        @if($adj->approver)
                                            <p class="text-[10px] text-emerald-500 font-bold"><i class="fa fa-check-square-o mr-1"></i> {{ $adj->approver?->name }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    @if ($adj->status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            <form method="POST" action="{{ route('stock-adjustments.approve', $adj) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('{{ __('adjustment.approve_confirm') }}')" 
                                                        class="px-3 py-1.5 bg-brand text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:scale-105 transition-all">
                                                    {{ __('adjustment.approve_btn') }}
                                                </button>
                                            </form>
                                            <a href="{{ route('stock-adjustments.show', $adj) }}" 
                                               class="p-1.5 text-slate-400 hover:text-brand transition-colors" title="{{ __('adjustment.detail') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <form method="POST" action="{{ route('stock-adjustments.destroy', $adj) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('{{ __('adjustment.delete_confirm') }}')" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('stock-adjustments.show', $adj) }}" 
                                               class="p-1.5 text-slate-400 hover:text-brand transition-colors" title="{{ __('adjustment.detail') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest italic">{{ __('adjustment.processed') }}</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('adjustment.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $adjustments->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
