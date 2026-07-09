@props([
    'icon',
    'label',
    'value',
    'color' => 'blue',
    'delay' => 0,
])

@php
    $palette = [
        'blue' => ['bg' => 'bg-primary-50', 'icon' => 'bg-primary-500 text-white', 'text' => 'text-primary-600'],
        'green' => ['bg' => 'bg-secondary-50', 'icon' => 'bg-secondary-500 text-white', 'text' => 'text-secondary-600'],
        'amber' => ['bg' => 'bg-accent-50', 'icon' => 'bg-accent-500 text-white', 'text' => 'text-accent-600'],
        'purple' => ['bg' => 'bg-violet-50', 'icon' => 'bg-violet-500 text-white', 'text' => 'text-violet-600'],
        'rose' => ['bg' => 'bg-rose-50', 'icon' => 'bg-rose-500 text-white', 'text' => 'text-rose-600'],
    ];

    $tone = $palette[$color] ?? $palette['blue'];
@endphp

<div
    style="animation-delay: {{ $delay }}ms"
    class="animate-fade-in-up rounded-2xl {{ $tone['bg'] }} p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-soft sm:p-5"
>
    <div class="flex items-center gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tone['icon'] }}">
            <x-player.icon :name="$icon" class="h-5 w-5" />
        </span>
        <div class="min-w-0">
            <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-0.5 text-2xl font-bold {{ $tone['text'] }}">{{ $value }}</p>
        </div>
    </div>
</div>
