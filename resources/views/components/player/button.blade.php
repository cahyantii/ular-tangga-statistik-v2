@props([
    'variant' => 'primary',
    'href' => null,
    'tag' => null,
])

@php
    $variants = [
        'primary' => 'bg-primary-500 text-white hover:bg-primary-600',
        'secondary' => 'bg-secondary-500 text-white hover:bg-secondary-600',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600',
        'outline' => 'border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface text-slate-600 dark:text-dark-text hover:bg-slate-50 dark:hover:bg-dark-surface-hover',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:-translate-y-0.5 ' . ($variants[$variant] ?? $variants['primary']);

    $element = $tag ?? ($href ? 'a' : 'button');
@endphp

@if ($element === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
