@extends('layouts.app')

@section('title', 'Laporan Laba Rugi (Profit & Loss)')
@section('page-title', 'Laporan')

@section('content')
    <div class="space-y-8">
        
        <!-- Filter Card -->
        <x-ui.card icon="filter" title="Filter Periode Analisa">
            <form method="GET" action="{{ route('reports.income-statement') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Rentang Tanggal</label>
                    <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-4 py-3 border border-slate-100">
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                        <span class="text-slate-300">-</span>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="bg-transparent border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 flex-1">
                    </div>
                </div>

                @if ($canSelectLocations)
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cabang</label>
                        <select name="location_id" class="w-full bg-slate-50 border-transparent rounded-xl px-5 py-3.5 text-xs font-bold text-slate-700">
                            <option value="">Lokasi Aktif</option>
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" {{ (int) ($filters['location_id'] ?? 0) === $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-3 md:col-span-2">
                    <button type="submit" class="px-8 py-3.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all">Tampilkan Laporan</button>
                    <a href="{{ route('reports.income-statement') }}" class="px-5 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase text-center">Reset</a>
                </div>
            </form>
        </x-ui.card>

        <!-- Income Statement Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-ui.card icon="plus-circle" title="Pendapatan (Sales)">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total Penjualan</p>
            </x-ui.card>

            <x-ui.card icon="minus-circle" title="HPP (COGS)">
                <h3 class="text-2xl font-black text-rose-500 tracking-tighter mt-2">Rp {{ number_format($cogsTotal, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Modal Penjualan</p>
            </x-ui.card>

            <x-ui.card icon="line-chart" title="Laba Kotor">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter mt-2">Rp {{ number_format($grossProfit, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Sales - COGS</p>
            </x-ui.card>

            <x-ui.card icon="credit-card" title="Biaya Ops">
                <h3 class="text-2xl font-black text-rose-500 tracking-tighter mt-2">Rp {{ number_format($expenseTotal, 0, ',', '.') }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Total Pengeluaran</p>
            </x-ui.card>
        </div>

        <!-- Net Profit Focus Card -->
        <div class="bg-slate-900 p-12 rounded-[3.5rem] text-white shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand/10 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8 text-center md:text-left">
                <div>
                     <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 mb-4 italic">Bottom Line Results</p>
                     <h1 class="text-sm font-bold text-slate-400 mb-2">Estimasi Laba Bersih (Net Profit)</h1>
                     <h2 class="text-6xl font-black tracking-tighter {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                     </h2>
                </div>
                
                <div class="px-8 py-4 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-md">
                     <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Margin</p>
                     @php
                        $margin = $incomeTotal > 0 ? ($netProfit / $incomeTotal) * 100 : 0;
                     @endphp
                     <h4 class="text-2xl font-black text-white italic">{{ number_format($margin, 1) }}%</h4>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 p-8 rounded-[2.5rem] border border-blue-100 flex items-start gap-5">
            <div class="p-4 bg-white rounded-2xl shadow-sm text-blue-500 text-xl"><i class="fa fa-info-circle"></i></div>
            <div>
                 <h5 class="text-sm font-black text-blue-900 uppercase tracking-widest mb-2">Penjelasan Laporan</h5>
                 <p class="text-xs text-blue-700/70 font-bold leading-relaxed max-w-2xl">
                    Laporan ini menampilkan estimasi laba rugi berdasarkan data transaksi yang tercatat di sistem. 
                    HPP dihitung berdasarkan biaya rata-rata (Average Cost) pada saat barang terjual. 
                    Biaya Operasional mencakup seluruh pengeluaran kas yang didefinisikan sebagai biaya pada menu Biaya.
                 </p>
            </div>
        </div>
    </div>
@endsection
