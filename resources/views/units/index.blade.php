@extends('layouts.app')

@section('title', __('unit.index_title'))
@section('page-title', __('unit.page_title'))

@section('content')
    <div class="max-w-4xl">
        <x-ui.card icon="balance-scale" title="{{ __('unit.card_title') }}">
            <x-slot name="actions">
                <form method="GET" action="{{ route('units.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('unit.search_placeholder') }}" 
                               class="bg-slate-50 border-transparent rounded-xl px-5 py-2 text-sm focus:ring-2 focus:ring-brand/20 w-48 transition-all">
                    </div>
                    <a href="{{ route('units.create') }}" class="px-5 py-2 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        <i class="fa fa-plus mr-2"></i>{{ __('unit.add_unit') }}
                    </a>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('unit.name') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('unit.abbreviation') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('unit.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('unit.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($units as $unit)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900">{{ $unit->name }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ $unit->abbreviation ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 {{ $unit->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                        {{ $unit->is_active ? __('unit.active') : __('unit.inactive') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('units.edit', $unit) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all" title="{{ __('app.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('{{ __('unit.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all" title="{{ __('app.delete') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold mb-6">{{ __('unit.no_data') }}</p>
                                    <a href="{{ route('units.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20 uppercase text-xs tracking-widest">{{ __('unit.add_now') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $units->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
