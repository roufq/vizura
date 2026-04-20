@props(['type' => 'info', 'message'])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
        'error' => 'bg-rose-50 border-rose-100 text-rose-700',
        'warning' => 'bg-amber-50 border-amber-100 text-amber-700',
        default => 'bg-blue-50 border-blue-100 text-blue-700',
    };

    $icon = match($type) {
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        default => 'info-circle',
    };
@endphp

<div {{ $attributes->merge(['class' => "p-4 rounded-2xl border flex items-center gap-3 $classes transition-all duration-300"]) }}>
    <div class="flex-shrink-0">
        <i class="fa fa-{{ $icon }} text-lg"></i>
    </div>
    <div class="flex-1">
        <p class="text-sm font-bold leading-tight">{{ $message }}</p>
    </div>
</div>
