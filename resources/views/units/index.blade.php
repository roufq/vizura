@extends('layouts.app')

@section('title', 'Satuan Produk')
@section('page-title', 'Satuan')

@section('content')
    <div class="max-w-4xl">
        <x-ui.card icon="balance-scale" title="Manajemen Satuan">
            <x-slot name="actions">
                <form method="GET" action="{{ route('units.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari satuan..." 
                               class="bg-slate-50 border-transparent rounded-xl px-5 py-2 text-sm focus:ring-2 focus:ring-brand/20 w-48 transition-all">
                    </div>
                    <a href="{{ route('units.create') }}" class="px-5 py-2 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        <i class="fa fa-plus mr-2"></i>Tambah Satuan
                    </a>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Satuan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Singkatan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
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
                                        {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('units.edit', $unit) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('units.destroy', $unit) }}" onsubmit="return confirm('Hapus satuan ini?')">
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
                                    <p class="text-slate-400 font-bold mb-6">Belum ada satuan ditemukan.</p>
                                    <a href="{{ route('units.create') }}" class="px-8 py-3 bg-brand text-white rounded-2xl font-bold shadow-xl shadow-brand/20">Tambah Sekarang</a>
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
