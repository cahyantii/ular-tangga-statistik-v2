{{--
    FAB navigasi global (di luar halaman gameplay — lihat pengecualian
    `game.show` di layouts/player.blade.php, karena halaman itu sudah punya
    FAB aksi permainannya sendiri dengan 5 tombol berbeda, lihat
    resources/views/game/show.blade.php #mobile-action-fab).

    Breakpoint sengaja `lg:hidden` (BUKAN `xl:hidden` seperti FAB dalam-game)
    supaya konsisten dengan kapan sidebar sendiri berubah dari overlay
    hamburger jadi sidebar tetap (lihat components/player/sidebar.blade.php:
    `lg:translate-x-0`/`lg:sticky`).

    5 dari 6 menu sidebar dipilih (Sertifikat sengaja tidak masuk - jarang
    dicek dibanding yang lain, tetap bisa diakses lewat menu hamburger),
    Dashboard di tengah & lebih besar karena itu juga titik mulai bermain
    (kartu Vs Robot/Multiplayer ada di situ) - peran serupa tombol Dadu di
    FAB dalam-game.
--}}
@php
    $left = [
        ['route' => 'player.achievements', 'is' => 'player.achievements', 'label' => 'Achievement', 'icon' => 'star'],
        ['route' => 'leaderboard', 'is' => 'leaderboard', 'label' => 'Leaderboard', 'icon' => 'trophy'],
    ];
    $right = [
        ['route' => 'player.progress', 'is' => 'player.progress', 'label' => 'Progress', 'icon' => 'chart-bar'],
        ['route' => 'profile.edit', 'is' => 'profile.edit', 'label' => 'Profil', 'icon' => 'user'],
    ];
    $dashboardActive = request()->routeIs('dashboard');
@endphp

<div class="fixed inset-x-3 bottom-3 z-40 mx-auto flex max-w-sm items-end justify-between gap-1 rounded-[28px] bg-white/95 p-2 shadow-soft-lg backdrop-blur-md lg:hidden dark:bg-slate-800/95">
    @foreach ($left as $link)
        @php $active = request()->routeIs($link['is']); @endphp
        <a href="{{ route($link['route']) }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 transition-colors duration-150 {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700' }}">
            <x-player.icon :name="$link['icon']" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">{{ $link['label'] }}</span>
        </a>
    @endforeach

    <a
        href="{{ route('dashboard') }}"
        class="-mt-6 flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-full text-white shadow-lg shadow-primary-900/30 transition-all duration-200 active:scale-95 {{ $dashboardActive ? 'bg-primary-600' : 'bg-primary-500' }}"
    >
        <x-player.icon name="home" class="h-7 w-7" />
    </a>

    @foreach ($right as $link)
        @php $active = request()->routeIs($link['is']); @endphp
        <a href="{{ route($link['route']) }}" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 transition-colors duration-150 {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700' }}">
            <x-player.icon :name="$link['icon']" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">{{ $link['label'] }}</span>
        </a>
    @endforeach
</div>
