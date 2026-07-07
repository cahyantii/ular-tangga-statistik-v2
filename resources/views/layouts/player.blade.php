<!DOCTYPE html>
<html lang="id" x-data="{ mobileMenuOpen: false }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <nav class="bg-white border-b border-slate-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center gap-8">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-extrabold text-emerald-700">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">UT</span>
                            Ular Tangga Statistik
                        </a>

                        <div class="hidden sm:flex sm:items-center sm:gap-1">
                            <a href="{{ route('dashboard') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('leaderboard') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('leaderboard') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Leaderboard
                            </a>
                            <a href="{{ route('player.achievements') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('player.achievements') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Achievement
                            </a>
                            <a href="{{ route('player.progress') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('player.progress') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Progress
                            </a>
                            <a href="{{ route('player.certificate') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('player.certificate') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Sertifikat
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('profile.edit') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-50' }}">
                                Profil
                            </a>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                        <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Keluar
                            </button>
                        </form>
                    </div>

                    <div class="flex items-center sm:hidden">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-500">
                            &#9776;
                        </button>
                    </div>
                </div>
            </div>

            <div class="sm:hidden" x-show="mobileMenuOpen" x-cloak>
                <div class="space-y-1 border-t border-slate-200 px-4 py-3">
                    <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard</a>
                    <a href="{{ route('leaderboard') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Leaderboard</a>
                    <a href="{{ route('player.achievements') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Achievement</a>
                    <a href="{{ route('player.progress') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Progress</a>
                    <a href="{{ route('player.certificate') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Sertifikat</a>
                    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-slate-50">Keluar</button>
                    </form>
                </div>
            </div>
        </nav>

        @if (isset($header))
            <header class="bg-white border-b border-slate-200">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        @stack('scripts')
    </body>
</html>
