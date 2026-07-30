<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->id() }}">

        <x-theme-init key="user-theme" />

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden font-sans antialiased bg-app-bg text-slate-800 dark:bg-slate-900 dark:text-slate-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            <x-player.sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <x-player.topbar :score="$navScore ?? null">
                    @if (isset($header))
                        {{ $header }}
                    @endif
                </x-player.topbar>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 {{ request()->routeIs('game.show') ? '' : 'max-lg:pb-24' }}">
                    <x-admin.flash />
                    {{ $slot }}
                </main>
            </div>

            <x-player.notification-icon-templates />
            <x-player.feedback-modal />

            {{--
                FAB navigasi global — disembunyikan HANYA di halaman gameplay
                (route game.show), karena halaman itu sudah punya FAB aksi
                permainan sendiri (5 tombol berbeda: Progress/Pemain/Dadu/Log/
                Keluar, lihat #mobile-action-fab di resources/views/game/show.blade.php)
                supaya tidak dobel FAB di layar yang sama.
            --}}
            @unless (request()->routeIs('game.show'))
                <x-player.mobile-fab />
            @endunless
        </div>

        @stack('scripts')
    </body>
</html>
