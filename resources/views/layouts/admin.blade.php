<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin - {{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-200 transform transition-transform lg:translate-x-0 lg:static lg:inset-auto"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="h-16 flex items-center px-6 font-extrabold text-white text-lg border-b border-slate-800">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 mr-2">UT</span>
                    Admin Panel
                </div>

                <nav class="px-3 py-6 space-y-6 text-sm">
                    <div>
                        <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Analytics</p>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Dashboard
                        </a>
                    </div>

                    <div>
                        <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Management</p>

                        <a href="{{ route('admin.management.users.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.users.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Pengguna
                        </a>
                        <a href="{{ route('admin.management.kategori-materi.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.kategori-materi.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Kategori Materi
                        </a>
                        <a href="{{ route('admin.management.materi.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.materi.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Materi
                        </a>
                        <a href="{{ route('admin.management.soal.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.soal.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Soal
                        </a>
                        <a href="{{ route('admin.management.papan-permainan.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.papan-permainan.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Papan Permainan
                        </a>
                        <a href="{{ route('admin.management.achievements.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.achievements.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Achievement
                        </a>
                        <a href="{{ route('admin.management.game-settings.index') }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.management.game-settings.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                            Game Settings
                        </a>
                    </div>
                </nav>
            </aside>

            <div class="flex-1 flex flex-col lg:pl-0">
                <!-- Navbar -->
                <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500">
                        &#9776;
                    </button>

                    <div class="flex-1"></div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Keluar
                            </button>
                        </form>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <x-admin.flash />
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
