@extends('layouts.app')

@section('title', 'Upgrade Your Plan')
@section('page-title', 'Subscription Limit Reached')

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-2xl overflow-hidden text-center p-12 space-y-8 relative">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
        
        <div class="w-24 h-24 bg-brand/10 text-brand rounded-3xl flex items-center justify-center text-4xl mx-auto shadow-inner">
            <i class="fa fa-rocket"></i>
        </div>

        <div class="space-y-3">
            <h2 class="text-3xl font-black text-slate-900 leading-tight">Yukk, Tingkatkan Potensi Bisnis Anda!</h2>
            <p class="text-slate-500 font-medium max-w-md mx-auto">
                {{ \App\Models\SaasConfig::get('upgrade_message') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ \App\Models\SaasConfig::get('upgrade_pstore_url') }}" target="_blank" 
               class="flex flex-col items-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-brand/30 hover:bg-white transition-all group text-center">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-slate-400 group-hover:text-brand shadow-sm mb-4 mx-auto">
                    <i class="fa fa-shopping-bag"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm">P-Store</h4>
                <p class="text-[10px] text-slate-400 font-medium">Beli lisensi baru.</p>
            </a>

            @php
                $wa = \App\Models\SaasConfig::get('upgrade_wa_number');
                $message = urlencode("Halo Super Admin Vizura, saya ingin tanya-tanya tentang upgrade paket untuk bisnis saya: " . (auth()->user()->tenant->name ?? 'User Vizura'));
                $waUrl = "https://wa.me/{$wa}?text={$message}";
            @endphp
            <a href="{{ $waUrl }}" target="_blank" 
               class="flex flex-col items-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all group text-center">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-slate-400 group-hover:text-emerald-500 shadow-sm mb-4 mx-auto">
                    <i class="fa fa-whatsapp"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm">WhatsApp</h4>
                <p class="text-[10px] text-slate-400 font-medium">Chat bantuan CS.</p>
            </a>

            <a href="mailto:{{ \App\Models\SaasConfig::get('upgrade_email') }}?subject=Upgrade%20Plan%20Request&body=Halo%20Admin%20Vizura," 
               class="flex flex-col items-center p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all group text-center">
                <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-slate-400 group-hover:text-indigo-500 shadow-sm mb-4 mx-auto">
                    <i class="fa fa-envelope"></i>
                </div>
                <h4 class="font-bold text-slate-900 text-sm">Email</h4>
                <p class="text-[10px] text-slate-400 font-medium">Support via Gmail.</p>
            </a>
        </div>

        <div class="pt-8 border-t border-slate-50">
            <a href="{{ url()->previous() }}" class="text-sm font-bold text-slate-400 hover:text-slate-900 transition-colors">
                <i class="fa fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection
