@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <!-- Location Picker & Summary Title -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand/5 rounded-full blur-3xl"></div>
            
            <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('dashboard.location_summary') }}</h1>
                    <p class="text-slate-500 font-medium">{{ __('dashboard.monitoring_performance') }} <span class="text-brand font-bold">{{ $locationName }}</span></p>
                </div>

                @if ($canSelectLocations)
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3 bg-slate-50 p-2 rounded-3xl border border-slate-100">
                        <div class="relative group min-w-[200px]">
                            <select id="location_id" name="location_id" class="w-full appearance-none bg-white border border-slate-200 rounded-2xl px-5 py-2.5 pr-10 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all cursor-pointer shadow-sm">
                                <option value="">📍 {{ __('dashboard.active_location') }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-hover:text-brand transition-colors">
                                <i class="fa fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                        @if ($canViewAll)
                            <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2.5 rounded-2xl border border-slate-200 hover:border-brand/30 transition-all shadow-sm">
                                <input type="checkbox" name="all_locations" value="1" {{ ($filters['all_locations'] ?? false) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-brand focus:ring-brand">
                                <span class="text-xs font-bold text-slate-600">{{ __('dashboard.all_locations') }}</span>
                            </label>
                        @endif

                        <button type="submit" class="bg-brand text-white px-6 py-2.5 rounded-2xl font-bold text-sm shadow-md shadow-brand/20 hover:bg-brand-dark transition-all shrink-0">{{ __('dashboard.apply') }}</button>
                    </form>
                @endif
            </div>

            @if ($locationName === '-')
                <div class="mt-8 p-4 bg-rose-50 border border-rose-100 rounded-3xl flex items-center gap-4 text-rose-700">
                    <div class="w-10 h-10 rounded-2xl bg-white flex items-center justify-center text-lg">💡</div>
                    <p class="text-sm font-semibold">{{ __('dashboard.location_not_selected') }} <a href="{{ route('locations.active') }}" class="underline font-black">{{ __('dashboard.select_location_prompt') }}</a></p>
                </div>
            @endif
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $metrics = [];
                if ($isOwner) {
                    $metrics = [
                        ['label' => __('dashboard.metrics.total_sales_month'), 'value' => $salesMonth, 'icon' => 'shopping-cart', 'color' => 'bg-brand'],
                        ['label' => __('dashboard.metrics.gross_profit_month'), 'value' => $grossProfitMonth, 'icon' => 'line-chart', 'color' => 'bg-indigo-600'],
                        ['label' => __('dashboard.metrics.transactions_today'), 'value' => $transactionsToday, 'icon' => 'bolt', 'color' => 'bg-amber-500', 'is_qty' => true],
                        ['label' => __('dashboard.metrics.total_receivable'), 'value' => $receivableTotal, 'icon' => 'money', 'color' => 'bg-rose-500'],
                    ];
                } elseif ($isManager) {
                    $metrics = [
                        ['label' => __('dashboard.metrics.sales_today'), 'value' => $salesToday, 'icon' => 'sun-o', 'color' => 'bg-brand'],
                        ['label' => __('dashboard.metrics.receivable_outstanding'), 'value' => $receivableTotal, 'icon' => 'clock-o', 'color' => 'bg-amber-500'],
                        ['label' => __('dashboard.metrics.payable_outstanding'), 'value' => $payableTotal, 'icon' => 'credit-card', 'color' => 'bg-rose-500'],
                        ['label' => __('dashboard.metrics.transactions_today'), 'value' => $transactionsToday, 'icon' => 'list-ul', 'color' => 'bg-slate-700', 'is_qty' => true],
                    ];
                } elseif ($isHeadStore) {
                    $metrics = [
                        ['label' => __('dashboard.metrics.sales_today'), 'value' => $salesToday, 'icon' => 'shopping-bag', 'color' => 'bg-brand'],
                        ['label' => __('dashboard.metrics.transactions_today'), 'value' => $transactionsToday, 'icon' => 'hashtag', 'color' => 'bg-slate-700', 'is_qty' => true],
                    ];
                } elseif ($isCashier) {
                    $metrics = [
                        ['label' => __('dashboard.metrics.my_sales_today'), 'value' => $cashierSalesToday, 'icon' => 'user', 'color' => 'bg-brand'],
                        ['label' => __('dashboard.metrics.my_transactions_today'), 'value' => $cashierTransactionsToday, 'icon' => 'check-circle', 'color' => 'bg-emerald-600', 'is_qty' => true],
                    ];
                }
            @endphp

            @foreach ($metrics as $m)
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 truncate">{{ $m['label'] }}</p>
                            <h4 class="text-2xl xl:text-3xl font-black text-slate-900 tracking-tighter truncate">
                                {{ isset($m['is_qty']) ? number_format((float) $m['value'], 0, ',', '.') : 'Rp '.number_format((float) $m['value'], 0, ',', '.') }}
                            </h4>
                        </div>
                        <div class="w-12 h-12 shrink-0 rounded-2xl {{ $m['color'] }} text-white flex items-center justify-center text-xl shadow-lg shadow-{{ explode('-', $m['color'])[1] ?? 'slate' }}-500/30 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                            <i class="fa fa-{{ $m['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Quick Actions -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors"></div>
            
            <div class="relative space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-6 bg-brand rounded-full"></div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.quick_actions') }}</h3>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('sales.create') }}" class="group/btn flex items-center gap-3 bg-slate-900 px-6 py-3.5 rounded-2xl text-white font-bold shadow-lg shadow-slate-900/20 hover:-translate-y-1 transition-all">
                        <i class="fa fa-shopping-cart text-brand text-lg"></i>
                        <span>{{ __('dashboard.new_transaction') }}</span>
                    </a>
                    @if (! $isCashier)
                        <a href="{{ route('purchases.create') }}" class="flex items-center gap-3 bg-white border border-slate-200 px-6 py-3.5 rounded-2xl text-slate-700 font-bold hover:border-emerald-500/30 hover:bg-emerald-50 hover:text-emerald-700 transition-all hover:-translate-y-1 shadow-sm">
                            <i class="fa fa-shopping-basket text-emerald-500 text-lg"></i>
                            <span>{{ __('dashboard.make_purchase') }}</span>
                        </a>
                        <a href="{{ route('stock-adjustments.create') }}" class="flex items-center gap-3 bg-white border border-slate-200 px-6 py-3.5 rounded-2xl text-slate-700 font-bold hover:border-amber-500/30 hover:bg-amber-50 hover:text-amber-700 transition-all hover:-translate-y-1 shadow-sm">
                            <i class="fa fa-sliders text-amber-500 text-lg"></i>
                            <span>{{ __('dashboard.stock_adjustment') }}</span>
                        </a>
                    @endif
                    @if ($isManager || $isHeadStore || $isOwner)
                        <a href="{{ route('stock-transfers.create') }}" class="flex items-center gap-3 bg-white border border-slate-200 px-6 py-3.5 rounded-2xl text-slate-700 font-bold hover:border-indigo-500/30 hover:bg-indigo-50 hover:text-indigo-700 transition-all hover:-translate-y-1 shadow-sm">
                            <i class="fa fa-truck text-indigo-500 text-lg"></i>
                            <span>{{ __('dashboard.stock_transfer') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts and Lists -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Sales Chart -->
            @if ($isOwner || $isManager)
                <div class="lg:col-span-8 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="fa fa-line-chart"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.sales_trend') }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-brand"></div>
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('dashboard.revenue') }}</span>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>
            @endif

            <!-- Low Stock List -->
            <div class="{{ ($isOwner || $isManager) ? 'lg:col-span-4' : 'lg:col-span-6' }} bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="fa fa-archive"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.low_stock') }}</h3>
                </div>

                <div class="space-y-4">
                    @forelse ($lowStocks as $stock)
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:border-rose-100 hover:bg-rose-50 transition-all">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $stock->product?->name ?? '-' }}</p>
                                <p class="text-[10px] font-black text-slate-400 uppercase">{{ $stock->product?->unit?->name ?? 'pcs' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-rose-600">{{ number_format((float) $stock->quantity_on_hand, 0, ',', '.') }}</p>
                                <div class="w-12 h-1 bg-rose-200 rounded-full mt-1">
                                    <div class="h-full bg-rose-500 rounded-full" style="width: 25%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <div class="text-4xl mb-4">✅</div>
                            <p class="text-sm font-bold text-slate-400">{{ __('dashboard.all_stock_safe') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Best Sellers / Adjustments -->
            @if ($isOwner || $isManager)
                <div class="lg:col-span-6 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i class="fa fa-star"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.best_sellers') }}</h3>
                    </div>
                    
                    <div class="overflow-hidden rounded-3xl border border-slate-50">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.product') }}</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.qty') }}</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.turnover') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($topProducts as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-slate-800">{{ $item->product?->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-xs font-black text-slate-500">{{ number_format((float) $item->total_qty, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-black text-brand">Rp {{ number_format((float) $item->total_sales, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-bold">{{ __('dashboard.no_sales_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($isManager)
                <div class="lg:col-span-6 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa fa-refresh"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.operational_monitoring') }}</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-8 bg-blue-50 rounded-3xl border border-blue-100 flex flex-col items-center text-center">
                            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">{{ __('dashboard.pending_transfer') }}</p>
                            <h4 class="text-4xl font-black text-blue-700 tracking-tighter">{{ number_format((float) $pendingTransfers, 0, ',', '.') }}</h4>
                            <a href="{{ route('stock-transfers.index') }}" class="mt-4 text-xs font-bold text-blue-600 hover:underline">{{ __('dashboard.manage') }} &rarr;</a>
                        </div>
                        <div class="p-8 bg-amber-50 rounded-3xl border border-amber-100 flex flex-col items-center text-center text-amber-900">
                            <p class="text-[10px] font-black text-amber-400 uppercase tracking-widest mb-2">{{ __('dashboard.pending_adjustment') }}</p>
                            <h4 class="text-4xl font-black text-amber-700 tracking-tighter">{{ number_format((float) $pendingAdjustments, 0, ',', '.') }}</h4>
                            <a href="{{ route('stock-adjustments.index') }}" class="mt-4 text-xs font-bold text-amber-600 hover:underline">{{ __('dashboard.detail') }} &rarr;</a>
                        </div>
                    </div>
                </div>
            @endif

             @if ($isCashier)
                <div class="lg:col-span-12 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center">
                                <i class="fa fa-folder-open-o"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('dashboard.my_draft_transactions') }}</h3>
                        </div>
                        <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">{{ count($draftSales) }} {{ __('dashboard.total_drafts') }}</span>
                    </div>

                    <div class="overflow-hidden rounded-3xl border border-slate-50">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.reference') }}</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.date') }}</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.total') }}</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-black text-slate-400 uppercase">{{ __('dashboard.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($draftSales as $draft)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $draft->reference_no }}</td>
                                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">{{ $draft->created_at?->format('d/m/y H:i') }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-black text-slate-800">Rp {{ number_format((float) $draft->total, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('sales.resume', $draft) }}" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">{{ __('dashboard.resume') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-black italic">{{ __('dashboard.all_transactions_smooth') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            window.VizuraConfig = window.VizuraConfig || {};
            window.VizuraConfig.dashboard = {
                labels: @json($labels),
                series: @json($series)
            };
        </script>
        <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
    @endpush
@endsection
