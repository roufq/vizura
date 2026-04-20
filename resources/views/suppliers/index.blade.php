@extends('layouts.app')

@section('title', 'Manajemen Supplier')
@section('page-title', 'Supplier')

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="address-book" title="Daftar Rekanan Supplier">
            <x-slot name="actions">
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kontak..." 
                               class="bg-slate-50 border-transparent rounded-xl px-5 py-2.5 text-sm focus:ring-2 focus:ring-brand/20 w-64 transition-all">
                    </div>
                    <a href="{{ route('suppliers.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        <i class="fa fa-plus mr-2"></i>Tambah Supplier
                    </a>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Supplier</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kontak</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
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
                                        {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus supplier ini?')">
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
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold mb-6">Belum ada supplier ditemukan.</p>
                                    <a href="{{ route('suppliers.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20">Tambah Sekarang</a>
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
