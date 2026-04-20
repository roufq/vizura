@extends('layouts.app')

@section('title', 'Laporan Arus Kas')
@section('page-title', 'Laporan Arus Kas')

@section('content')
    <div class="space-y-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-ui.card icon="exchange" title="Total Perubahan Kas">
                <h3 class="text-3xl font-black {{ $netChange >= 0 ? 'text-emerald-500' : 'text-rose-500' }} tracking-tighter mt-2">
                    {{ $netChange >= 0 ? '+' : '' }} Rp {{ number_format($netChange, 0, ',', '.') }}
                </h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic">Net Cash Flow</p>
            </x-ui.card>
        </div>

        <!-- Filter Card -->
        <x-ui.card icon="filter" title="Filter Analisa Kas">
            <form method="GET" action="{{ route('reports.cash-flow') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Lokasi Kerja</label>
                        <select name="location_id" class="w-full bg-slate-50 border-none rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all cursor-pointer appearance-none">
                            <option value="">📍 Cabang Aktif</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($canViewAll)
                        <div class="flex items-end pb-1 px-1">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-50 px-4 py-3 rounded-xl border border-dotted border-slate-200 hover:border-brand/20 transition-all w-full">
                                <input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span class="text-xs font-bold text-slate-600">Gabung Semua Data</span>
                            </label>
                        </div>
                    @endif
                @endif

                <!-- Buttons -->
                <div class="flex items-end gap-3">
                    <button type="submit" class="flex-1 px-6 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">Analisa</button>
                    <a href="{{ route('reports.cash-flow') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center flex items-center justify-center transition-colors hover:bg-slate-200">Reset</a>
                </div>
            </form>
        </x-ui.card>

        <!-- Table Card -->
        <x-ui.card icon="table" title="Pergerakan Per Akun Kas">
            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Rekening / Akun Kas</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Net Change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($cashMovements as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-bold text-slate-900 leading-tight">{{ $row['account']->name }}</p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Liquid Asset</p>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-sm {{ $row['total'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }} tabular-nums">
                                    {{ $row['total'] >= 0 ? '+' : '' }} Rp {{ number_format($row['total'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20">
                                        <div class="w-16 h-16 mb-4 border-4 border-slate-300 rounded-2xl flex items-center justify-center text-3xl">💱</div>
                                        <p class="text-slate-500 font-bold text-sm">Tidak ada pergerakan kas yang tercatat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-900 text-white rounded-b-3xl overflow-hidden">
                        <tr>
                            <td class="px-8 py-6 text-xs font-black uppercase tracking-[0.3em]">Total Akhir Perubahan</td>
                            <td class="px-8 py-6 text-right text-xl font-black italic tracking-tighter tabular-nums">
                                {{ $netChange >= 0 ? '+' : '' }} Rp {{ number_format($netChange, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>
    </div>
@endsection
