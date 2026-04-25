@extends('layouts.app')

@section('title', 'Manage Tenants - Super Admin')
@section('page-title', 'Multi-Tenant Overview')

@section('content')
<div class="space-y-8">
    <!-- Header Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Owners</p>
            <h3 class="text-3xl font-black text-slate-900">{{ $tenants->count() }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Active Subscriptions</p>
            <h3 class="text-3xl font-black text-emerald-600">{{ $tenants->where('status', 'active')->count() }}</h3>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total System Outlets</p>
            <h3 class="text-3xl font-black text-brand">{{ $tenants->sum('locations_count') }}</h3>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h4 class="font-bold text-slate-900">Registered Owners & Companies</h4>
            <a href="{{ route('super-admin.tenants.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-sm font-bold shadow-lg shadow-brand/20 hover:scale-105 transition-transform inline-block">
                <i class="fa fa-plus mr-2"></i> Register New Owner
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-400 border-b border-slate-100">
                        <th class="px-8 py-5 font-black">Company / Owner</th>
                        <th class="px-8 py-5 font-black">Plan</th>
                        <th class="px-8 py-5 font-black">Stats</th>
                        <th class="px-8 py-5 font-black">Status</th>
                        <th class="px-8 py-5 font-black text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($tenants as $tenant)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-colors">
                                    <i class="fa fa-building text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $tenant->name }}</p>
                                    <p class="text-[11px] text-slate-500 font-medium italic">{{ $tenant->slug }}.vizura.app</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            @php
                                $planClass = match($tenant->plan) {
                                    'enterprise' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                    'business' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    default => 'bg-slate-50 text-slate-600 border-slate-100',
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border {{ $planClass }}">
                                {{ $tenant->plan }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex gap-6">
                                <div class="text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Outlets</p>
                                    <p class="text-sm font-black {{ $tenant->locations_count >= $tenant->maxOutlets() ? 'text-rose-500' : 'text-slate-700' }}">
                                        {{ $tenant->locations_count }} / {{ $tenant->maxOutlets() > 1000 ? '∞' : $tenant->maxOutlets() }}
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Users</p>
                                    <p class="text-sm font-black {{ $tenant->users_count >= $tenant->maxUsers() ? 'text-rose-500' : 'text-slate-700' }}">
                                        {{ $tenant->users_count }} / {{ $tenant->maxUsers() > 1000 ? '∞' : $tenant->maxUsers() }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <form action="{{ route('super-admin.tenants.status', $tenant) }}" method="POST" class="space-y-1">
                                @csrf @method('PATCH')
                                <div class="flex flex-col gap-1">
                                    <select onchange="this.form.submit()" name="plan" class="text-[10px] font-black uppercase bg-slate-900 text-white rounded-lg px-2 py-1 outline-none border-none">
                                        <option value="starter" {{ $tenant->plan == 'starter' ? 'selected' : '' }}>Starter</option>
                                        <option value="business" {{ $tenant->plan == 'business' ? 'selected' : '' }}>Business</option>
                                        <option value="enterprise" {{ $tenant->plan == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                                    <select onchange="this.form.submit()" name="status" class="text-[10px] font-bold bg-white border border-slate-200 rounded-lg px-2 py-1 outline-none">
                                        <option value="active" {{ $tenant->status == 'active' ? 'selected' : '' }}>ACTIVE</option>
                                        <option value="suspended" {{ $tenant->status == 'suspended' ? 'selected' : '' }}>SUSPENDED</option>
                                    </select>
                                </div>
                            </form>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="#" class="p-2 text-slate-400 hover:text-brand transition-colors" title="Impersonate (Login as Owner)">
                                    <i class="fa fa-user-secret"></i>
                                </a>
                                <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="p-2 text-slate-400 hover:text-slate-900 transition-colors">
                                    <i class="fa fa-chevron-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
