@extends('layouts.app')

@section('title', 'Tenant Detail: ' . $tenant->name)
@section('page-title', 'Owner Overview: ' . $tenant->name)

@section('content')
<div class="space-y-8">
    <!-- Header Infographic -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-8 py-10 bg-slate-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
            <!-- Decoration -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-brand/10 rounded-full blur-3xl -mr-32 -mt-32"></div>
            
            <div class="flex items-center gap-6 relative z-10">
                <div class="w-20 h-20 rounded-3xl bg-brand flex items-center justify-center text-4xl font-black shadow-2xl shadow-brand/40">
                    {{ substr($tenant->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-3xl font-black leading-tight">{{ $tenant->name }}</h2>
                    <p class="text-slate-400 font-medium italic text-base">{{ $tenant->slug }}.vizura.app</p>
                </div>
            </div>
            <div class="flex gap-4 relative z-10">
                <form action="{{ route('super-admin.tenants.impersonate', $tenant) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-8 py-4 bg-emerald-500 text-white rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-xl shadow-emerald-500/20 hover:scale-105 transition-all">
                        <i class="fa fa-user-secret mr-2"></i> Login as Owner
                    </button>
                </form>
                <a href="{{ route('super-admin.tenants.index') }}" class="px-8 py-4 bg-white/10 text-white rounded-2xl font-black uppercase tracking-widest text-[11px] border border-white/10 backdrop-blur-md hover:bg-white/20 transition-all">
                    Back to List
                </a>
            </div>
        </div>
        
        <div class="p-8 flex flex-col lg:flex-row gap-8 w-full">
            <!-- Quota Locations -->
            <div class="flex-1 bg-slate-50 p-6 rounded-[2rem] border border-slate-100 relative group flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Outlet Quota</h5>
                    <div class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-xs">
                        <i class="fa fa-map-marker"></i>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-black text-slate-900 leading-none mb-2">
                            {{ $tenant->locations->count() }} <span class="text-sm text-slate-400 font-bold">/ {{ $tenant->maxOutlets() }}</span>
                        </p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase italic">Base: {{ $tenant->plan === 'starter' ? 1 : ($tenant->plan === 'business' ? 5 : 'Inf') }} + Extra: {{ $tenant->extra_locations }}</p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <form action="{{ route('super-admin.tenants.update-quota', $tenant) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="type" value="locations">
                            <input type="hidden" name="action" value="inc">
                            <button type="submit" class="w-8 h-8 bg-white text-slate-900 border border-slate-200 rounded-lg shadow-sm hover:bg-slate-900 hover:text-white transition-all font-black">+</button>
                        </form>
                        <form action="{{ route('super-admin.tenants.update-quota', $tenant) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="type" value="locations">
                            <input type="hidden" name="action" value="dec">
                            <button type="submit" class="w-8 h-8 bg-white text-slate-900 border border-slate-200 rounded-lg shadow-sm hover:bg-rose-500 hover:text-white transition-all font-black">-</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quota Users -->
            <div class="flex-1 bg-slate-50 p-6 rounded-[2rem] border border-slate-100 relative group flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-[11px] font-black text-slate-400 uppercase tracking-widest">User Quota</h5>
                    <div class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-xs">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-black text-slate-900 leading-none mb-2">
                            {{ $tenant->users->count() }} <span class="text-sm text-slate-400 font-bold">/ {{ $tenant->maxUsers() }}</span>
                        </p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase italic">Base: {{ $tenant->plan === 'starter' ? 3 : ($tenant->plan === 'business' ? 15 : 'Inf') }} + Extra: {{ $tenant->extra_users }}</p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <form action="{{ route('super-admin.tenants.update-quota', $tenant) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="type" value="users">
                            <input type="hidden" name="action" value="inc">
                            <button type="submit" class="w-8 h-8 bg-white text-slate-900 border border-slate-200 rounded-lg shadow-sm hover:bg-slate-900 hover:text-white transition-all font-black">+</button>
                        </form>
                        <form action="{{ route('super-admin.tenants.update-quota', $tenant) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="type" value="users">
                            <input type="hidden" name="action" value="dec">
                            <button type="submit" class="w-8 h-8 bg-white text-slate-900 border border-slate-200 rounded-lg shadow-sm hover:bg-rose-500 hover:text-white transition-all font-black">-</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Data Management -->
            <div class="flex-1 bg-slate-900 p-6 rounded-[2rem] shadow-xl shadow-slate-900/20 text-white flex flex-col justify-between">
                <h5 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-4">Master Data Tools</h5>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('super-admin.tenants.export', $tenant) }}" class="flex items-center justify-between px-5 py-3 bg-white/10 rounded-xl hover:bg-white/20 transition-all group">
                        <span class="text-xs font-bold uppercase tracking-widest">Backup & Export</span>
                        <i class="fa fa-upload text-brand group-hover:-translate-y-0.5 transition-transform"></i>
                    </a>
                    
                    <form action="{{ route('super-admin.tenants.import', $tenant) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                        @csrf
                        <div class="relative">
                            <input type="file" name="file" class="hidden" id="import_file" onchange="this.form.submit()">
                            <label for="import_file" class="flex items-center justify-between px-5 py-3 bg-indigo-600 rounded-xl cursor-pointer hover:bg-indigo-700 transition-all group">
                                <span class="text-xs font-bold uppercase tracking-widest">Restore / Import</span>
                                <i class="fa fa-download text-white group-hover:translate-y-0.5 transition-transform"></i>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Users List -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-users text-slate-400"></i> Registered Users
                </h4>
                <a href="{{ route('super-admin.tenants.add-user', $tenant) }}" class="text-[10px] font-black text-brand uppercase tracking-tighter hover:underline">One-Click Add</a>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/30">
                        <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-50">
                            <th class="px-8 py-4">Name / Email</th>
                            <th class="px-8 py-4 text-right">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tenant->users as $user)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-[10px] font-black text-slate-600 uppercase border border-slate-200">
                                    {{ $user->roles->first()?->name ?? 'User' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Locations List -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h4 class="font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa fa-map-marker text-slate-400"></i> Registered Locations
                </h4>
                <a href="{{ route('super-admin.tenants.add-location', $tenant) }}" class="text-[10px] font-black text-brand uppercase tracking-tighter hover:underline">One-Click Add</a>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/30">
                        <tr class="text-[10px] uppercase font-black text-slate-400 border-b border-slate-50">
                            <th class="px-8 py-4">Code / Name</th>
                            <th class="px-8 py-4 text-right">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tenant->locations as $loc)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-8 py-4">
                                <p class="text-sm font-bold text-slate-900">[{{ $loc->code }}] {{ $loc->name }}</p>
                                <p class="text-[11px] text-slate-500">{{ $loc->phone }}</p>
                            </td>
                            <td class="px-8 py-4 text-right">
                                @if($loc->toko_pusat)
                                    <span class="text-[10px] font-black text-brand uppercase italic">Headquarters</span>
                                @else
                                    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter">Branch</span>
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
