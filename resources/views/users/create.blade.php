@extends('layouts.app')

@section('title', __('user.create_title'))
@section('page-title', __('user.add_user'))

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-6 px-4">
            <i class="fa fa-arrow-left"></i> {{ __('user.back_to_list') }}
        </a>

        <x-ui.card icon="user-plus" title="{{ __('user.access_info_card') }}">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('user.full_name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('user.name_placeholder') }}" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('app.email_address') }} <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('user.email_placeholder') }}" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('email') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('user.password_label') }} <span class="text-rose-500">*</span></label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('password') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="role" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('user.role_label') }} <span class="text-rose-500">*</span></label>
                        <select id="role" name="role" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>{{ __('user.select_role') }}</option>
                            @foreach ($roles as $roleOption)
                                <option value="{{ $roleOption }}" {{ old('role') === $roleOption ? 'selected' : '' }}>
                                    {{ $roleOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-6 pt-6 border-t border-slate-50">
                    <div class="space-y-2">
                        <label for="active_location_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('user.default_location') }} <span class="text-rose-500">*</span></label>
                        <select id="active_location_id" name="active_location_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all" required>
                             <option value="" disabled {{ old('active_location_id') ? '' : 'selected' }}>{{ __('user.select_main_loc') }}</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" {{ (int) old('active_location_id') === $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('active_location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('user.additional_access') }}</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($locations as $location)
                                <label class="relative group cursor-pointer">
                                    <input type="checkbox" name="location_ids[]" value="{{ $location->id }}" class="peer sr-only" {{ in_array($location->id, old('location_ids', []), true) ? 'checked' : '' }}>
                                    <div class="p-4 bg-white border border-slate-100 rounded-2xl shadow-sm transition-all group-hover:border-brand/20 peer-checked:border-brand peer-checked:bg-brand/5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 peer-checked:text-brand">
                                                <i class="fa fa-building"></i>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700">{{ $location->name }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('location_ids') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-50 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        {{ __('user.cancel') }}
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        {{ __('user.save_user') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
