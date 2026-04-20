@extends('layouts.app')

@section('title', __('manager.index_title'))
@section('page-title', __('manager.index_title'))

@section('content')
    <div class="space-y-8">
        <x-ui.card icon="sitemap" title="{{ __('manager.card_title') }}">
            <x-slot name="actions">
                <form method="GET" action="{{ route('manager-locations.index') }}" class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('manager.search_placeholder') }}" 
                               class="bg-slate-50 border-transparent rounded-xl px-5 py-2.5 text-sm focus:ring-2 focus:ring-brand/20 w-64 transition-all">
                    </div>
                    @if ($search !== '')
                        <a href="{{ route('manager-locations.index') }}" class="px-4 py-2 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                            {{ __('manager.reset_btn') }}
                        </a>
                    @endif
                </form>
            </x-slot>

            <div class="overflow-x-auto -mx-8">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-y border-slate-100">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('manager.manager_info') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('manager.location_access') }}</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ __('manager.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($managers as $manager)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-sm uppercase">
                                            {{ substr($manager->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $manager->name }}</p>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-1 italic">
                                                {{ $manager->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if ($manager->locations->isEmpty())
                                        <div class="flex items-center gap-2 text-rose-500 bg-rose-50 px-3 py-1 rounded-full w-fit">
                                            <i class="fa fa-exclamation-triangle text-[10px]"></i>
                                            <span class="text-[10px] font-black uppercase tracking-widest">{{ __('manager.no_access') }}</span>
                                        </div>
                                    @else
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($manager->locations as $loc)
                                                <span class="px-2.5 py-1 bg-brand/5 text-brand border border-brand/10 rounded-lg text-[10px] font-bold">
                                                    {{ $loc->name }}
                                                </span>
                                            @endforeach
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                {{ $manager->locations->count() }} {{ __('manager.locations_count') }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('manager-locations.edit', $manager) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-50 text-slate-500 rounded-xl hover:bg-brand hover:text-white font-black text-[10px] uppercase tracking-widest shadow-sm transition-all group-hover:shadow-md">
                                        <i class="fa fa-map-marker"></i>
                                        {{ __('manager.manage_btn') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-24 text-center">
                                    <p class="text-slate-400 font-bold mb-6">{{ __('manager.no_data') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $managers->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
