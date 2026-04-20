@extends('layouts.app')

@section('title', __('expense.index_title'))
@section('page-title', __('app.menu.expenses'))

@section('content')
    <div class="space-y-8">
        @php
            $pageTotalAmount = $expenses->getCollection()->sum('amount');
        @endphp

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card icon="calendar-check-o" title="{{ __('expense.summary_today') }}" class="from-rose-500/5 to-white bg-gradient-to-br">
                <h2 class="text-2xl font-black tracking-tight text-rose-600 mt-2">Rp {{ number_format((float) $todayTotal, 0, ',', '.') }}</h2>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('expense.summary_today_desc') }}</p>
            </x-ui.card>

            <x-ui.card icon="bar-chart" title="{{ __('expense.summary_month') }}" class="from-indigo-500/5 to-white bg-gradient-to-br">
                <h2 class="text-2xl font-black tracking-tight text-indigo-600 mt-2">Rp {{ number_format((float) $monthTotal, 0, ',', '.') }}</h2>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('expense.summary_month_desc', ['month' => now()->translatedFormat('F')]) }}</p>
            </x-ui.card>

            <x-ui.card icon="money" title="{{ __('expense.summary_page') }}">
                <h2 class="text-2xl font-black tracking-tight text-slate-800 mt-2">Rp {{ number_format((float) $pageTotalAmount, 0, ',', '.') }}</h2>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">{{ __('expense.summary_page_desc', ['count' => $expenses->count()]) }}</p>
            </x-ui.card>
        </div>

        <x-ui.card icon="list-alt" title="{{ __('expense.log_card_title') }}">
            <x-slot name="actions">
                <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center justify-end gap-3">
                    <div class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('expense.search_placeholder') }}" 
                               class="bg-slate-50 border-slate-100 rounded-xl px-4 py-2 text-[10px] font-black w-32 focus:ring-2 focus:ring-brand/20 transition-all uppercase">
                        
                        <div class="flex items-center gap-2 bg-slate-50 rounded-xl px-3 py-1.5 border border-slate-100">
                            <span class="text-[9px] font-black text-slate-400 uppercase whitespace-nowrap">{{ __('adjustment.period') }}:</span>
                            <div class="flex items-center gap-2">
                                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="bg-transparent border-none p-0 text-[10px] font-black text-slate-600 focus:ring-0 w-24 h-4 uppercase">
                                <span class="text-slate-300">-</span>
                                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="bg-transparent border-none p-0 text-[10px] font-black text-slate-600 focus:ring-0 w-24 h-4 uppercase">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($canViewAll)
                            <div class="min-w-[160px]">
                                <select name="location_id" class="no-select2 bg-slate-50 border-slate-100 rounded-xl px-4 py-2 text-[10px] font-black text-slate-600">
                                    <option value="">{{ __('expense.all_locations') }}</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" {{ (int) ($filters['location_id'] ?? 0) === $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all flex-shrink-0">
                            <i class="fa fa-filter text-xs"></i>
                        </button>
                        
                        <a href="{{ route('expenses.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all flex-shrink-0">
                            <i class="fa fa-plus-circle mr-2 text-[10px]"></i>{{ __('expense.page_title') }}
                        </a>
                    </div>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('expense.table_time_ref') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('expense.table_category_account') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('app.amount') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('expense.table_staff_location') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($expenses as $e)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <p class="text-[10px] font-black text-slate-400 uppercase mb-1">{{ optional($e->expense_date)->format('d M Y') }}</p>
                                    <p class="text-sm font-bold text-slate-900">{{ $e->reference_no }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-slate-700">{{ $e->account?->name ?? 'Unknown Account' }}</p>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">via: {{ $e->paymentAccount?->name ?? 'None' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-sm text-rose-500">
                                    Rp {{ number_format((float) $e->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-slate-200"></div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $e->creator?->name ?? '-' }}</p>
                                            <p class="text-[9px] font-black text-indigo-500 uppercase tracking-widest italic mt-0.5">{{ $e->location?->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold italic">{{ __('expense.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $expenses->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
