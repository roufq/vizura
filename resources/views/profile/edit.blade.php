@extends('layouts.app')

@section('title', __('profile.title'))
@section('page-title', __('profile.page_title'))

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Profile Info -->
        <div class="lg:col-span-4 space-y-6 text-center">
            <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-xl shadow-slate-200/50">
                <div class="w-24 h-24 bg-brand/10 border border-brand/20 rounded-full flex items-center justify-center text-brand mx-auto mb-6">
                    <i class="fa fa-user-circle text-5xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900">{{ $user->name }}</h3>
                <p class="text-xs font-bold text-slate-400 mt-1 italic">{{ $user->email }}</p>
                
                <div class="mt-8 pt-8 border-t border-slate-50 space-y-3">
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <span>Role</span>
                        <span class="text-brand">{{ $user->roles->pluck('name')->first() ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <span>{{ __('profile.active_location') }}</span>
                        <span class="text-slate-700">{{ $user->activeLocation?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 p-8 rounded-[2.5rem] border border-indigo-100 text-left">
                <p class="text-[10px] font-black text-indigo-900 uppercase tracking-widest mb-2"><i class="fa fa-info-circle mr-2"></i>{{ __('profile.security_note_title') }}</p>
                <p class="text-[10px] text-indigo-600/70 font-bold leading-relaxed">{{ __('profile.security_note_desc') }}</p>
            </div>
        </div>

        <!-- Right: Forms -->
        <div class="lg:col-span-8 space-y-8">
            <x-ui.card icon="id-card" title="{{ __('profile.personal_info') }}">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                             <label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('profile.full_name') }}</label>
                             <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                                    class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all" required>
                             @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                             <label for="email" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('profile.email_address') }}</label>
                             <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                                    class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-brand/10 transition-all" required>
                             @error('email') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-brand/20 hover:scale-105 transition-all">{{ __('profile.save_profile') }}</button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card icon="shield" title="{{ __('profile.security_danger_zone') }}">
                <div class="flex flex-col md:flex-row gap-8 items-start">
                    <div class="flex-1 space-y-2">
                         <h4 class="text-xs font-black text-rose-500 uppercase tracking-widest">{{ __('profile.danger_zone') }}</h4>
                         <p class="text-[10px] text-slate-400 font-bold leading-relaxed">{{ __('profile.danger_zone_desc') }}</p>
                    </div>
                    
                    <form method="POST" action="{{ route('profile.destroy') }}" class="flex-1 space-y-4">
                        @csrf
                        @method('DELETE')

                        <div class="space-y-2">
                            <label for="del_password" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('profile.type_password') }}</label>
                            <input type="password" id="del_password" name="password" 
                                   class="w-full bg-rose-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-rose-600 focus:ring-4 focus:ring-rose-500/10 transition-all" required>
                            @if ($errors->userDeletion->has('password'))
                                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>

                        <button type="submit" onclick="return confirm('{{ __('profile.delete_confirm') }}')" 
                                class="w-full py-4 bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-600 transition-all shadow-lg shadow-rose-100">
                            {{ __('profile.delete_account_btn') }}
                        </button>
                    </form>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection
