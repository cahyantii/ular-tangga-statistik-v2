@props([
    'icon',
    'label',
    'value',
    'color' => 'blue',
    'delay' => 0,
])

@php
    $palette = [
        'blue' => ['bg' => 'bg-primary-50 dark:bg-slate-800 dark:border dark:border-slate-700/60', 'icon' => 'bg-primary-500 text-white', 'text' => 'text-primary-600 dark:text-primary-400'],
        'green' => ['bg' => 'bg-secondary-50 dark:bg-slate-800 dark:border dark:border-slate-700/60', 'icon' => 'bg-secondary-500 text-white', 'text' => 'text-secondary-600 dark:text-secondary-400'],
        'amber' => ['bg' => 'bg-accent-50 dark:bg-slate-800 dark:border dark:border-slate-700/60', 'icon' => 'bg-accent-500 text-white', 'text' => 'text-accent-600 dark:text-amber-400'],
        'purple' => ['bg' => 'bg-violet-50 dark:bg-slate-800 dark:border dark:border-slate-700/60', 'icon' => 'bg-violet-500 text-white', 'text' => 'text-violet-600 dark:text-violet-400'],
        'rose' => ['bg' => 'bg-rose-50 dark:bg-slate-800 dark:border dark:border-slate-700/60', 'icon' => 'bg-rose-500 text-white', 'text' => 'text-rose-600 dark:text-rose-400'],
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
            <p class="truncate text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-0.5 text-2xl font-bold {{ $tone['text'] }}">{{ $value }}</p>
        </div>
    </div>
</div>
