@extends('layouts.app')

@section('title', __('unit.create_title'))
@section('page-title', __('unit.create_title'))

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('units.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-6 px-4">
            <i class="fa fa-arrow-left"></i> {{ __('app.back_to_list') }}
        </a>

        <x-ui.card icon="balance-scale" title="{{ __('unit.unit_info') }}">
            <form method="POST" action="{{ route('units.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('unit.name_label') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('unit.name_placeholder') }}" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="abbreviation" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('unit.abbr_label') }}</label>
                        <input type="text" id="abbreviation" name="abbreviation" value="{{ old('abbreviation') }}" placeholder="{{ __('unit.abbr_placeholder') }}"
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('abbreviation') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="is_active" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('unit.is_active_label') }}</label>
                        <div class="flex items-center gap-4 mt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                                <span class="ml-3 text-xs font-bold text-slate-600 uppercase tracking-widest">{{ __('unit.active') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-50 flex items-center justify-end gap-3">
                    <a href="{{ route('units.index') }}" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        {{ __('unit.cancel') }}
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        {{ __('unit.save_unit') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
