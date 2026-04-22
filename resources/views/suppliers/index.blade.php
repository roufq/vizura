@extends('layouts.app')

@section('title', __('supplier.index_title'))
@section('page-title', __('supplier.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="address-book" title="{{ __('supplier.card_title') }}">
            <x-slot name="actions">
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('suppliers.index') }}" class="relative group">
                        <i class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-brand transition-colors"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('supplier.search_placeholder') }}" 
                               class="bg-slate-50 border-slate-100 rounded-xl pl-14 pr-5 py-2.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 w-44 sm:w-64 transition-all border outline-none">
                    </form>

                    <div class="w-px h-8 bg-slate-100 mx-1"></div>

                    <a href="{{ route('suppliers.create') }}" class="flex items-center gap-2 px-5 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                        <i class="fa fa-plus text-xs"></i>
                        <span class="hidden sm:inline">{{ __('supplier.add_supplier') }}</span>
                    </a>
                </div>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('supplier.info_header') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('supplier.contact') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('supplier.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('supplier.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($suppliers as $supplier)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-sm uppercase">
                                            {{ substr($supplier->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $supplier->name }}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1 italic">
                                                ID: {{ str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                            <i class="fa fa-phone opacity-50 w-4"></i>
                                            <span>{{ $supplier->phone ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                            <i class="fa fa-envelope-o opacity-50 w-4"></i>
                                            <span>{{ $supplier->email ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                        {{ $supplier->is_active ? __('supplier.active') : __('supplier.inactive') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all" title="{{ __('app.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('{{ __('supplier.delete_confirm') }}')">
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
                                    <p class="text-slate-400 font-bold mb-6">{{ __('supplier.no_data') }}</p>
                                    <a href="{{ route('suppliers.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20 uppercase text-xs tracking-widest">{{ __('supplier.add_now') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $suppliers->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
