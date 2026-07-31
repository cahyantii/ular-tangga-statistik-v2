<!DOCTYPE html>
<html lang="id">
    {{-- Class "dark" ditambah/dihapus oleh Alpine store playerTheme (resources/js/theme.js).
         Script inline di bawah menerapkan preferensi SEBELUM render pertama (anti-FOUC). --}}
    <script>
        (function () {
            if (localStorage.getItem('player-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->id() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- ============================================================ --}}
        {{-- Player dark mode — CSS langsung (tidak butuh Vite build)     --}}
        {{-- Menangani komponen leaderboard, podium, badge & surface yang  --}}
        {{-- memerlukan transisi real-time saat toggle dark mode diklik.   --}}
        {{-- ============================================================ --}}
        <style>
            /* ---- Podium cards ---- */
            .podium-card-gold {
                background: linear-gradient(to bottom, #FEE3C2, #FFF2E3, #ffffff);
                border-color: #FCC386;
            }
            .podium-card-silver {
                background: linear-gradient(to bottom, #f1f5f9, #f8fafc, #ffffff);
                border-color: #cbd5e1;
            }
            .podium-card-bronze {
                background: linear-gradient(to bottom, #fed7aa, #fff7ed, #ffffff);
                border-color: #fdba74;
            }
            html.dark .podium-card-gold {
                background: linear-gradient(to bottom, rgba(120,53,15,0.55), rgba(30,27,20,0.9), #1e293b) !important;
                border-color: rgba(245,158,11,0.55) !important;
                box-shadow: 0 0 30px rgba(245,158,11,0.12), inset 0 1px 0 rgba(245,158,11,0.08);
            }
            html.dark .podium-card-silver {
                background: linear-gradient(to bottom, rgba(71,85,105,0.6), rgba(30,41,59,0.9), #1e293b) !important;
                border-color: rgba(148,163,184,0.45) !important;
            }
            html.dark .podium-card-bronze {
                background: linear-gradient(to bottom, rgba(120,53,15,0.4), rgba(30,25,15,0.85), #1e293b) !important;
                border-color: rgba(194,120,60,0.5) !important;
            }

            /* ---- Podium text ---- */
            html.dark .podium-card-gold   .podium-name  { color: #fde68a !important; }
            html.dark .podium-card-silver .podium-name  { color: #e2e8f0 !important; }
            html.dark .podium-card-bronze .podium-name  { color: #fed7aa !important; }
            html.dark .podium-card-gold   .podium-score { color: #fbbf24 !important; }

            /* ---- Leaderboard current-user row highlight ---- */
            .leaderboard-row-highlight {
                background: linear-gradient(to right, #EAF1FC, #f5f3ff);
            }
            html.dark .leaderboard-row-highlight {
                background: linear-gradient(to right, rgba(15,76,186,0.25), rgba(30,41,59,0.8)) !important;
            }

            /* ---- Leaderboard tab inactive ---- */
            html.dark .leaderboard-tab-inactive {
                background-color: #1e293b !important;
                color: #94a3b8 !important;
                border: 1px solid rgba(100,116,139,0.3) !important;
            }
            html.dark .leaderboard-tab-inactive:hover {
                background-color: #334155 !important;
                color: #cbd5e1 !important;
            }

            /* ---- Leaderboard table surface ---- */
            html.dark .bg-white    { background-color: #1e293b; }
            html.dark .bg-slate-50 { background-color: rgba(30,41,59,0.7); }

            /* ---- Surface umum yang berubah saat toggle ---- */
            html.dark .bg-primary-50  { background-color: rgba(15,76,186,0.15) !important; }
            html.dark .bg-primary-100 { background-color: rgba(15,76,186,0.25) !important; }
            html.dark .bg-violet-50   { background-color: rgba(109,40,217,0.15) !important; }
            html.dark .bg-violet-100  { background-color: rgba(109,40,217,0.25) !important; }
            html.dark .bg-accent-50   { background-color: rgba(246,139,31,0.12) !important; }
            html.dark .bg-green-50    { background-color: rgba(22,163,74,0.12) !important; }
            html.dark .bg-emerald-50  { background-color: rgba(16,185,129,0.12) !important; }
            html.dark .bg-blue-50     { background-color: rgba(37,99,235,0.12) !important; }
            html.dark .bg-rose-50     { background-color: rgba(225,29,72,0.12) !important; }
            html.dark .bg-red-50      { background-color: rgba(220,38,38,0.12) !important; }
            html.dark .bg-slate-100   { background-color: rgba(51,65,85,0.6) !important; }
        </style>
    </head>
    <body class="overflow-x-hidden font-sans antialiased bg-app-bg text-slate-800 dark:bg-[#0f172a] dark:text-slate-200">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
            <x-player.sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <x-player.topbar :score="$navScore ?? null">
                    @if (isset($header))
                        {{ $header }}
                    @endif
                </x-player.topbar>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 {{ request()->routeIs('game.show') ? 'max-xl:px-2 max-xl:py-0 max-xl:pb-28' : 'max-lg:pb-24' }}">
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
