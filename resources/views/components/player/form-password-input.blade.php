@props([
    'label',
    'name',
    'bag' => 'default',
    'tone' => 'violet',
    'size' => 'md',
])

@php
    $hasError = $errors->getBag($bag)->has($name);
    $isLg = $size === 'lg';

    $borderClasses = $hasError
        ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-100 dark:border-rose-800 dark:focus:border-rose-500'
        : ($isLg
            ? 'border-slate-200 focus:border-blue-700 focus:ring-blue-100 dark:border-slate-700 dark:focus:border-blue-500'
            : ($tone === 'primary'
                ? 'border-slate-200 focus:border-primary-400 focus:ring-primary-100 dark:border-slate-700 dark:focus:border-primary-500'
                : 'border-slate-200 focus:border-violet-400 focus:ring-violet-100 dark:border-slate-700 dark:focus:border-violet-500'));

    $sizeClasses = $isLg ? 'h-14 rounded-[14px] text-base pl-12 pr-12' : 'py-2.5 rounded-xl text-sm pl-10 pr-11';
    $iconSizeClasses = $isLg ? 'h-5 w-5' : 'h-4 w-4';
    $iconPad = $isLg ? 'pl-4' : 'pl-3.5';
    $btnPad = $isLg ? 'pr-4' : 'pr-3.5';
@endphp

<div x-data="{ show: false }">
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-dark-text">{{ $label }}</label>

    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center {{ $iconPad }} text-slate-400">
            <x-player.icon name="lock" class="{{ $iconSizeClasses }}" />
        </span>

        <input
            :type="show ? 'text' : 'password'"
            id="{{ $name }}"
            name="{{ $name }}"
            aria-invalid="{{ $hasError ? 'true' : 'false' }}"
            {{ $attributes->merge(['class' => "w-full border {$borderClasses} {$sizeClasses} bg-white dark:bg-dark-surface-hover text-slate-700 dark:text-dark-text placeholder:text-slate-400 dark:placeholder:text-dark-muted transition focus:outline-none focus:ring-2"]) }}
        >

        <button
            type="button"
            @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center {{ $btnPad }} text-slate-400 dark:text-dark-muted transition hover:text-slate-600 dark:hover:text-dark-text"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
        >
            <x-player.icon name="eye" class="{{ $iconSizeClasses }}" x-show="!show" />
            <x-player.icon name="eye-off" class="{{ $iconSizeClasses }}" x-show="show" x-cloak />
        </button>
    </div>

    @error($name, $bag)
        <p class="mt-1.5 text-xs font-medium text-rose-500">{{ $message }}</p>
    @enderror
</div>
