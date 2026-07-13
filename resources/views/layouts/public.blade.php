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
    <body class="overflow-x-hidden bg-white font-sans text-slate-800 antialiased">
        <header class="sticky top-0 z-30 bg-white shadow-sm">
            <nav class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-10 w-10 shrink-0 rounded-full object-contain shadow-sm sm:h-12 sm:w-12"
                        loading="lazy"
                    >
                    <span class="truncate text-base font-extrabold text-slate-900 sm:text-lg">Ular Tangga Statistik</span>
                </a>

                <div class="hidden items-center gap-8 text-sm font-semibold text-slate-600 md:flex">
                    @foreach ([
                        'home' => ['route' => 'home', 'label' => 'Beranda'],
                        'about' => ['route' => 'about', 'label' => 'Tentang'],
                        'how-to-play' => ['route' => 'how-to-play', 'label' => 'Cara Bermain'],
                        'faq' => ['route' => 'faq', 'label' => 'FAQ'],
                    ] as $key => $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            aria-current="{{ $active ? 'page' : 'false' }}"
                            class="border-b-2 pb-1 transition-colors duration-200 {{ $active ? 'border-primary-600 text-primary-600' : 'border-transparent hover:text-primary-600' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="flex shrink-0 items-center gap-4">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex shrink-0 items-center whitespace-nowrap rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:px-5 sm:py-2.5 sm:text-sm"
                        >
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-primary-600 sm:inline">Masuk</a>
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex shrink-0 items-center whitespace-nowrap rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:px-5 sm:py-2.5 sm:text-sm"
                        >
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
                <div class="flex gap-6">
                    <a href="{{ route('about') }}" class="hover:text-primary-600">Tentang</a>
                    <a href="{{ route('faq') }}" class="hover:text-primary-600">FAQ</a>
                </div>
            </div>
        </footer>
    </body>
</html>
