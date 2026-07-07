<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-white">
        <header class="border-b border-slate-100 bg-white/80 backdrop-blur sticky top-0 z-30">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-lg text-emerald-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">UT</span>
                    Ular Tangga Statistik
                </a>

                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}" class="hover:text-emerald-700 {{ request()->routeIs('home') ? 'text-emerald-700' : '' }}">Beranda</a>
                    <a href="{{ route('about') }}" class="hover:text-emerald-700 {{ request()->routeIs('about') ? 'text-emerald-700' : '' }}">Tentang</a>
                    <a href="{{ route('how-to-play') }}" class="hover:text-emerald-700 {{ request()->routeIs('how-to-play') ? 'text-emerald-700' : '' }}">Cara Bermain</a>
                    <a href="{{ route('faq') }}" class="hover:text-emerald-700 {{ request()->routeIs('faq') ? 'text-emerald-700' : '' }}">FAQ</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-700">Masuk</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition">Daftar Gratis</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-100 mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
                <div class="flex gap-6">
                    <a href="{{ route('about') }}" class="hover:text-emerald-700">Tentang</a>
                    <a href="{{ route('faq') }}" class="hover:text-emerald-700">FAQ</a>
                </div>
            </div>
        </footer>
    </body>
</html>
