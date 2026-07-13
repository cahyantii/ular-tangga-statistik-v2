@props([
    'navPromptText',
    'navLinkText',
    'navLinkHref',
])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-[#F7FAFF] font-sans text-slate-800 antialiased">
        <header class="border-b border-slate-100 bg-white">
            <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-4">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-11 w-11 shrink-0 object-contain md:h-12 md:w-12 lg:h-[52px] lg:w-[52px]"
                    >
                    <span class="leading-tight">
                        <span class="block text-base font-bold text-slate-900">Ular Tangga Statistik</span>
                        <span class="block text-xs text-slate-500">Belajar Statistik, Asyik &amp; Seru!</span>
                    </span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-slate-500 sm:inline">{{ $navPromptText }}</span>
                    <a
                        href="{{ $navLinkHref }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-700 px-4 py-2 text-sm font-semibold text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-50"
                    >
                        <x-player.icon name="user-plus" class="h-4 w-4" />
                        {{ $navLinkText }}
                    </a>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="grid grid-cols-1 items-center gap-10 md:grid-cols-2 md:gap-8 lg:gap-16">
                <div class="order-2 animate-fade-in-up md:order-1">
                    {{ $hero }}
                </div>

                <div class="order-1 animate-fade-in-up md:order-2">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
                <div class="flex gap-6">
                    <a href="{{ route('about') }}" class="hover:text-blue-700">Tentang</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-700">FAQ</a>
                </div>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
