@props([
    'icon' => null,
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'bag' => 'default',
    'size' => 'md',
])

@php
    $hasError = $errors->getBag($bag)->has($name);
    $isLg = $size === 'lg';

    $borderClasses = $hasError
        ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100 dark:border-rose-500/50 dark:focus:ring-rose-500/20'
        : ($isLg
            ? 'border-slate-200 focus:border-blue-700 focus:ring-blue-100 dark:border-slate-600 dark:focus:ring-blue-500/20'
            : 'border-slate-200 focus:border-primary-400 focus:ring-primary-100 dark:border-slate-600 dark:focus:ring-primary-500/20');

    $sizeClasses = $isLg ? 'h-14 rounded-[14px] text-base' : 'py-2.5 rounded-xl text-sm';
    $iconSizeClasses = $isLg ? 'h-5 w-5' : 'h-4 w-4';
    $iconPad = $isLg ? 'pl-12' : 'pl-10';
    $noIconPad = $isLg ? 'pl-4' : 'pl-3.5';
@endphp

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $label }}</label>

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center {{ $isLg ? 'pl-4' : 'pl-3.5' }} text-slate-400 dark:text-slate-500">
                <x-player.icon :name="$icon" class="{{ $iconSizeClasses }}" />
            </span>
        @endif

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            {{ $attributes->merge([
                'class' => "w-full border {$borderClasses} {$sizeClasses} bg-white text-slate-700 placeholder:text-slate-400 transition focus:outline-none focus:ring-2 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 "
                    . ($icon ? $iconPad : $noIconPad)
                    . (isset($suffix) ? ' pr-32' : ($isLg ? ' pr-4' : ' pr-3.5')),
            ]) }}
        >

        @isset($suffix)
            <span class="absolute inset-y-0 right-2.5 flex items-center">{{ $suffix }}</span>
        @endisset
    </div>

    @error($name, $bag)
        <p class="mt-1.5 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
