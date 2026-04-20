@extends('layouts.app')

@section('title', 'Detail Hutang: ' . $purchase->reference_no)
@section('page-title', 'Pelunasan Hutang')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8">
        <a href="{{ route('purchases.payables.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-2 px-4 focus:outline-none">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Side: Information -->
            <div class="col-span-12 lg:col-span-4 space-y-6">
                <x-ui.card icon="credit-card" title="Informasi Hutang">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">No. Dokumen</span>
                            <span class="text-sm font-bold text-slate-900 tracking-tight">{{ $purchase->reference_no }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Supplier</span>
                            <span class="text-sm font-bold text-slate-700 uppercase">{{ $purchase->supplier?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Lokasi</span>
                            <span class="text-sm font-bold text-slate-600">{{ $purchase->location?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Tagihan</span>
                            <span class="text-sm font-black text-slate-900 tracking-tight">Rp {{ number_format((float) $purchase->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Sudah Terbayar</span>
                            <span class="text-sm font-black text-emerald-600 tracking-tight">Rp {{ number_format((float) $purchase->paid_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50">
                            <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">Sisa Hutang</span>
                            <span class="text-sm font-black text-rose-600 tracking-tight">Rp {{ number_format((float) $purchase->payable_balance, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status Pembayaran</span>
                            @php
                                $statusClass = match($purchase->payment_status) {
                                    'paid' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'partial' => 'bg-warning-50 text-warning-600 border-warning-100', // Note: Check if warning exists in brand, else amber
                                    default => 'bg-slate-100 text-slate-500 border-slate-200'
                                };
                                if ($purchase->payment_status === 'partial') $statusClass = 'bg-amber-50 text-amber-600 border-amber-100';
                                
                                $statusLabel = match($purchase->payment_status) {
                                    'paid' => 'LUNAS',
                                    'partial' => 'SEBAGIAN',
                                    default => 'BELUM LUNAS'
                                };
                            @endphp
                            <span class="px-4 py-1.5 {{ $statusClass }} border rounded-full text-[9px] font-black uppercase tracking-widest">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Side: Action & History -->
            <div class="col-span-12 lg:col-span-8 space-y-8">
                <!-- Payment Form -->
                <x-ui.card icon="plus-circle" title="Input Pembayaran Baru">
                    @php($isPaid = $purchase->payment_status === 'paid')
                    @if ($isPaid)
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 mb-6">
                            <p class="text-xs font-bold text-blue-700 leading-relaxed italic">
                                <i class="fa fa-info-circle mr-2"></i> Hutang ini sudah lunas. Form pembayaran dinonaktifkan.
                            </p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('purchases.payables.store', $purchase) }}" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="paid_at" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Tanggal Bayar</label>
                                <input id="paid_at" name="paid_at" type="date" value="{{ old('paid_at', now()->toDateString()) }}" {{ $isPaid ? 'disabled' : '' }}
                                       class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                                @error('paid_at') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="method" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Metode Pembayaran</label>
                                <select id="method" name="method" required {{ $isPaid ? 'disabled' : '' }}
                                        class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all">
                                    <option value="">Pilih metode</option>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" {{ old('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('method') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="amount" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Jumlah Pembayaran</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-6 text-xs font-black text-slate-400">Rp</span>
                                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $purchase->payable_balance) }}" required {{ $isPaid ? 'disabled' : '' }}
                                           class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-sm font-black text-brand focus:ring-4 focus:ring-brand/10 transition-all placeholder:text-slate-300">
                                </div>
                                @error('amount') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="reference_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">No. Referensi (Opsional)</label>
                                <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no') }}" placeholder="Contoh: No. Cek, Ref TRF, dll" {{ $isPaid ? 'disabled' : '' }}
                                       class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-xs font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all placeholder:text-slate-300">
                                @error('reference_no') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" {{ $isPaid ? 'disabled' : '' }}
                                    class="w-full px-8 py-5 bg-brand text-white rounded-3xl font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-brand/40 hover:scale-[1.01] active:scale-95 transition-all disabled:opacity-50 disabled:grayscale disabled:scale-100 disabled:cursor-not-allowed">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </x-ui.card>

                <!-- Riwayat Pembayaran -->
                <x-ui.card icon="history" title="Riwayat Pembayaran Hutang">
                    <div class="overflow-x-auto -mx-8 -my-2">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-y border-slate-100">
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Metode</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Jumlah</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Referensi / Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($purchase->payments as $payment)
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-8 py-5">
                                            <p class="text-xs font-bold text-slate-800 tracking-tight">{{ optional($payment->paid_at)->format('d M Y') }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded text-[9px] font-black uppercase tracking-widest ring-1 ring-slate-200/50">{{ strtoupper($payment->method) }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-right font-black text-sm text-slate-900 tracking-tight">
                                            Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $payment->reference_no ?? '-' }}</p>
                                            <p class="text-[9px] font-medium text-slate-300 italic uppercase">Petugas: {{ $payment->creator?->name ?? 'System' }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-16 text-center">
                                            <div class="text-4xl opacity-20 grayscale mb-4">💳</div>
                                            <p class="text-slate-400 font-bold italic uppercase tracking-widest text-[10px]">Belum ada riwayat pembayaran untuk dokumen ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
@endsection
