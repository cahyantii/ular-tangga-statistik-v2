@props([
    'label',
    'color' => 'slate',
])

@php
    $palette = [
        'slate' => 'bg-slate-100 text-slate-600',
        'blue' => 'bg-primary-100 text-primary-600',
        'green' => 'bg-secondary-100 text-secondary-600',
        'amber' => 'bg-accent-100 text-accent-600',
        'purple' => 'bg-violet-100 text-violet-600',
        'rose' => 'bg-rose-100 text-rose-600',
    ];

    $tone = $palette[$color] ?? $palette['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold whitespace-nowrap {$tone}"]) }}>
    {{ $label }}
</span>
