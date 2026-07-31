<!DOCTYPE html>
<html lang="id">
    {{-- Class "dark-admin" ditambah/dihapus oleh Alpine store adminTheme (resources/js/theme.js).
         Script inline di bawah menerapkan preferensi SEBELUM render pertama (anti-FOUC). --}}
    <script>
        (function () {
            if (localStorage.getItem('admin-theme') === 'dark') {
                document.documentElement.classList.add('dark-admin');
            }
        })();
    </script>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-id" content="{{ auth()->id() }}">

        <title>Admin - {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- ============================================================ --}}
        {{-- Admin dark mode — CSS langsung (tidak butuh Vite build)      --}}
        {{-- Ini meng-override semua elemen putih/terang di panel admin   --}}
        {{-- saat html.dark-admin aktif.                                  --}}
        {{-- ============================================================ --}}
        <style>
            /* Surfaces putih & abu menjadi gelap */
            html.dark-admin .bg-white,
            html.dark-admin .bg-white\/80,
            html.dark-admin .bg-white\/95 {
                background-color: #1e293b !important;
            }
            html.dark-admin .bg-slate-50,
            html.dark-admin .bg-gray-50 {
                background-color: rgba(30, 41, 59, 0.7) !important;
            }
            html.dark-admin .bg-slate-100,
            html.dark-admin .bg-gray-100 {
                background-color: rgba(51, 65, 85, 0.6) !important;
            }

            /* Teks */
            html.dark-admin .text-slate-900 { color: #f1f5f9 !important; }
            html.dark-admin .text-slate-800 { color: #e2e8f0 !important; }
            html.dark-admin .text-slate-700 { color: #cbd5e1 !important; }
            html.dark-admin .text-slate-600 { color: #94a3b8 !important; }
            html.dark-admin .text-slate-500 { color: #64748b !important; }

            /* Border */
            html.dark-admin .border-slate-100 { border-color: rgba(51, 65, 85, 0.4) !important; }
            html.dark-admin .border-slate-200 { border-color: rgba(71, 85, 105, 0.5) !important; }

            /* Input, select, textarea */
            html.dark-admin input[type="text"],
            html.dark-admin input[type="number"],
            html.dark-admin input[type="date"],
            html.dark-admin input[type="email"],
            html.dark-admin input[type="password"],
            html.dark-admin input[type="search"],
            html.dark-admin select,
            html.dark-admin textarea {
                background-color: #1e293b !important;
                color: #e2e8f0 !important;
                border-color: rgba(71, 85, 105, 0.6) !important;
            }
            html.dark-admin input::placeholder,
            html.dark-admin textarea::placeholder { color: #64748b !important; }

            /* Table */
            html.dark-admin thead tr,
            html.dark-admin thead,
            html.dark-admin .admin-thead-neutral,
            html.dark-admin .admin-thead-gradient {
                background: rgba(15, 23, 42, 0.85) !important;
                background-image: none !important;
            }
            html.dark-admin .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
            html.dark-admin .divide-y > :not([hidden]) ~ :not([hidden]) {
                border-color: rgba(51, 65, 85, 0.5) !important;
            }

            /* Header card gradient (soal index dll) */
            html.dark-admin .admin-header-gradient {
                background: linear-gradient(90deg, #1e293b 0%, rgba(6,78,59,0.25) 50%, #1e293b 100%) !important;
            }

            /* Hero overlay (dashboard) */
            html.dark-admin .admin-hero-overlay {
                background: linear-gradient(90deg, rgba(15,23,42,.96) 0%, rgba(15,23,42,.78) 42%, rgba(15,23,42,.15) 100%) !important;
            }

            /* Hover baris tabel */
            html.dark-admin .hover\:bg-green-50\/40:hover  { background-color: rgba(6,78,59,0.15) !important; }
            html.dark-admin .hover\:bg-slate-50\/60:hover  { background-color: rgba(51,65,85,0.2) !important; }
            html.dark-admin .hover\:bg-slate-50:hover      { background-color: rgba(51,65,85,0.3) !important; }
            html.dark-admin .hover\:bg-slate-100:hover     { background-color: rgba(51,65,85,0.5) !important; }
            html.dark-admin .hover\:bg-green-50:hover      { background-color: rgba(6,78,59,0.25) !important; }
            html.dark-admin .hover\:bg-blue-50:hover       { background-color: rgba(30,58,138,0.25) !important; }
            html.dark-admin .hover\:bg-red-50:hover        { background-color: rgba(127,29,29,0.25) !important; }

            /* Stat cards berwarna pastel */
            html.dark-admin .bg-green-50\/60  { background-color: rgba(6,78,59,0.25) !important; }
            html.dark-admin .bg-blue-50\/60   { background-color: rgba(30,58,138,0.25) !important; }
            html.dark-admin .bg-orange-50\/60 { background-color: rgba(124,45,18,0.25) !important; }
            html.dark-admin .bg-violet-50\/60 { background-color: rgba(76,29,149,0.25) !important; }
            html.dark-admin .bg-green-50      { background-color: rgba(6,78,59,0.2) !important; }
            html.dark-admin .bg-blue-50       { background-color: rgba(30,58,138,0.2) !important; }
            html.dark-admin .bg-emerald-50    { background-color: rgba(6,78,59,0.2) !important; }
            html.dark-admin .bg-violet-50     { background-color: rgba(76,29,149,0.2) !important; }
            html.dark-admin .bg-amber-50      { background-color: rgba(120,53,15,0.2) !important; }
            html.dark-admin .bg-orange-50     { background-color: rgba(124,45,18,0.2) !important; }
            html.dark-admin .bg-red-50        { background-color: rgba(127,29,29,0.2) !important; }
            html.dark-admin .bg-blue-100      { background-color: rgba(30,58,138,0.3) !important; }
            html.dark-admin .bg-violet-100    { background-color: rgba(76,29,149,0.3) !important; }
            html.dark-admin .bg-green-100     { background-color: rgba(6,78,59,0.3) !important; }
            html.dark-admin .bg-emerald-100   { background-color: rgba(6,78,59,0.3) !important; }
            html.dark-admin .bg-orange-100    { background-color: rgba(124,45,18,0.3) !important; }

            /* Border pastel */
            html.dark-admin .border-green-100  { border-color: rgba(34,197,94,0.18) !important; }
            html.dark-admin .border-blue-100   { border-color: rgba(59,130,246,0.18) !important; }
            html.dark-admin .border-orange-100 { border-color: rgba(249,115,22,0.18) !important; }
            html.dark-admin .border-violet-100 { border-color: rgba(139,92,246,0.18) !important; }

            /* Hardcoded hex */
            html.dark-admin .text-\[\#1E293B\] { color: #e2e8f0 !important; }
            html.dark-admin .text-\[\#1F2937\] { color: #e2e8f0 !important; }
            html.dark-admin .text-\[\#64748B\] { color: #94a3b8 !important; }
            html.dark-admin .text-\[\#92400E\] { color: #fde68a !important; }
            html.dark-admin .text-\[\#B45309\] { color: #fcd34d !important; }
            html.dark-admin .border-\[\#E5E7EB\],
            html.dark-admin .border-\[\#E5E7EB\]\/70 { border-color: rgba(51,65,85,0.5) !important; }
            html.dark-admin .bg-\[\#FEF3C7\]   { background-color: rgba(120,53,15,0.2) !important; }
            html.dark-admin .border-\[\#FDE68A\] { border-color: rgba(251,191,36,0.3) !important; }

            /* Tombol action di tabel (border outline) */
            html.dark-admin .border-green-200  { border-color: rgba(34,197,94,0.3) !important; }
            html.dark-admin .border-slate-200  { border-color: rgba(71,85,105,0.4) !important; }
            html.dark-admin .border-red-200    { border-color: rgba(252,165,165,0.3) !important; }
            html.dark-admin .border-green-500  { border-color: rgba(34,197,94,0.6) !important; }
            html.dark-admin .border-blue-500   { border-color: rgba(59,130,246,0.6) !important; }
            html.dark-admin .border-violet-500 { border-color: rgba(139,92,246,0.6) !important; }

            /* Tombol outline di header soal (bg-white + border) */
            html.dark-admin a.border-green-500,
            html.dark-admin a.border-blue-500,
            html.dark-admin a.border-violet-500 {
                background-color: #1e293b !important;
            }

            /* Badge chip kategori (violet) */
            html.dark-admin .bg-violet-100.text-violet-700 {
                background-color: rgba(76,29,149,0.3) !important;
                color: #c4b5fd !important;
            }
            html.dark-admin .bg-green-100.text-green-700,
            html.dark-admin .bg-green-100.text-green-600 {
                background-color: rgba(6,78,59,0.3) !important;
                color: #4ade80 !important;
            }
            html.dark-admin .bg-blue-100.text-blue-700 {
                background-color: rgba(30,58,138,0.3) !important;
                color: #93c5fd !important;
            }
            html.dark-admin .bg-red-50.text-red-600 {
                background-color: rgba(127,29,29,0.25) !important;
                color: #fca5a5 !important;
            }
            html.dark-admin .bg-amber-50.text-amber-600 {
                background-color: rgba(120,53,15,0.25) !important;
                color: #fcd34d !important;
            }
            html.dark-admin .bg-slate-100.text-slate-500 {
                background-color: rgba(51,65,85,0.5) !important;
                color: #94a3b8 !important;
            }

            /* Skeleton chart */
            html.dark-admin .bg-slate-100.animate-pulse { background-color: #1e293b !important; }

            /* Sidebar */
            html.dark-admin .admin-sidebar {
                background-color: #1a2744 !important;
                border-right-color: rgba(71,85,105,0.5) !important;
            }
            html.dark-admin nav .text-\[\#64748B\]  { color: #94a3b8 !important; }
            html.dark-admin nav .text-\[\#334155\]  { color: #94a3b8 !important; }
            html.dark-admin nav .text-\[\#1E293B\]  { color: #cbd5e1 !important; }

            /* Header */
            html.dark-admin header {
                background-color: #1e293b !important;
                border-bottom-color: rgba(71,85,105,0.5) !important;
            }
            html.dark-admin .admin-header-glass {
                background: rgba(15,23,42,0.88) !important;
                border-bottom-color: rgba(71,85,105,0.5) !important;
            }

            /* Footer */
            html.dark-admin footer {
                background-color: #1e293b !important;
                border-top-color: rgba(71,85,105,0.5) !important;
            }
            html.dark-admin footer p,
            html.dark-admin footer a { color: #64748b !important; }

            /* Notif dropdown */
            html.dark-admin .notif-dropdown-glass {
                background: rgba(15,23,42,0.97) !important;
                border-color: rgba(71,85,105,0.5) !important;
            }
            html.dark-admin .notif-dropdown-glass .text-slate-800,
            html.dark-admin .notif-dropdown-glass .font-semibold { color: #e2e8f0 !important; }
            html.dark-admin .notif-dropdown-glass .text-slate-500 { color: #94a3b8 !important; }
            html.dark-admin .notif-dropdown-glass .text-slate-400 { color: #64748b !important; }
            html.dark-admin .notif-dropdown-glass .border-slate-100,
            html.dark-admin .notif-dropdown-glass .border-b { border-color: rgba(71,85,105,0.5) !important; }
            html.dark-admin .notif-dropdown-glass .hover\:bg-slate-50:hover { background-color: rgba(51,65,85,0.35) !important; }
            html.dark-admin .notif-dropdown-glass .bg-slate-100 { background-color: rgba(51,65,85,0.7) !important; }

            /* Profile dropdown */
            html.dark-admin .rounded-xl.p-1\.5 {
                background-color: #1e293b !important;
                border-color: rgba(71,85,105,0.5) !important;
            }

            /* Flash messages */
            html.dark-admin .bg-emerald-50 { background-color: rgba(6,78,59,0.2) !important; }
            html.dark-admin .border-emerald-200 { border-color: rgba(52,211,153,0.3) !important; }
            html.dark-admin .text-emerald-800 { color: #6ee7b7 !important; }
        </style>
    </head>
    <body class="antialiased">
        <div
            x-data="{ sidebarOpen: false, profileOpen: false }"
            @keydown.escape.window="sidebarOpen = false"
            class="min-h-screen font-admin bg-admin-bg text-slate-800"
        >
            {{-- Sidebar backdrop (mobile & tablet): starts below the navbar so it never dims/covers it --}}
            <div
                x-show="sidebarOpen"
                x-cloak
                x-transition.opacity.duration.300ms
                @click="sidebarOpen = false"
                class="fixed inset-x-0 top-[72px] bottom-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden"
            ></div>

            {{-- Sidebar: starts right below the navbar on mobile/tablet so the navbar (and its
                 hamburger) is never covered; reverts to full height (own logo header) at lg+ --}}
            <aside
                id="admin-sidebar"
                class="admin-sidebar fixed left-0 top-[72px] bottom-0 z-40 flex w-[264px] shrink-0 -translate-x-full flex-col overflow-hidden shadow-[2px_0_16px_-4px_rgba(15,23,42,0.06)] transition-transform duration-300 ease-in-out lg:top-0 lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                {{-- URL gambar dekorasi diisi lewat CSS custom property dari asset()
                     Laravel (bukan url('/images/...') langsung di app.css) supaya
                     tetap resolve dengan benar baik saat `npm run dev` (Vite HMR
                     menyajikan CSS dari origin-nya sendiri, path root-relative di
                     dalam CSS jadi salah arah) maupun di build production. --}}
                style="--admin-sidebar-decoration: url('{{ asset('images/brand/sidebar-admin.png') }}')"
            >
                {{-- Sidebar header (relative z-[1]: memastikan tetap tampil DI ATAS
                     dekorasi latar .admin-sidebar::after, lihat resources/css/app.css) --}}
                <div class="relative z-[1] flex h-[110px] shrink-0 items-center gap-[17px] px-6">
                    <img
                        src="{{ asset('images/brand/logo-admin.png') }}"
                        alt="Logo Admin"
                        class="h-[91px] w-[91px] shrink-0 select-none object-contain"
                    >
                    <div class="min-w-0 leading-tight" style="text-shadow: 0 1px 3px rgba(255,255,255,.85);">
                        <p class="whitespace-nowrap text-base font-extrabold leading-tight">
                            <span class="text-[#F97316]">Ular</span>
                            <span class="text-[#EA580C]">Tangga</span>
                        </p>
                        <p class="whitespace-nowrap text-base font-extrabold leading-tight text-[#1E3A8A]">Statistik</p>
                        <p class="mt-0.5 whitespace-nowrap text-xs font-semibold text-[#16A34A]">Admin Panel</p>
                    </div>
                </div>

                {{-- Menu (relative z-[1]: memastikan tetap tampil & tetap bisa
                     diklik DI ATAS dekorasi latar .admin-sidebar::after).
                     SENGAJA tanpa overflow-y-auto/scroll apa pun - sidebar
                     tidak boleh punya scrollbar sendiri. <aside> pembungkus
                     sudah `overflow-hidden` (tanpa scrollbar apa pun) dan
                     TIDAK bergerak (position:fixed) saat halaman di-scroll;
                     yang scroll hanya kolom konten utama di sebelah kanan. --}}
                <nav class="relative z-[1] flex flex-1 flex-col space-y-4 px-4 pt-5 pb-7 text-sm">
                    <div>
                        <p class="px-3 mb-2 text-xs font-bold uppercase tracking-[0.12em] text-[#64748B]">Analytics</p>
                        @php $active = request()->routeIs('admin.dashboard'); @endphp
                        <a href="{{ route('admin.dashboard') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="home" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Dashboard</span>
                        </a>
                    </div>

                    <div class="space-y-1">
                        <p class="px-3 mb-2 text-xs font-bold uppercase tracking-[0.12em] text-[#64748B]">Management</p>

                        @php $active = request()->routeIs('admin.management.users.*'); @endphp
                        <a href="{{ route('admin.management.users.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="users" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Pengguna</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.kategori-materi.*'); @endphp
                        <a href="{{ route('admin.management.kategori-materi.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="tag" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Kategori Materi</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.materi.*'); @endphp
                        <a href="{{ route('admin.management.materi.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="book" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Materi</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.soal.*'); @endphp
                        <a href="{{ route('admin.management.soal.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="help" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Soal</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.papan-permainan.*'); @endphp
                        <a href="{{ route('admin.management.papan-permainan.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="grid" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Papan Permainan</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.achievements.*'); @endphp
                        <a href="{{ route('admin.management.achievements.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="trophy" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Achievement</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.game-settings.*'); @endphp
                        <a href="{{ route('admin.management.game-settings.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="settings" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Game Settings</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.notifications.*'); @endphp
                        <a href="{{ route('admin.management.notifications.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="bell" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Notifikasi</span>
                        </a>
                        @php $active = request()->routeIs('admin.management.feedback.*'); @endphp
                        <a href="{{ route('admin.management.feedback.index') }}"
                           @click="sidebarOpen = false"
                           class="flex h-11 items-center gap-3 rounded-[18px] px-4 font-semibold transition-colors {{ $active ? 'admin-sidebar-nav-active text-white' : 'text-[#334155] hover:bg-[rgba(34,197,94,.08)]' }}">
                            <x-player.icon name="mail" class="h-5 w-5 shrink-0" />
                            <span class="{{ $active ? 'text-white' : 'text-[#1E293B]' }}">Feedback</span>
                        </a>
                    </div>
                </nav>
            </aside>

            {{-- Content column: offset by sidebar width at lg+, full width (sidebar off-canvas) below lg --}}
            <div class="flex min-h-screen flex-col lg:ml-[264px]">
                {{-- Header --}}
                <header
                    x-data="{ scrolled: false }"
                    x-init="scrolled = window.scrollY > 8"
                    @scroll.window="scrolled = window.scrollY > 8"
                    :class="scrolled ? 'admin-header-glass' : 'border-b border-[#E5E7EB] bg-white shadow-sm'"
                    class="sticky top-0 z-50 flex h-[72px] shrink-0 items-center gap-3 px-6 transition-colors duration-200 sm:px-8"
                >
                    {{-- Hamburger toggle: lives in the navbar (left side), always above the
                         sidebar (z-50 > z-40) and never overlapped by it --}}
                    <button
                        type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-700 lg:hidden"
                        :aria-expanded="sidebarOpen.toString()"
                        aria-controls="admin-sidebar"
                        aria-label="Buka menu"
                    >
                        <span
                            class="absolute inline-flex transition-all duration-300 ease-in-out"
                            x-bind:class="sidebarOpen ? 'opacity-0 scale-75 rotate-45' : 'opacity-100 scale-100 rotate-0'"
                        >
                            <x-player.icon name="menu" class="h-6 w-6" />
                        </span>
                        <span
                            class="absolute inline-flex transition-all duration-300 ease-in-out"
                            x-bind:class="sidebarOpen ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-75 -rotate-45'"
                        >
                            <x-player.icon name="close" class="h-6 w-6" />
                        </span>
                    </button>

                    <div class="flex-1"></div>

                    {{-- Toggle mode gelap/terang admin --}}
                    <button
                        type="button"
                        @click="$store.adminTheme.toggle()"
                        :aria-label="$store.adminTheme.isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
                        :title="$store.adminTheme.isDark ? 'Mode Terang' : 'Mode Gelap'"
                        class="theme-toggle-btn text-slate-500 hover:text-slate-700"
                    >
                        {{-- Ikon bulan: tampil saat mode terang (klik → gelap) --}}
                        <svg x-show="!$store.adminTheme.isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
                        </svg>
                        {{-- Ikon matahari: tampil saat mode gelap (klik → terang) --}}
                        <svg x-show="$store.adminTheme.isDark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="5" />
                            <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                        </svg>
                    </button>

                    <div class="relative" x-data="{ notifOpen: false }">
                        <button
                            type="button"
                            @click="notifOpen = !notifOpen"
                            @click.outside="notifOpen = false"
                            class="js-notification-bell relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-50"
                            aria-label="Notifikasi"
                        >
                            <x-player.icon name="bell" class="h-5 w-5" />
                            <span
                                x-show="$store.notifications.unreadCount > 0"
                                x-cloak
                                x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"
                                class="notif-badge-pulse absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                            ></span>
                        </button>

                        <div
                            x-show="notifOpen"
                            x-cloak
                            x-transition
                            @click.outside="notifOpen = false"
                            class="notif-dropdown-glass absolute right-0 z-40 mt-2 w-80 max-w-[90vw] overflow-hidden rounded-2xl border border-admin-border shadow-lg"
                        >
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-bold text-slate-800">Notifikasi</p>
                                <button type="button" @click="$store.notifications.markAllRead()" class="text-xs font-semibold text-green-600 hover:underline">
                                    Tandai Semua Dibaca
                                </button>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="$store.notifications.loading">
                                    <div class="space-y-2 p-4">
                                        <div class="notif-skeleton-line h-10 rounded-lg"></div>
                                        <div class="notif-skeleton-line h-10 rounded-lg"></div>
                                    </div>
                                </template>

                                <template x-if="!$store.notifications.loading && $store.notifications.items.length === 0">
                                    <p class="p-6 text-center text-sm text-slate-400">Belum ada notifikasi.</p>
                                </template>

                                <template x-for="notification in $store.notifications.items" :key="notification.id">
                                    <div class="flex items-start gap-3 border-b border-slate-50 px-4 py-3 last:border-b-0 hover:bg-slate-50" :class="{ 'bg-green-50/40': !notification.read_at }">
                                        <span x-init="$store.notifications.mountIcon($el, notification.icon)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500"></span>
                                        <a :href="notification.url ?? '#'" @click="$store.notifications.markRead(notification.id)" class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-slate-800" x-text="notification.title"></p>
                                            <p class="mt-0.5 line-clamp-2 text-xs text-slate-500" x-text="notification.message"></p>
                                            <p class="mt-1 text-[11px] text-slate-400" x-text="notification.created_at_human"></p>
                                        </a>
                                        <button type="button" @click="$store.notifications.remove(notification.id)" aria-label="Hapus" class="shrink-0 text-slate-300 hover:text-red-500">
                                            <x-player.icon name="close" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <a href="{{ route('admin.management.notifications.index') }}" class="block border-t border-slate-100 px-4 py-2.5 text-center text-xs font-semibold text-slate-500 hover:bg-slate-50">
                                Lihat Semua Notifikasi
                            </a>
                        </div>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            @click="profileOpen = !profileOpen"
                            @click.outside="profileOpen = false"
                            class="flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-50"
                        >
                            <x-player.avatar :user="auth()->user()" size="h-9 w-9" />
                            <span class="hidden sm:block text-left leading-tight">
                                <span class="block text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                                <span class="block text-xs text-slate-500">
                                    {{ auth()->user()->role === \App\Enums\UserRole::Admin ? 'Administrator' : auth()->user()->role->label() }}
                                </span>
                            </span>
                            <x-player.icon name="chevron-down" class="hidden sm:block h-4 w-4 text-slate-400" />
                        </button>

                        <div
                            x-show="profileOpen"
                            x-cloak
                            x-transition
                            class="absolute right-0 z-40 mt-2 w-48 rounded-xl border border-admin-border bg-white p-1.5 shadow-lg"
                        >
                            <div class="px-2.5 py-2 sm:hidden">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ auth()->user()->role === \App\Enums\UserRole::Admin ? 'Administrator' : auth()->user()->role->label() }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50"
                                >
                                    <x-player.icon name="logout" class="h-4 w-4" />
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <x-admin.flash />
                    {{ $slot }}
                </main>

                {{-- Footer --}}
                <footer class="shrink-0 border-t border-admin-border bg-white px-4 py-4 sm:px-6">
                    <div class="flex flex-col items-center justify-between gap-2 text-xs text-slate-500 sm:flex-row">
                        <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Semua hak dilindungi.</p>
                        <div class="flex items-center gap-4">
                            <a href="#" class="hover:text-slate-700">Dokumentasi</a>
                            <a href="#" class="hover:text-slate-700">Bantuan</a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <x-player.notification-icon-templates />

        @stack('scripts')
    </body>
</html>
