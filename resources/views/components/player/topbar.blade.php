@props(['score' => null])

@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-100 bg-white/80 px-4 py-3 backdrop-blur-md dark:border-slate-700/50 dark:bg-slate-900/85 sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 hidden lg:block">
            <x-player.icon name="menu" class="h-6 w-6" />
        </button>

        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if (! is_null($score))
            <div class="flex items-center gap-1.5 rounded-full bg-accent-50 px-2.5 py-1.5 text-accent-600 dark:bg-amber-900/30 dark:text-amber-300 sm:gap-2 sm:px-3.5 sm:py-2">
                <x-player.icon name="star" class="h-4 w-4 shrink-0" />
                <span class="text-xs font-semibold sm:text-sm">{{ $score }} Poin</span>
            </div>
        @endif

        {{-- Toggle mode gelap/terang player --}}
        <button
            type="button"
            @click="$store.playerTheme.toggle()"
            :aria-label="$store.playerTheme.isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
            :title="$store.playerTheme.isDark ? 'Mode Terang' : 'Mode Gelap'"
            class="theme-toggle-btn text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
        >
            {{-- Ikon bulan: tampil saat mode terang (klik → gelap) --}}
            <svg x-show="!$store.playerTheme.isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
            </svg>
            {{-- Ikon matahari: tampil saat mode gelap (klik → terang) --}}
            <svg x-show="$store.playerTheme.isDark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="5" />
                <path stroke-linecap="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
            </svg>
        </button>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" class="js-notification-bell relative rounded-full p-2.5 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                <x-player.icon name="bell" class="h-5 w-5" />
                <span
                    x-show="$store.notifications.unreadCount > 0"
                    x-cloak
                    x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"
                    class="notif-badge-pulse absolute right-1 top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900"
                ></span>
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="notif-dropdown-glass absolute right-0 z-30 mt-2 w-80 max-w-[90vw] origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-soft-lg dark:border-slate-700/50 dark:bg-slate-800"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-700/50">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-100">Notifikasi</p>
                    <button type="button" @click="$store.notifications.markAllRead()" class="text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400">
                        Tandai Semua Dibaca
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto text-sm text-slate-500 dark:text-slate-400">
                    <template x-if="$store.notifications.loading">
                        <div class="space-y-2 p-4">
                            <div class="notif-skeleton-line h-10 rounded-lg"></div>
                            <div class="notif-skeleton-line h-10 rounded-lg"></div>
                        </div>
                    </template>

                    <template x-if="!$store.notifications.loading && $store.notifications.items.length === 0">
                        <p class="p-6 text-center text-slate-400 dark:text-slate-500">Belum ada notifikasi baru.</p>
                    </template>

                    <template x-for="notification in $store.notifications.items" :key="notification.id">
                        <div class="flex items-start gap-3 border-b border-slate-50 px-4 py-3 last:border-b-0 hover:bg-slate-50 dark:border-slate-700/40 dark:hover:bg-slate-700/50" :class="{ 'bg-primary-50/40 dark:bg-primary-950/40': !notification.read_at }">
                            <span x-init="$store.notifications.mountIcon($el, notification.icon)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300"></span>
                            <a :href="notification.url ?? '#'" @click="$store.notifications.markRead(notification.id)" class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="notification.title"></p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400" x-text="notification.message"></p>
                                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500" x-text="notification.created_at_human"></p>
                            </a>
                            <button type="button" @click="$store.notifications.remove(notification.id)" aria-label="Hapus" class="shrink-0 text-slate-300 hover:text-red-500 dark:text-slate-500">
                                <x-player.icon name="close" class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-slate-100 dark:hover:bg-slate-800 sm:pr-3">
                <x-player.avatar :user="$user" />
                <span class="hidden text-left leading-tight sm:block">
                    <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $user->name }}</span>
                    <span class="block text-xs text-slate-400">{{ $user->role->label() }}</span>
                </span>
                <x-player.icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 sm:block" />
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 z-30 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white py-2 shadow-soft-lg dark:border-slate-700/50 dark:bg-slate-800"
            >
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/60">
                    <x-player.icon name="user" class="h-4 w-4" /> Profil
                </a>
                <button type="button" @click="open = false; $dispatch('open-modal', 'feedback-modal')"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/60">
                    <x-player.icon name="mail" class="h-4 w-4" /> Kirim Masukan
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <x-player.icon name="logout" class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
