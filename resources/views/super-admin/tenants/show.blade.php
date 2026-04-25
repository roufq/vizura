@extends('layouts.app')

@section('title', 'Tenant Detail: ' . $tenant->name)
@section('page-title', 'Owner Overview: ' . $tenant->name)

@section('content')
<div class="space-y-8">
    <!-- Header Infographic -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-8 py-10 bg-slate-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-brand flex items-center justify-center text-3xl font-black shadow-xl shadow-brand/40">
                    {{ substr($tenant->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black leading-tight">{{ $tenant->name }}</h2>
                    <p class="text-slate-400 font-medium italic text-sm">{{ $tenant->slug }}.vizura.app</p>
                </div>
            </div>
            <div class="flex gap-4">
                <div class="px-6 py-3 bg-white/10 rounded-2xl border border-white/10 backdrop-blur-md">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Current Plan</p>
                    <p class="text-sm font-black uppercase tracking-tight">{{ $tenant->plan }}</p>
                </div>
                <div class="px-6 py-3 bg-white/10 rounded-2xl border border-white/10 backdrop-blur-md">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</p>
                    <p class="text-sm font-black uppercase tracking-tight text-emerald-400">{{ $tenant->status }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h5 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4">Account Information</h5>
                <ul class="space-y-3">
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Registered On</span>
                        <span class="font-bold text-slate-900">{{ $tenant->created_at->format('M d, Y') }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Owner ID</span>
                        <span class="font-bold text-slate-900">#{{ $tenant->owner_id }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="md:border-l md:pl-8 border-slate-100">
                <h5 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4">Usage Stats</h5>
                <ul class="space-y-3">
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Active Users</span>
                        <span class="font-bold text-slate-900">{{ $tenant->users->count() }} / {{ $tenant->maxUsers() }}</span>
                    </li>
                    <li class="flex justify-between items-center text-sm">
                        <span class="text-slate-500">Active Outlets</span>
                        <span class="font-bold text-slate-900">{{ $tenant->locations->count() }} / {{ $tenant->maxOutlets() }}</span>
                    </li>
                </ul>
            </div>

            <div class="md:border-l md:pl-8 border-slate-100 flex flex-col justify-center">
                <a href="{{ route('super-admin.tenants.index') }}" class="w-full text-center py-3 bg-slate-50 text-slate-600 rounded-xl font-bold hover:bg-slate-100 transition-colors">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Users List -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-users text-slate-400"></i> Registered Users
                </h4>
            </div>
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/30">
                        <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-50">
                            <th class="px-8 py-4">Name / Email</th>
                            <th class="px-8 py-4">Role</th>
                            <th class="px-8 py-4">Location</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tenant->users as $user)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-[10px] font-black text-slate-600 uppercase border border-slate-200">
                                    {{ $user->roles->first()?->name ?? 'User' }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-[11px] font-medium text-slate-500">
                                {{ $user->activeLocation?->name ?? 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Locations List -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-map-marker text-slate-400"></i> Registered Locations
                </h4>
            </div>
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/30">
                        <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-50">
                            <th class="px-8 py-4">Code / Name</th>
                            <th class="px-8 py-4">Address</th>
                            <th class="px-8 py-4">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tenant->locations as $loc)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-slate-900">[{{ $loc->code }}] {{ $loc->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $loc->phone }}</p>
                            </td>
                            <td class="px-8 py-4 text-[11px] text-slate-500 truncate max-w-[150px]">
                                {{ $loc->address }}
                            </td>
                            <td class="px-8 py-4">
                                @if($loc->toko_pusat)
                                    <span class="text-[10px] font-black text-brand uppercase italic">Headquarters</span>
                                @else
                                    <span class="text-[10px] font-medium text-slate-400 uppercase">Branch</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
