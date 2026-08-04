@props([
    'label',
    'color' => 'slate',
])

@php
    $palette = [
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-dark-surface-hover dark:text-dark-muted',
        'blue' => 'bg-primary-100 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400',
        'green' => 'bg-secondary-100 text-secondary-600 dark:bg-secondary-900/20 dark:text-secondary-400',
        'amber' => 'bg-accent-100 text-accent-600 dark:bg-accent-900/20 dark:text-accent-400',
        'purple' => 'bg-violet-100 text-violet-600 dark:bg-violet-900/20 dark:text-violet-400',
        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
    ];

    $tone = $palette[$color] ?? $palette['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap {$tone}"]) }}>
    {{ $label }}
</span>
