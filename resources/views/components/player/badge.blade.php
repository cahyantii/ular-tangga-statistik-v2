@props([
    'label',
    'color' => 'slate',
])

@php
    $palette = [
        'slate' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        'blue' => 'bg-primary-100 text-primary-600 dark:bg-primary-950/60 dark:text-primary-300',
        'green' => 'bg-secondary-100 text-secondary-600 dark:bg-secondary-950/60 dark:text-secondary-300',
        'amber' => 'bg-accent-100 text-accent-600 dark:bg-amber-950/60 dark:text-amber-300',
        'purple' => 'bg-violet-100 text-violet-600 dark:bg-violet-950/60 dark:text-violet-300',
        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300',
    ];

    $tone = $palette[$color] ?? $palette['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap {$tone}"]) }}>
    {{ $label }}
</span>
