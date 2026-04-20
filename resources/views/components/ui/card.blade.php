@props(['title' => null, 'icon' => null, 'actions' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden']) }}>
    @if($title || $icon || $actions)
        <div class="px-8 py-6 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                @if($icon)
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center flex-shrink-0">
                        <i class="fa fa-{{ $icon }}"></i>
                    </div>
                @endif
                @if($title)
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight truncate">{{ $title }}</h3>
                @endif
            </div>
            @if($actions)
                <div class="flex flex-wrap items-center gap-2 md:justify-end">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="p-8">
        {{ $slot }}
    </div>
</div>
