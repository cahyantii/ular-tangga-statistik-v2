@php
    $links = [
        ['route' => 'dashboard', 'is' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'leaderboard', 'is' => 'leaderboard', 'label' => 'Leaderboard', 'icon' => 'trophy'],
        ['route' => 'player.achievements', 'is' => 'player.achievements', 'label' => 'Achievement', 'icon' => 'star'],
        ['route' => 'player.progress', 'is' => 'player.progress', 'label' => 'Progress', 'icon' => 'chart-bar'],
        ['route' => 'player.certificate', 'is' => 'player.certificate*', 'label' => 'Sertifikat', 'icon' => 'certificate'],
        ['route' => 'profile.edit', 'is' => 'profile.edit', 'label' => 'Profil', 'icon' => 'user'],
    ];
@endphp

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 h-screen w-[280px] shrink-0 overflow-hidden text-white transition-transform duration-300 ease-in-out lg:sticky lg:inset-y-auto lg:top-0 lg:left-auto lg:h-screen lg:w-[260px] lg:translate-x-0 xl:w-[280px]"
>
    {{-- Background artwork: harus mengisi seluruh sidebar tanpa celah --}}
    <img
        src="{{ asset('images/brand/logo-sidebar.png') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 h-full w-full select-none object-cover object-center"
    >
    {{-- Overlay gradien agar menu tetap terbaca di atas artwork --}}
    <div
        class="pointer-events-none absolute inset-0"
        style="background: linear-gradient(180deg, rgba(6,36,120,.82), rgba(5,50,160,.74), rgba(3,30,110,.84));"
        aria-hidden="true"
    ></div>

    <div class="relative z-10 flex h-full flex-col overflow-y-auto scrollbar-none px-5 py-6">
        {{-- 1. Logo --}}
        <div class="flex shrink-0 items-center justify-between lg:justify-start">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img
                    src="{{ asset('images/brand/logo-baruuuu.png') }}"
                    alt="Logo Ular Tangga Statistik"
                    class="h-[70px] w-[70px] shrink-0 object-contain drop-shadow-md"
                >
                <span class="leading-tight">
                    <span class="block text-base font-bold">Ular Tangga Statistik</span>
                    <span class="block text-xs text-white/70">Belajar Statistik, Asyik &amp; Seru!</span>
                </span>
            </a>

            <button
                @click="sidebarOpen = false"
                aria-label="Tutup menu"
                class="rounded-lg p-1.5 text-white/80 transition-colors duration-200 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 lg:hidden"
            >
                <x-player.icon name="close" class="h-6 w-6" />
            </button>
        </div>

        {{-- 2. Menu navigasi (mengisi ruang tengah, item ditengahkan vertikal) --}}
        <nav class="flex flex-1 flex-col justify-center gap-1.5 py-6" aria-label="Navigasi utama">
            @foreach ($links as $link)
                @php $active = request()->routeIs($link['is']); @endphp
                <a
                    href="{{ route($link['route']) }}"
                    aria-current="{{ $active ? 'page' : 'false' }}"
                    class="group flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 {{ $active ? 'bg-white text-secondary-600 shadow-soft' : 'text-white/85 hover:translate-x-1 hover:bg-white/10' }}"
                >
                    <x-player.icon :name="$link['icon']" class="h-5 w-5 {{ $active ? 'text-secondary-600' : 'text-white/80 group-hover:text-white' }}" />
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- 3. Tombol Keluar (selalu di bagian paling bawah) --}}
        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-white/85 transition-all duration-300 hover:translate-x-1 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
            >
                <x-player.icon name="logout" class="h-5 w-5" />
                Keluar
            </button>
        </form>
    </div>
</aside>

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
></div>
