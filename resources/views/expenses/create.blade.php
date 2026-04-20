@extends('layouts.app')

@section('title', __('expense.create_title'))
@section('page-title', __('expense.page_title'))

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-6 px-4">
            <i class="fa fa-arrow-left"></i> {{ __('expense.back_to_list') }}
        </a>

        <x-ui.card icon="money" title="{{ __('expense.card_title') }}">
            <form method="POST" action="{{ route('expenses.store') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="reference_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.reference_no') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="reference_no" name="reference_no" value="{{ old('reference_no', $referenceNo) }}" required readonly
                               class="w-full bg-slate-100 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-500 cursor-not-allowed">
                        @error('reference_no') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="expense_date" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.date') }} <span class="text-rose-500">*</span></label>
                        <input type="date" id="expense_date" name="expense_date" value="{{ old('expense_date', now()->toDateString()) }}" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('expense_date') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="account_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.category') }} <span class="text-rose-500">*</span></label>
                        <select id="account_id" name="account_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all" required>
                            <option value="" disabled {{ old('account_id') ? '' : 'selected' }}>{{ __('expense.select_category') }}</option>
                            @foreach ($expenseAccounts as $account)
                                <option value="{{ $account->id }}" {{ (int) old('account_id') === $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="payment_account_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.payment_account') }} <span class="text-rose-500">*</span></label>
                        <select id="payment_account_id" name="payment_account_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all" required>
                            <option value="" disabled {{ old('payment_account_id') ? '' : 'selected' }}>{{ __('expense.select_payment_account') }}</option>
                            @foreach ($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ (int) old('payment_account_id') === $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-slate-50">
                    <label for="amount" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.amount') }} <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <div class="absolute left-6 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</div>
                        <input type="number" min="0" step="0.01" id="amount" name="amount" value="{{ old('amount') }}" placeholder="0" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl pl-14 pr-6 py-4 text-xl font-black text-rose-500 focus:ring-4 focus:ring-rose-500/10 transition-all tracking-tight">
                    </div>
                    @error('amount') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('expense.description') }}</label>
                    <textarea id="description" name="description" rows="3" placeholder="{{ __('expense.description_placeholder') }}"
                              class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">{{ old('description') }}</textarea>
                    @error('description') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                </div>

                <div class="pt-8 border-t border-slate-50 flex items-center justify-end gap-3">
                    <a href="{{ route('expenses.index') }}" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        {{ __('expense.cancel') }}
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        {{ __('expense.save') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
