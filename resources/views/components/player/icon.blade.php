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

    @case('crown')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M4 17h16M4 17 3 8l4.5 4L12 6l4.5 6L21 8l-1 9" />
            <circle cx="12" cy="6" r="1" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('pawn')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <circle cx="12" cy="7.5" r="3" />
            <path d="M9.2 12c-1 1.3-1.6 3-1.6 4.8h8.8c0-1.8-.6-3.5-1.6-4.8" />
            <path d="M6.5 19.5h11" />
        </svg>
        @break

    @case('shield')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 3.5 19 6.5v5c0 5-3 8-7 9-4-1-7-4-7-9v-5L12 3.5Z" />
            <path d="m12 9.3 1 2 2.2.3-1.6 1.5.4 2.2-2-1-2 1 .4-2.2-1.6-1.5 2.2-.3 1-2Z" />
        </svg>
        @break

    @case('wreath')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M6 19c-2-3-2-8 1-11" />
            <path d="M18 19c2-3 2-8-1-11" />
            <path d="M7.3 15.8 5.4 16.3M7.7 12.6l-1.9.3M8.6 9.6 6.9 9.4" />
            <path d="M16.7 15.8 18.6 16.3M16.3 12.6l1.9.3M15.4 9.6l1.7-.2" />
            <circle cx="12" cy="18.3" r="1.2" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('building')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M5 20.5V5.5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v15" />
            <path d="M13 10.5h5a1 1 0 0 1 1 1v9" />
            <path d="M8 8.5h.01M11 8.5h.01M8 11.5h.01M11 11.5h.01M8 14.5h.01M11 14.5h.01M16 13.5h.01M16 16.5h.01" />
            <path d="M9 20.5v-3h2v3" />
        </svg>
        @break

    @case('pie-chart')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 3.5v8.5h8.5a8.5 8.5 0 1 1-8.5-8.5Z" />
            <path d="M15.5 3.9A8.5 8.5 0 0 1 20.1 8.5H15.5V3.9Z" />
        </svg>
        @break

    @case('medal')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M8.5 3.5 6 9l4 2.5M15.5 3.5 18 9l-4 2.5" />
            <path d="M8.5 3.5h7L18 9H6l2.5-5.5Z" />
            <circle cx="12" cy="15" r="5.5" />
            <path d="M12 12v6M9.5 15h5" />
        </svg>
        @break

    @case('flame')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M12 3.5c1 2 .5 3.4-.5 4.6-1.2-1-1.8-.4-2 .6-1.8 1.2-3 3.2-3 5.5a5.5 5.5 0 0 0 11 0c0-3-1.6-4.8-2.7-6.1-.5 1-1 1.5-1.7 1.3-.9-.3-1.4-2.9-1.1-5.9Z" />
            <path d="M10.3 14a1.7 1.7 0 0 0 3.4 0c0-1.1-.9-1.8-1.7-2.8-.8 1-1.7 1.7-1.7 2.8Z" />
        </svg>
        @break

    @case('lightbulb')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M9 18.5h6M9.7 21h4.6" />
            <path d="M12 3.5a5.8 5.8 0 0 0-3.4 10.5c.6.5 1 1.2 1 2h4.8c0-.8.4-1.5 1-2A5.8 5.8 0 0 0 12 3.5Z" />
            <path d="M12 6.7a3.3 3.3 0 0 0-2.3 5.6" />
        </svg>
        @break

    @case('user-plus')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <circle cx="10" cy="8" r="3.5" />
            <path d="M3.5 20c0-3.6 2.9-6.5 6.5-6.5 1.3 0 2.5.4 3.5 1" />
            <path d="M18 9v6M15 12h6" />
        </svg>
        @break

    @case('gift')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="4" y="9.5" width="16" height="4" rx="1" />
            <rect x="5.5" y="13.5" width="13" height="7" rx="1" />
            <path d="M12 9.5v11" />
            <path d="M12 9.5c-1.5 0-3-1-3-2.8A2.2 2.2 0 0 1 11.2 4.5c1.8 0 2.8 2.3 2.8 5" />
            <path d="M12 9.5c1.5 0 3-1 3-2.8A2.2 2.2 0 0 0 12.8 4.5c-1.8 0-2.8 2.3-2.8 5" />
        </svg>
        @break

    @case('mail')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="3.5" y="5.5" width="17" height="13" rx="2.5" />
            <path d="m4.5 7 7.5 6 7.5-6" />
        </svg>
        @break

    @case('eye-off')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M3.5 3.5l17 17" />
            <path d="M10.6 5.7A10.4 10.4 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a15.7 15.7 0 0 1-3.4 4.2M7.4 7.3A15.6 15.6 0 0 0 2.5 12s3.5 6.5 9.5 6.5c1.4 0 2.6-.3 3.7-.8" />
            <path d="M9.9 10a2.75 2.75 0 0 0 3.9 3.9" />
        </svg>
        @break

    @case('save')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M5 4.5h11l3.5 3.5v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-13a1 1 0 0 1 1-1Z" />
            <path d="M8 4.5v5h7v-5M8 19.5V14h8v5.5" />
        </svg>
        @break

    @case('upload-cloud')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M7.5 18.5a4.5 4.5 0 0 1-.8-8.93 5.5 5.5 0 0 1 10.7-1.9 4 4 0 0 1-.9 7.83" />
            <path d="M12 20v-8m0 0-3 3m3-3 3 3" />
        </svg>
        @break

    @case('calendar')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="3.5" y="5.5" width="17" height="15" rx="2.5" />
            <path d="M3.5 10h17M8 3.5v4M16 3.5v4" />
        </svg>
        @break

    @case('trend-up')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M3.5 16.5 9 11l4 4 7.5-8.5" />
            <path d="M15.5 6h5v5" />
        </svg>
        @break

    @case('graduation-cap')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="m3 9 9-4 9 4-9 4-9-4Z" />
            <path d="M7 11v4.5c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5V11" />
            <path d="M20.5 9v6" />
        </svg>
        @break

    @case('clipboard-search')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="5" y="5.5" width="14" height="16" rx="2" />
            <path d="M9 4.5h6a1 1 0 0 1 1 1v1.5H8V5.5a1 1 0 0 1 1-1Z" />
            <circle cx="10.5" cy="13.5" r="3" />
            <path d="m13.8 16.8 2 2" />
        </svg>
        @break

    @case('grid')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" />
        </svg>
        @break

    @case('wifi')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M4 8.5a13 13 0 0 1 16 0" />
            <path d="M7 12.3a8.5 8.5 0 0 1 10 0" />
            <path d="M10 16a4 4 0 0 1 4 0" />
            <circle cx="12" cy="19.3" r="0.6" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('headset')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="{{ $classes }}">
            <path d="M4 13v-1a8 8 0 0 1 16 0v1" />
            <rect x="3" y="13" width="4" height="6" rx="1.5" />
            <rect x="17" y="13" width="4" height="6" rx="1.5" />
            <path d="M19 19v1a2 2 0 0 1-2 2h-3" />
        </svg>
        @break

    @default
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="{{ $classes }}">
            <circle cx="12" cy="12" r="8.5" />
        </svg>
@endswitch
