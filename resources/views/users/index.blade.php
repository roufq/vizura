@extends('layouts.app')

@section('title', __('user.index_title'))
@section('page-title', __('user.page_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="users" title="{{ __('user.card_title') }}">
            <x-slot name="actions">
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('user.search_placeholder') }}" 
                               class="bg-slate-50 border-transparent rounded-xl px-5 py-2.5 text-sm focus:ring-2 focus:ring-brand/20 w-48 transition-all">
                    </div>
                    <select name="role" class="bg-slate-50 border-transparent rounded-xl px-5 py-2.5 text-sm focus:ring-2 focus:ring-brand/20 transition-all font-bold text-slate-600">
                        <option value="">{{ __('user.all_roles') }}</option>
                        @foreach (['Owner', 'Manager', 'HeadStore', 'Cashier'] as $roleOption)
                            <option value="{{ $roleOption }}" {{ $role === $roleOption ? 'selected' : '' }}>{{ $roleOption }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="p-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition-all">
                        <i class="fa fa-filter"></i>
                    </button>
                    <a href="{{ route('users.create') }}" class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-bold shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        <i class="fa fa-user-plus mr-2"></i>{{ __('user.add_user') }}
                    </a>
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('user.name_email') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('user.role') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('user.active_location') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ __('user.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 leading-tight">
                                                {{ $u->name }}
                                                @if ((int) $u->id === (int) Auth::id())
                                                    <span class="ml-2 text-[8px] font-black bg-blue-50 text-blue-500 px-1.5 py-0.5 rounded uppercase tracking-widest">{{ __('user.you') }}</span>
                                                @endif
                                            </p>
                                            <p class="text-[10px] font-medium text-slate-400 mt-1">{{ $u->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @php
                                        $roleName = $u->roles->first()?->name ?? 'User';
                                        $roleColor = match($roleName) {
                                            'Owner' => 'bg-rose-50 text-rose-600',
                                            'Manager' => 'bg-indigo-50 text-indigo-600',
                                            'Cashier' => 'bg-emerald-50 text-emerald-600',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    @endphp
                                    <span class="inline-block px-3 py-1 {{ $roleColor }} rounded-full text-[9px] font-black uppercase">
                                        {{ $roleName }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                        <i class="fa fa-map-marker opacity-40"></i>
                                        <span>{{ $u->activeLocation?->name ?? __('user.no_location') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('users.edit', $u) }}" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand/10 hover:text-brand transition-all">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        @if ((int) $u->id !== (int) Auth::id())
                                            <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('{{ __('user.delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-rose-50 hover:text-rose-600 transition-all">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold">{{ __('user.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-slate-50 pt-8">
                {{ $users->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
