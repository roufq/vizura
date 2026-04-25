@extends('layouts.app')

@section('title', 'Register New Owner')
@section('page-title', 'Add New SaaS Client')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('super-admin.tenants.store') }}" method="POST">
        @csrf
        <div class="space-y-8">
            <!-- Section 1: Business Profile -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <h4 class="font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa fa-briefcase text-slate-400"></i> Business Information
                    </h4>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Business Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Toko Berkah Jaya" 
                               class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                        @error('name') <p class="text-rose-500 text-[11px] font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Business Plan</label>
                        <select name="plan" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                            <option value="starter" {{ old('plan') == 'starter' ? 'selected' : '' }}>SOLO STORE (Starter)</option>
                            <option value="business" {{ old('plan') == 'business' ? 'selected' : '' }}>BUSINESS</option>
                            <option value="enterprise" {{ old('plan') == 'enterprise' ? 'selected' : '' }}>ENTERPRISE</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 2: Owner Login Detail -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <h4 class="font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa fa-user-circle text-slate-400"></i> Owner Account Credentials
                    </h4>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Full Name</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="Owner's Name" 
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Email Address</label>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}" placeholder="owner@email.com" 
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                            @error('owner_email') <p class="text-rose-500 text-[11px] font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Password</label>
                            <input type="password" name="password" 
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-black uppercase text-slate-400 tracking-widest">Confirm Password</label>
                            <input type="password" name="password_confirmation" 
                                   class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-brand/10 focus:border-brand outline-none transition-all font-bold text-slate-700" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('super-admin.tenants.index') }}" class="px-8 py-4 text-slate-500 font-bold hover:text-slate-900 transition-all">
                    Cancel & Back
                </a>
                <button type="submit" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black shadow-xl shadow-slate-900/20 hover:scale-105 active:scale-95 transition-all">
                    <i class="fa fa-check-circle mr-2"></i> Register Owner & Create Tenant
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
