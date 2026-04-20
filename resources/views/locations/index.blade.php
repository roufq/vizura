@extends('layouts.app')

@section('title', __('location.index_title'))
@section('page-title', __('location.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="map-marker" title="{{ __('location.card_title') }}">
            <x-slot name="actions">
                <form method="GET" action="{{ route('locations.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="{{ __('location.search_placeholder') }}"
                            class="bg-slate-50 border-transparent rounded-xl px-5 py-2.5 text-sm focus:ring-2 focus:ring-brand/20 w-64 transition-all">
                    </div>
                    <a href="{{ route('locations.create') }}"
                        class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        <i class="fa fa-plus mr-2"></i>{{ __('location.add_location') }}
                    </a>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                {{ __('location.branch_code') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                {{ __('location.contact_info') }}</th>
                            <th
                                class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                {{ __('location.status') }}</th>
                            <th
                                class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                                {{ __('location.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($locations as $loc)
                            @php $isActiveBranch = (int) session('active_location_id') === (int) $loc->id; @endphp
                            <tr
                                class="hover:bg-slate-50/50 transition-colors group {{ $isActiveBranch ? 'bg-brand/[0.02]' : '' }}">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl {{ $loc->toko_pusat ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-black text-xs uppercase shadow-sm">
                                            {{ $loc->code }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 leading-tight">
                                                {{ $loc->name }}
                                                @if($loc->toko_pusat)
                                                    <span
                                                        class="ml-2 text-[8px] font-black bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded uppercase">{{ __('location.headquarters') }}</span>
                                                @endif
                                            </p>
                                            @if($isActiveBranch)
                                                <p class="text-[9px] font-black text-brand uppercase tracking-widest mt-1">●
                                                    {{ __('location.currently_used') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-600 truncate max-w-xs">
                                            {{ $loc->address ?? __('location.address_not_set') }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium italic"><i
                                                class="fa fa-phone mr-1"></i> {{ $loc->phone ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="px-3 py-1 {{ $loc->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                        {{ $loc->is_active ? __('location.active') : __('location.inactive') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('locations.edit', $loc) }}"
                                            class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all"
                                            title="{{ __('app.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        @if ($canSync)
                                            <form method="POST" action="{{ route('locations.sync-stock', $loc) }}"
                                                onsubmit="return confirm('{{ __('location.sync_stock_confirm') }}')">
                                                @csrf
                                                <button type="submit"
                                                    class="p-2.5 bg-slate-50 text-amber-500 rounded-xl hover:bg-amber-50 transition-all"
                                                    title="{{ __('location.sync_stock') }}">
                                                    <i class="fa fa-refresh"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (!$loc->toko_pusat)
                                            <form method="POST" action="{{ route('locations.destroy', $loc) }}"
                                                onsubmit="return confirm('{{ __('location.delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all"
                                                    title="{{ __('app.delete') }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center text-slate-400 font-bold italic">
                                    {{ __('location.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $locations->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection