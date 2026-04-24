@extends('layouts.app')

@section('title', __('transfer.index_title'))
@section('page-title', __('transfer.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="truck" title="{{ __('transfer.card_title') }}">
            <x-slot name="actions">
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
                    <form method="GET" action="{{ route('stock-transfers.index') }}" class="flex flex-wrap items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative group">
                            <i class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-brand transition-colors"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('transfer.search_placeholder') ?? 'Search Ref...' }}" 
                                   class="bg-slate-50 border-slate-100 rounded-xl pl-14 pr-4 py-2.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 w-44 sm:w-64 transition-all border outline-none">
                        </div>

                        <!-- Location Filter -->
                        <div class="relative group">
                            <select name="location_id" class="bg-slate-50 border-slate-100 rounded-xl px-5 py-2.5 text-xs font-bold text-slate-500 focus:ring-4 focus:ring-brand/10 transition-all border outline-none cursor-pointer appearance-none pr-10 min-w-[140px]">
                                <option value="">{{ __('transfer.all_locations') ?? 'All Locations' }}</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ (int) $locationId === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <i class="fa fa-angle-down absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none group-hover:text-brand transition-colors"></i>
                        </div>

                        <!-- Status Selection -->
                        <div class="relative group">
                            <select name="status" class="bg-slate-50 border-slate-100 rounded-xl px-5 py-2.5 text-xs font-bold text-slate-500 focus:ring-4 focus:ring-brand/10 transition-all border outline-none cursor-pointer appearance-none pr-10 min-w-[140px]">
                                <option value="">{{ __('transfer.all_status') ?? 'All Status' }}</option>
                                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>{{ __('transfer.draft') }}</option>
                                <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>{{ __('transfer.sent') }}</option>
                                <option value="received" {{ $status === 'received' ? 'selected' : '' }}>{{ __('transfer.received') }}</option>
                            </select>
                            <i class="fa fa-angle-down absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400 pointer-events-none group-hover:text-brand transition-colors"></i>
                        </div>

                        <!-- Submit Filter -->
                        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-brand transition-all shadow-lg shadow-slate-900/10 active:scale-95">
                            <i class="fa fa-filter text-xs"></i>
                        </button>
                    </form>

                    <div class="hidden lg:block w-px h-8 bg-slate-100 mx-1"></div>

                    <a href="{{ route('stock-transfers.create') }}" class="flex items-center gap-2 px-6 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                        <i class="fa fa-plus-circle text-xs"></i>
                        <span>{{ __('transfer.create_btn') }}</span>
                    </a>
                </div>
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
                                        <a href="{{ route('stock-transfers.show', $t) }}" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl hover:bg-brand/10 hover:text-brand transition-all" title="{{ __('app.detail') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        @if ($t->status === 'draft')
                                            <form method="POST" action="{{ route('stock-transfers.send', $t) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_send') }}')" 
                                                        class="px-4 py-1.5 bg-brand text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:scale-105 transition-all">
                                                    {{ __('transfer.btn_send') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('stock-transfers.destroy', $t) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_delete') }}')" class="p-2.5 bg-slate-50 text-slate-300 rounded-xl hover:bg-rose-50 hover:text-rose-500 transition-all">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @elseif ($t->status === 'sent')
                                            <form method="POST" action="{{ route('stock-transfers.receive', $t) }}">
                                                @csrf
                                                <button type="submit" onclick="return confirm('{{ __('transfer.confirm_receive') }}')" 
                                                        class="px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:scale-105 transition-all">
                                                    {{ __('transfer.btn_receive') }}
                                                </button>
                                            </form>
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
