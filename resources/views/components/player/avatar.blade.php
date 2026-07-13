@props([
    'user',
    'size' => 'h-9 w-9',
    'textSize' => 'text-sm',
])

@php
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp

@if ($user->avatar_url)
    <img
        src="{{ $user->avatar_url }}"
        alt="Avatar {{ $user->name }}"
        loading="lazy"
        {{ $attributes->merge(['class' => "{$size} shrink-0 rounded-full object-cover"]) }}
    >
@else
    <span
        aria-hidden="true"
        {{ $attributes->merge(['class' => "flex {$size} shrink-0 items-center justify-center rounded-full bg-primary-500 {$textSize} font-bold text-white"]) }}
    >
        {{ $initial }}
    </span>
@endif
