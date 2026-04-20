@extends('layouts.app')

@section('title', 'Laporan Kas Harian')
@section('page-title', 'Laporan Kas Harian')

@section('content')
    <div class="space-y-8">
        <!-- Summary Mini Dash -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-ui.card icon="money" title="Total Penerimaan">
                <h3 class="text-3xl font-black text-emerald-500 tracking-tighter mt-2">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </h3>
                <p class="text-[10px] font-black text-emerald-600/50 uppercase tracking-[0.2em] mt-1 italic">Settled Revenue</p>
            </x-ui.card>

            <x-ui.card icon="credit-card" title="Ringkasan Metode" class="lg:col-span-2">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @forelse ($totalsByMethod as $method => $total)
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $method }}</p>
                            <p class="text-sm font-black text-slate-800 tracking-tight italic">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 font-bold italic">Belum ada data pembayaran.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <!-- Filter Card -->
        <x-ui.card icon="filter" title="Filter Periode Kas">
            <form method="GET" action="{{ route('reports.cash-up') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Date Range -->
                <div class="md:col-span-1 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Rentang Tanggal</label>
                    <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                        <span class="text-slate-300">-</span>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                    </div>
                </div>

                <!-- Location -->
                @if ($canSelectLocations)
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cabang / Lokasi</label>
                        <select name="location_id" class="w-full bg-slate-50 border-none rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer appearance-none">
                            <option value="">📍 Lokasi Aktif</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($canViewAll)
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-4 py-3 rounded-xl border border-dotted border-slate-200 hover:border-brand/20 transition-all w-full">
                                <input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span class="text-xs font-bold text-slate-600">Semua Cabang</span>
                            </label>
                        </div>
                    @endif
                @endif

                <!-- Action -->
                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 px-6 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">Filter</button>
                    <a href="{{ route('reports.cash-up') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center flex items-center justify-center hover:bg-slate-200 transition-colors">Reset</a>
                </div>
            </form>
        </x-ui.card>

        <!-- Table Card -->
        <x-ui.card icon="table" title="Histori Kas Per Hari">
            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Metode Pembayaran</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($rows as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ \Carbon\Carbon::parse($row->sale_date)->format('d F Y') }}</p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">{{ \Carbon\Carbon::parse($row->sale_date)->format('l') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center px-3 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest italic shadow-sm">
                                        {{ $row->method }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-sm font-black text-slate-800 tabular-nums italic">Rp {{ number_format((float) $row->total, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20">
                                        <div class="w-16 h-16 mb-4 border-4 border-slate-300 rounded-2xl flex items-center justify-center text-3xl">🏦</div>
                                        <p class="text-slate-500 font-bold text-sm">Belum ada data penerimaan kas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
@endsection
