@extends('layouts.app')

@section('title', __('category.index_title'))
@section('page-title', __('category.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="tags" title="{{ __('category.card_title') }}">
            <x-slot name="actions">
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('categories.index') }}" class="relative group">
                        <i class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-brand transition-colors"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('category.search_placeholder') }}" 
                               class="bg-slate-50 border-slate-100 rounded-xl pl-14 pr-5 py-2.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 w-44 sm:w-64 transition-all border outline-none">
                    </form>

                    <div class="w-px h-8 bg-slate-100 mx-1"></div>

                    <a href="{{ route('categories.create') }}" class="flex items-center gap-2 px-5 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                        <i class="fa fa-plus text-xs"></i>
                        <span class="hidden sm:inline">{{ __('category.add_category') }}</span>
                    </a>
                </div>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('category.name') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('category.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('category.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900">{{ $category->name }}</p>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="px-3 py-1 {{ $category->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                        {{ $category->is_active ? __('category.active') : __('category.inactive') }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('categories.edit', $category) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('{{ __('category.delete_confirm') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold mb-6">{{ __('category.no_data') }}</p>
                                    <a href="{{ route('categories.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20">{{ __('category.add_now') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $categories->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
