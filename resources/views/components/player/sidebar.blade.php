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
    class="fixed inset-y-0 left-0 z-40 flex h-screen w-[280px] shrink-0 flex-col overflow-y-auto scrollbar-none bg-gradient-to-b from-primary-500 via-primary-600 to-primary-700 px-5 py-6 text-white transition-transform duration-300 ease-in-out lg:sticky lg:inset-y-auto lg:top-0 lg:left-auto lg:h-screen lg:translate-x-0"
>
    {{-- 1. Logo --}}
    <div class="flex shrink-0 items-center justify-between lg:justify-start">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img
                src="{{ asset('images/brand/logo-baruuuu.png') }}"
                alt="Logo Ular Tangga Statistik"
                class="h-14 w-14 shrink-0 object-contain drop-shadow-md"
            >
            <span class="leading-tight">
                <span class="block text-base font-bold">Ular Tangga Statistik</span>
                <span class="block text-xs text-white/70">Belajar Statistik, Asyik &amp; Seru!</span>
            </span>
        </a>

        <button @click="sidebarOpen = false" class="rounded-lg p-1.5 text-white/80 hover:bg-white/10 lg:hidden">
            <x-player.icon name="close" class="h-6 w-6" />
        </button>
    </div>

    {{-- 2. Menu navigasi (mengisi ruang tengah, item ditengahkan vertikal) --}}
    <nav class="flex flex-1 flex-col justify-center gap-1.5 py-6">
        @foreach ($links as $link)
            @php $active = request()->routeIs($link['is']); @endphp
            <a
                href="{{ route($link['route']) }}"
                class="group flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200 {{ $active ? 'bg-white text-secondary-600 shadow-soft' : 'text-white/85 hover:translate-x-1 hover:bg-white/10' }}"
            >
                <x-player.icon :name="$link['icon']" class="h-5 w-5 {{ $active ? 'text-secondary-600' : 'text-white/80 group-hover:text-white' }}" />
                {{ $link['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- 3. Ilustrasi ular & tangga --}}
    <div class="shrink-0 select-none opacity-90" aria-hidden="true">
        <svg viewBox="0 0 240 120" class="w-full" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 95c40-25 60 20 100-5s70 15 100-10" stroke="#FFFFFF" stroke-opacity="0.18" stroke-width="3" stroke-linecap="round" />
            <g transform="translate(140 20)">
                <rect x="0" y="0" width="10" height="70" rx="3" fill="#FFFFFF" fill-opacity="0.25" />
                <rect x="34" y="0" width="10" height="70" rx="3" fill="#FFFFFF" fill-opacity="0.25" />
                <rect x="0" y="12" width="44" height="6" rx="2" fill="#FFFFFF" fill-opacity="0.25" />
                <rect x="0" y="32" width="44" height="6" rx="2" fill="#FFFFFF" fill-opacity="0.25" />
                <rect x="0" y="52" width="44" height="6" rx="2" fill="#FFFFFF" fill-opacity="0.25" />
            </g>
            <g class="animate-float" transform="translate(15 55)">
                <path d="M0 30c0-16 26-16 26-32 0-8-8-10-14-6" stroke="#F68B1F" stroke-width="7" stroke-linecap="round" fill="none" />
                <circle cx="14" cy="-9" r="4.5" fill="#F68B1F" />
                <circle cx="12.7" cy="-10.3" r="0.9" fill="#0A317A" />
                <path d="M9 -6.5c1.2 1.4 3.2 1.4 4.4 0" stroke="#0A317A" stroke-width="1" stroke-linecap="round" fill="none" />
            </g>
            <g transform="translate(190 62)">
                <rect x="0" y="0" width="18" height="18" rx="4" fill="#FFFFFF" fill-opacity="0.9" transform="rotate(12 9 9)" />
                <circle cx="6" cy="6" r="1.4" fill="#0F4CBA" transform="rotate(12 9 9)" />
                <circle cx="12" cy="12" r="1.4" fill="#0F4CBA" transform="rotate(12 9 9)" />
                <rect x="16" y="10" width="16" height="16" rx="4" fill="#FFFFFF" fill-opacity="0.7" transform="rotate(-8 24 18)" />
                <circle cx="20" cy="14" r="1.3" fill="#0F4CBA" transform="rotate(-8 24 18)" />
                <circle cx="24" cy="18" r="1.3" fill="#0F4CBA" transform="rotate(-8 24 18)" />
                <circle cx="28" cy="22" r="1.3" fill="#0F4CBA" transform="rotate(-8 24 18)" />
            </g>
        </svg>
    </div>

    {{-- 4. Tombol Keluar (selalu di bagian paling bawah) --}}
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
        @csrf
        <button
            type="submit"
            class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-white/85 transition-all duration-200 hover:bg-white/10 hover:translate-x-1"
        >
            <x-player.icon name="logout" class="h-5 w-5" />
            Keluar
        </button>
    </form>
</aside>

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
></div>
