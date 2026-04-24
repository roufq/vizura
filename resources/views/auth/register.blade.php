<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>{{ config('app.name', 'Vizura') }} - {{ __('auth.register_title') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/app.js') }}" defer></script>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="{{ asset('assets/css/pages/login.css') }}" rel="stylesheet">
</head>

<body class="h-full bg-slate-50 flex items-center justify-center p-6 sm:p-12 overflow-hidden relative">
    
    <!-- Background Accents -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand/10 rounded-full blur-[100px] animate-pulse"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s"></div>

    <div class="w-full max-w-lg relative z-10">
        <!-- Logo Area -->
        <div class="flex flex-col items-center mb-10 group">
            <div class="w-20 h-20 bg-brand rounded-[2rem] flex items-center justify-center text-white shadow-2xl shadow-brand/40 group-hover:scale-110 transition-transform duration-500">
                <svg class="w-12 h-12" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 40L50 70L70 40" stroke="currentColor" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="50" cy="30" r="10" fill="currentColor"/>
                </svg>
            </div>
            <h2 class="mt-6 text-2xl font-black text-slate-900 tracking-tight">{{ config('app.name') }}</h2>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mt-2 italic">Modern POS Terminal</p>
        </div>

        <!-- Register Card -->
        <div class="glass-panel rounded-[3.5rem] shadow-2xl shadow-slate-200/50 p-10 sm:p-16 border border-white">
            <div class="mb-10">
                <h1 class="text-2xl font-black text-slate-900 tracking-tighter">{{ __('auth.register_title') }}</h1>
                <p class="text-sm font-bold text-slate-400 mt-2">{{ __('auth.register_subtitle') ?? 'Join our ecosystem today' }}</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('auth.full_name') }}</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa fa-user-o"></i></span>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="w-full bg-slate-100/50 border-transparent focus:bg-white focus:ring-4 focus:ring-brand/10 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                               placeholder="Full Name">
                    </div>
                    @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('auth.email_label') }}</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa fa-envelope-o"></i></span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                               class="w-full bg-slate-100/50 border-transparent focus:bg-white focus:ring-4 focus:ring-brand/10 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                               placeholder="name@company.com">
                    </div>
                    @error('email') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="password" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('auth.password_label') }}</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa fa-lock"></i></span>
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                   class="w-full bg-slate-100/50 border-transparent focus:bg-white focus:ring-4 focus:ring-brand/10 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                                   placeholder="••••••••">
                        </div>
                        @error('password') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('auth.confirm_password') }}</label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa fa-lock"></i></span>
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                   class="w-full bg-slate-100/50 border-transparent focus:bg-white focus:ring-4 focus:ring-brand/10 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 transition-all placeholder:text-slate-300"
                                   placeholder="••••••••">
                        </div>
                        @error('password_confirmation') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 px-1">
                    <input type="checkbox" id="accept" required class="w-4 h-4 rounded-md border-slate-200 text-brand focus:ring-brand">
                    <label for="accept" class="text-[10px] font-bold text-slate-500 cursor-pointer uppercase tracking-wider">{{ __('auth.agree_terms') }}</label>
                </div>

                <button type="submit" class="w-full py-5 bg-brand text-white rounded-3xl font-black text-sm uppercase tracking-[0.2em] shadow-2xl shadow-brand/40 hover:scale-[1.02] active:scale-95 transition-all">
                    {{ __('auth.register_btn') }}
                </button>
            </form>

            <div class="mt-12 text-center">
                 <p class="text-xs font-bold text-slate-400">{{ __('auth.has_account') }} <a href="{{ route('login') }}" class="text-brand font-black hover:underline underline-offset-4 decoration-2">{{ __('auth.login_btn') }}</a></p>
            </div>
        </div>

        <footer class="mt-12 text-center text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">
            &copy; {{ now()->year }} {{ config('app.name') }} Engine v2.0
        </footer>
    </div>

</body>
</html>
