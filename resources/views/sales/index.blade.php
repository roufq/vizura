@extends('layouts.app')

@section('title', 'Histori Penjualan')
@section('page-title', 'Penjualan')

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="shopping-cart" title="Riwayat Transaksi POS">
            <x-slot name="actions">
                <a href="{{ route('sales.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                    <i class="fa fa-plus-circle mr-2 text-sm"></i>Kasir Baru
                </a>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">No. Ref / Tanggal</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kasir & Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total Akhir</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $sale->reference_no }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1 italic">
                                        {{ ($sale->posted_at ?? $sale->created_at)?->format('d M Y, H:i') }}
                                    </p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs font-bold text-slate-700">{{ $sale->cashier?->name ?? 'System' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">Customer: <span class="text-slate-500 font-bold uppercase">{{ $sale->customer_name ?? 'CASH' }}</span></p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-widest">
                                        {{ $sale->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $statusColor = match($sale->status) {
                                            'posted' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                            'draft' => 'bg-amber-50 text-amber-600 border border-amber-100',
                                            'void', 'return' => 'bg-rose-50 text-rose-600 border border-rose-100',
                                            default => 'bg-slate-50 text-slate-400 border border-slate-100'
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 {{ $statusColor }} rounded-full text-[9px] font-black uppercase shadow-sm">
                                        {{ $sale->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-slate-900">
                                    Rp {{ number_format((float) $sale->total, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if ($sale->status === 'posted' && $sale->type === 'sale')
                                            <a href="{{ route('sales.receipt', $sale) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-slate-900 hover:text-white transition-all" title="Cetak Struk">
                                                <i class="fa fa-print"></i>
                                            </a>
                                            <form method="POST" action="{{ route('sales.void', $sale) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="void_reason" value="Void via UI">
                                                <button type="submit" onclick="return confirm('Batalkan (Void) transaksi ini? Stok akan kembali.')" class="p-2.5 bg-slate-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all" title="Void">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                            </form>
                                        @elseif ($sale->status === 'draft')
                                            <a href="{{ route('sales.resume', $sale) }}" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-bold uppercase hover:bg-indigo-600 hover:text-white transition-all">Lanjutkan</a>
                                        @else
                                             <a href="{{ route('sales.receipt', $sale) }}" class="p-2.5 bg-slate-50 text-slate-400 rounded-xl" title="Struk">
                                                <i class="fa fa-print text-sm opacity-50"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="mb-4 text-4xl">💰</div>
                                    <p class="text-slate-400 font-bold italic">Belum ada catatan penjualan untuk saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $sales->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
