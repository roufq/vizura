@extends('layouts.app')

@section('title', __('location.select_location_title'))
@section('page-title', __('location.choose_location'))

@section('content')
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="max-w-xl w-full">
            <div class="bg-white p-12 rounded-[3.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                <!-- Background Decoration -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-brand/5 rounded-full blur-3xl group-hover:bg-brand/10 transition-all duration-700"></div>
                <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl group-hover:bg-indigo-500/10 transition-all duration-700"></div>
                
                <div class="relative space-y-10">
                    <div class="text-center space-y-3">
                         <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 mx-auto mb-6">
                            <i class="fa fa-map-marker text-2xl"></i>
                         </div>
                        <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ __('location.location_inventory') }}</h3>
                        <p class="text-sm text-slate-500 font-bold max-w-xs mx-auto leading-relaxed">{{ __('location.select_location_desc') }}</p>
                    </div>

                    @if ($current)
                        <div class="p-6 bg-brand/5 rounded-3xl border border-brand/10 flex items-center gap-5 transition-transform hover:scale-[1.02]">
                            <div class="w-12 h-12 rounded-2xl bg-white text-brand flex items-center justify-center shadow-sm">
                                <i class="fa fa-dot-circle-o text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-brand mb-0.5 italic">{{ __('location.currently_used') }}</p>
                                <p class="text-lg font-black text-slate-800 tracking-tight">{{ $locations->firstWhere('id', $current)?->name ?? '-' }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($locations->isEmpty())
                        <div class="py-6 text-center space-y-6">
                            <div class="text-5xl animate-bounce">🏜️</div>
                            <p class="text-slate-400 font-bold italic">{{ __('location.no_data') }}</p>
                            <a href="{{ route('locations.index') }}" class="inline-block bg-slate-900 text-white px-10 py-4 rounded-3xl font-black uppercase text-[10px] tracking-widest shadow-xl shadow-slate-200">{{ __('nav.location') }}</a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('locations.active.update') }}" class="space-y-8">
                            @csrf

                            <div class="space-y-3">
                                <label for="location_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-2 italic">{{ __('location.card_title') }}</label>
                                <div class="relative group">
                                    <select id="location_id" name="location_id" class="appearance-none block w-full bg-slate-50 border-transparent rounded-3xl px-8 py-5 text-sm font-black text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all cursor-pointer" required>
                                        <option value="" disabled {{ old('location_id', $current) ? '' : 'selected' }}>
                                            {{ __('location.select_loc') }}
                                        </option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" {{ (int) old('location_id', $current) === $location->id ? 'selected' : '' }}>
                                                {{ $location->name }} — ({{ $location->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-8 top-1/2 -translate-y-1/2 pointer-events-none text-slate-300 group-hover:text-brand transition-colors">
                                        <i class="fa fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                @error('location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-2">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="w-full py-5 bg-brand text-white rounded-3xl font-black text-sm uppercase tracking-widest shadow-2xl shadow-brand/40 hover:bg-brand-dark hover:scale-[1.02] active:scale-95 transition-all">
                                {{ __('location.enter_location') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
