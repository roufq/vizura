@extends('layouts.app')

@section('title', 'SaaS Global Settings')
@section('page-title', 'Configure Upgrade Information')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h4 class="font-bold text-slate-900 flex items-center gap-2">
                <i class="fa fa-cogs text-slate-400"></i> Global SaaS Configuration
            </h4>
        </div>
        <form action="{{ route('super-admin.settings.update') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <div class="space-y-2">
                <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">P-Store Shop URL</label>
                <input type="url" name="upgrade_pstore_url" value="{{ $configs['upgrade_pstore_url'] }}" 
                       class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700">
                <p class="text-[10px] text-slate-400 font-medium">Link ke produk Vizura POS Anda di P-Store.</p>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">WhatsApp Number (International Format)</label>
                <input type="text" name="upgrade_wa_number" value="{{ $configs['upgrade_wa_number'] }}" placeholder="e.g. 628123456789"
                       class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700">
                <p class="text-[10px] text-slate-400 font-medium">Nomor WhatsApp untuk bantuan upgrade (tanpa tanda +).</p>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Support Email (Gmail)</label>
                <input type="email" name="upgrade_email" value="{{ $configs['upgrade_email'] }}" placeholder="your-email@gmail.com"
                       class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700">
                <p class="text-[10px] text-slate-400 font-medium">Alamat email tujuan untuk bantuan teknis / upgrade.</p>
            </div>

            <div class="space-y-2">
                <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Upgrade Message</label>
                <textarea name="upgrade_message" rows="4" 
                          class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700">{{ $configs['upgrade_message'] }}</textarea>
                <p class="text-[10px] text-slate-400 font-medium">Pesan yang muncul saat Owner mencapai limit data.</p>
            </div>

            <div class="pt-6 border-t border-slate-50 flex justify-end">
                <button type="submit" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all">
                    <i class="fa fa-save mr-2"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
