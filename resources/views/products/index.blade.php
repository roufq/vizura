@extends('layouts.app')

@section('title', __('product.index_title'))
@section('page-title', __('app.menu.products'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="cube" title="{{ __('product.card_title') }}">
            <x-slot name="actions">
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('products.index') }}" class="relative group">
                        <i class="fa fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs group-focus-within:text-brand transition-colors"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('product.search_placeholder') }}" 
                               class="bg-slate-50 border-slate-100 rounded-xl pl-14 pr-5 py-2.5 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 w-44 sm:w-64 transition-all border outline-none">
                        
                        @if ($search !== '')
                            <a href="{{ route('products.index') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600">
                                <i class="fa fa-times-circle"></i>
                            </a>
                        @endif
                    </form>

                    <div class="w-px h-8 bg-slate-100 mx-1"></div>

                    <a href="{{ route('products.create') }}" class="flex items-center gap-2 px-5 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 active:scale-95 transition-all">
                        <i class="fa fa-plus text-xs"></i>
                        <span class="hidden sm:inline">{{ __('product.add_product') }}</span>
                    </a>
                </div>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('product.info_header') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('product.category_unit') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('product.sale_price') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('product.cost_price') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('product.status') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('product.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase">
                                            {{ substr($product->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $product->name }}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1">
                                                SKU: <span class="text-slate-600">{{ $product->sku }}</span> | Barcode: {{ $product->barcode ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[10px] font-bold uppercase">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                        <p class="text-[10px] text-slate-500 font-medium italic">Unit: {{ $product->unit?->name ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-slate-700">
                                    Rp {{ number_format((float) $product->sale_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-right font-bold text-sm text-slate-400">
                                    Rp {{ number_format((float) $product->cost_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-center space-y-2">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="px-3 py-1 {{ $product->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }} rounded-full text-[9px] font-black uppercase">
                                            {{ $product->is_active ? __('product.active') : __('product.inactive') }}
                                        </span>
                                        @if($product->block_when_out_of_stock)
                                            <span class="text-[8px] font-bold text-amber-500 uppercase tracking-tighter">⚠️ {{ __('product.block_out_of_stock') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('products.edit', $product) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all" title="{{ __('app.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('{{ __('product.delete_confirm') }}')">
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
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="mb-4 text-4xl">📦</div>
                                    <p class="text-slate-400 font-bold mb-6">{{ __('product.no_data') }}</p>
                                    <a href="{{ route('products.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20 uppercase text-xs tracking-widest">{{ __('product.add_now') }}</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $products->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
