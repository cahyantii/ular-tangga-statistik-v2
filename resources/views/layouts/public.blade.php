<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-theme-init key="user-theme" />

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-white font-sans text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100">
        <header x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="sticky top-0 z-50 bg-white/90 shadow-sm backdrop-blur-md dark:bg-slate-900/90 dark:shadow-none dark:border-b dark:border-slate-800">
            <nav class="relative mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-10 w-10 shrink-0 rounded-full object-contain shadow-sm sm:h-12 sm:w-12"
                        loading="lazy"
                    >
                    <span class="truncate text-base font-extrabold text-slate-900 dark:text-slate-100 sm:text-lg">Ular Tangga Statistik</span>
                </a>

                <div class="hidden items-center gap-8 text-sm font-semibold text-slate-600 dark:text-slate-300 lg:flex">
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
                            class="border-b-2 pb-1 transition-colors duration-200 {{ $active ? 'border-primary-600 text-primary-600 dark:text-primary-400' : 'border-transparent hover:text-primary-600 dark:hover:text-primary-400' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="hidden shrink-0 items-center gap-4 lg:flex">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex shrink-0 items-center whitespace-nowrap rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:px-5 sm:py-2.5 sm:text-sm"
                        >
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-primary-600 dark:text-slate-300 dark:hover:text-primary-400">Masuk</a>
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex shrink-0 items-center whitespace-nowrap rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:px-5 sm:py-2.5 sm:text-sm"
                        >
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>

                {{-- Hamburger toggle (mobile & tablet) --}}
                <button
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-700 transition-colors duration-200 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 lg:hidden"
                    :aria-expanded="mobileMenuOpen.toString()"
                    aria-controls="mobile-menu"
                    aria-label="Buka menu navigasi"
                >
                    <span class="sr-only">Buka menu navigasi</span>
                    <span class="relative block h-4 w-5">
                        <span
                            class="absolute left-0 block h-0.5 w-5 rounded-full bg-current transition-all duration-300 ease-in-out"
                            :class="mobileMenuOpen ? 'top-1.5 rotate-45' : 'top-0 rotate-0'"
                        ></span>
                        <span
                            class="absolute left-0 top-1.5 block h-0.5 w-5 rounded-full bg-current transition-all duration-300 ease-in-out"
                            :class="mobileMenuOpen ? 'opacity-0' : 'opacity-100'"
                        ></span>
                        <span
                            class="absolute left-0 block h-0.5 w-5 rounded-full bg-current transition-all duration-300 ease-in-out"
                            :class="mobileMenuOpen ? 'top-1.5 -rotate-45' : 'top-3 rotate-0'"
                        ></span>
                    </span>
                </button>
            </nav>

            {{-- Mobile & tablet dropdown menu --}}
            <div
                x-show="mobileMenuOpen"
                x-cloak
                x-transition:enter="transition-all duration-300 ease-in-out"
                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all duration-300 ease-in-out"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                @click.outside="mobileMenuOpen = false"
                id="mobile-menu"
                class="absolute inset-x-0 top-full origin-top rounded-b-2xl border-b border-slate-100 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900 lg:hidden"
            >
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
                    <div class="flex flex-col gap-1 text-sm font-semibold text-slate-600 dark:text-slate-300">
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
                                class="rounded-lg border-b-2 px-3 py-2.5 transition-colors duration-200 {{ $active ? 'border-primary-600 font-semibold text-primary-600 dark:text-primary-400' : 'border-transparent hover:bg-slate-50 hover:text-primary-600 dark:hover:bg-slate-800 dark:hover:text-primary-400' }}"
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="my-3 border-t border-slate-100 dark:border-slate-800"></div>

                    <div class="flex flex-col gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors duration-200 hover:border-primary-200 hover:text-primary-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-primary-800 dark:hover:text-primary-400"
                            >
                                Masuk
                            </a>
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft"
                            >
                                Daftar Sekarang
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-slate-100 dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 text-sm text-slate-500 dark:text-slate-400 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
                <div class="flex gap-6">
                    <a href="{{ route('about') }}" class="hover:text-primary-600 dark:hover:text-primary-400">Tentang</a>
                    <a href="{{ route('faq') }}" class="hover:text-primary-600 dark:hover:text-primary-400">FAQ</a>
                </div>
            </div>
        </footer>
    </body>
</html>
