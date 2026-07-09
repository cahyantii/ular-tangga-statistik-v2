@props(['name'])

@php
    $classes = $attributes->get('class') ?: 'w-5 h-5';
@endphp

@switch($name)
    @case('home')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 11.5 12 4l9 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />
        </svg>
        @break

    @case('trophy')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10v5a5 5 0 0 1-10 0V4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 5H4v1a4 4 0 0 0 4 4M17 5h3v1a4 4 0 0 1-4 4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v3m-3 3h6m-6 0v-1a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1" />
        </svg>
        @break

    @case('star')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.6 5.6 6.1.6-4.6 4.1 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4.1 6.1-.6L12 3Z" />
        </svg>
        @break

    @case('chart-bar')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20V10m6.5 10V4M17 20v-6" />
        </svg>
        @break

    @case('certificate')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <rect x="3.5" y="4" width="17" height="12" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20.5 12 18l3 2.5V16H9v4.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 11h6" />
        </svg>
        @break

    @case('user')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="8" r="3.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20c0-4 3.5-6.5 7.5-6.5s7.5 2.5 7.5 6.5" />
        </svg>
        @break

    @case('users')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="9" cy="8" r="3" />
            <circle cx="17" cy="9" r="2.4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19.5c0-3.4 2.6-5.5 5.5-5.5s5.5 2.1 5.5 5.5M15.5 14.6c2.3.3 4 2 4 4.9" />
        </svg>
        @break

    @case('logout')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5H6a1.5 1.5 0 0 0-1.5 1.5v12A1.5 1.5 0 0 0 6 19.5h3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 8l4 4-4 4M18 12H9" />
        </svg>
        @break

    @case('bell')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5a5 5 0 0 0-5 5c0 5.8-2.2 7.5-2.2 7.5h14.4S17 14.3 17 8.5a5 5 0 0 0-5-5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 19a2.5 2.5 0 0 0 5 0" />
        </svg>
        @break

    @case('chevron-down')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
        </svg>
        @break

    @case('check')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4 10-10" />
        </svg>
        @break

    @case('check-circle')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.5 2.5 2.5 4.5-5" />
        </svg>
        @break

    @case('clock')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 2" />
        </svg>
        @break

    @case('gamepad')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8.5h10a4.5 4.5 0 0 1 4.4 5.5l-.3 1.3a2.3 2.3 0 0 1-4-.9l-.4-1.4H7.3l-.4 1.4a2.3 2.3 0 0 1-4 .9l-.3-1.3A4.5 4.5 0 0 1 7 8.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 11v3M7 12.5h3M16 11h.01M18 13h.01" />
        </svg>
        @break

    @case('cpu')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <rect x="7" y="7" width="10" height="10" rx="1.5" />
            <rect x="10" y="10" width="4" height="4" rx="0.5" />
            <path stroke-linecap="round" d="M9 3.5v2M12 3.5v2M15 3.5v2M9 18.5v2M12 18.5v2M15 18.5v2M3.5 9h2M3.5 12h2M3.5 15h2M18.5 9h2M18.5 12h2M18.5 15h2" />
        </svg>
        @break

    @case('arrow-right')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0-5.5-5.5M19.5 12 14 17.5" />
        </svg>
        @break

    @case('menu')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16M4 12h16M4 17.5h16" />
        </svg>
        @break

    @case('close')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
        </svg>
        @break

    @case('lock')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <rect x="5.5" y="10.5" width="13" height="9" rx="1.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10.5V8a4 4 0 0 1 8 0v2.5" />
        </svg>
        @break

    @case('download')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5v11m0 0 3.5-3.5M12 14.5 8.5 11M4.5 17v2a1.5 1.5 0 0 0 1.5 1.5h12a1.5 1.5 0 0 0 1.5-1.5v-2" />
        </svg>
        @break

    @case('eye')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" />
            <circle cx="12" cy="12" r="2.75" />
        </svg>
        @break

    @case('flag')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3.5v17" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4.5c2-1.5 4-1.5 6 0s4 1.5 6 0v8c-2 1.5-4 1.5-6 0s-4-1.5-6 0v-8Z" />
        </svg>
        @break

    @case('book')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.5c-1.8-1.3-4-1.8-6.5-1.5v13c2.5-.3 4.7.2 6.5 1.5m0-13c1.8-1.3 4-1.8 6.5-1.5v13c-2.5-.3-4.7.2-6.5 1.5m0-13v13" />
        </svg>
        @break

    @case('help')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.8 9.3a2.3 2.3 0 1 1 3.4 2c-.9.6-1.2 1-1.2 2" />
            <circle cx="12" cy="16.3" r="0.4" fill="currentColor" />
        </svg>
        @break

    @case('snake')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8c0-2.5 2-4 4.5-4S13 5.5 13 8s-2 4-4.5 4S4 14.5 4 17s2 4 4.5 4 4.5-1.5 4.5-4" />
            <circle cx="17" cy="6.5" r="2.5" />
            <path stroke-linecap="round" d="M19.2 5.8 21 5M19.2 7.2 21 8" />
        </svg>
        @break

    @case('ladder')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" d="M7 3v18M17 3v18" />
            <path stroke-linecap="round" d="M7 7h10M7 12h10M7 17h10" />
        </svg>
        @break

    @case('target')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
            <circle cx="12" cy="12" r="4.5" />
            <circle cx="12" cy="12" r="0.6" fill="currentColor" />
        </svg>
        @break

    @case('alert')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 21.5 20h-19L12 3.5Z" />
            <path stroke-linecap="round" d="M12 9.5v4.2" />
            <circle cx="12" cy="16.7" r="0.5" fill="currentColor" />
        </svg>
        @break

    @case('coin')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
            <circle cx="12" cy="12" r="5.2" />
            <path stroke-linecap="round" d="M12 9v6M10.3 10.3h2.4a1.3 1.3 0 1 1 0 2.6h-2M10.3 13.7h2.4" />
        </svg>
        @break

    @case('dice')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <rect x="3.5" y="3.5" width="17" height="17" rx="4" />
            <circle cx="8.3" cy="8.3" r="1.1" fill="currentColor" stroke="none" />
            <circle cx="15.7" cy="8.3" r="1.1" fill="currentColor" stroke="none" />
            <circle cx="12" cy="12" r="1.1" fill="currentColor" stroke="none" />
            <circle cx="8.3" cy="15.7" r="1.1" fill="currentColor" stroke="none" />
            <circle cx="15.7" cy="15.7" r="1.1" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('shoe')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19v-4.5c0-2 1.2-3 2.5-3.8L11 8c1-1.6 2.7-2.5 4.5-2.5.8 0 1.5.7 1.5 1.5v1.3c1.7.4 3 2 3 3.9V19a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Z" />
            <path stroke-linecap="round" d="M4 16.5h16" />
        </svg>
        @break

    @default
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
        </svg>
@endswitch
