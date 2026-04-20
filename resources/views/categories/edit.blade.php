@extends('layouts.app')

@section('title', __('category.edit_title') . ': ' . $category->name)
@section('page-title', __('category.page_title'))

@section('content')
    <div class="max-w-2xl">
        <x-ui.card icon="pencil" title="{{ __('category.edit_title') }}">
            <x-slot name="actions">
                <a href="{{ route('categories.index') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">{{ __('category.batal') }}</a>
            </x-slot>

            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="name" class="text-xs font-black uppercase tracking-wider text-slate-400 ml-1">{{ __('category.name_label') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" 
                           class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-brand/20 transition-all" required>
                    @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl cursor-pointer hover:bg-brand/5 transition-colors">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                    <div>
                        <p class="text-xs font-bold text-slate-800">{{ __('category.is_active_label') }}</p>
                    </div>
                </label>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand text-white py-4 rounded-2xl font-bold shadow-xl shadow-brand/20 hover:bg-brand-dark transition-all active:scale-[0.98]">
                        {{ __('app.save_changes') }}
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
