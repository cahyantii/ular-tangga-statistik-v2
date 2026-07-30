@props(['score' => null])

@php
    $user = auth()->user();
@endphp

<header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-100 bg-white/80 px-4 py-3 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80 sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 {{ request()->routeIs('game.show') ? 'max-lg:hidden' : 'hidden' }}">
            <x-player.icon name="menu" class="h-6 w-6" />
        </button>

        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if (! is_null($score))
            <div class="flex items-center gap-1.5 rounded-full bg-accent-50 px-2.5 py-1.5 text-accent-600 dark:bg-accent-500/10 sm:gap-2 sm:px-3.5 sm:py-2">
                <x-player.icon name="star" class="h-4 w-4 shrink-0" />
                <span class="text-xs font-semibold sm:text-sm">{{ $score }} Poin</span>
            </div>
        @endif

        <button
            type="button"
            onclick="toggleTheme('user-theme')"
            class="rounded-full p-2.5 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
            aria-label="Ganti mode gelap/terang"
        >
            <x-player.icon name="sun" class="hidden h-5 w-5 dark:block" />
            <x-player.icon name="moon" class="block h-5 w-5 dark:hidden" />
        </button>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" class="js-notification-bell relative rounded-full p-2.5 text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                <x-player.icon name="bell" class="h-5 w-5" />
                <span
                    x-show="$store.notifications.unreadCount > 0"
                    x-cloak
                    x-text="$store.notifications.unreadCount > 9 ? '9+' : $store.notifications.unreadCount"
                    class="notif-badge-pulse absolute right-1 top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white"
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
                class="notif-dropdown-glass absolute right-0 z-30 mt-2 w-80 max-w-[90vw] origin-top-right overflow-hidden rounded-2xl border border-slate-100 shadow-soft-lg dark:border-slate-700"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-slate-700">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Notifikasi</p>
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
                        <div class="flex items-start gap-3 border-b border-slate-50 px-4 py-3 last:border-b-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" :class="{ 'bg-primary-50/40 dark:bg-primary-500/10': !notification.read_at }">
                            <span x-init="$store.notifications.mountIcon($el, notification.icon)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300"></span>
                            <a :href="notification.url ?? '#'" @click="$store.notifications.markRead(notification.id)" class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="notification.title"></p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400" x-text="notification.message"></p>
                                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500" x-text="notification.created_at_human"></p>
                            </a>
                            <button type="button" @click="$store.notifications.remove(notification.id)" aria-label="Hapus" class="shrink-0 text-slate-300 hover:text-red-500 dark:text-slate-600">
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
                    <span class="block text-xs text-slate-400 dark:text-slate-500">{{ $user->role->label() }}</span>
                </span>
                <x-player.icon name="chevron-down" class="hidden h-4 w-4 text-slate-400 dark:text-slate-500 sm:block" />
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
                class="absolute right-0 z-30 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white py-2 shadow-soft-lg dark:border-slate-700 dark:bg-slate-800"
            >
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                    <x-player.icon name="user" class="h-4 w-4" /> Profil
                </a>
                <button type="button" @click="open = false; $dispatch('open-modal', 'feedback-modal')"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">
                    <x-player.icon name="mail" class="h-4 w-4" /> Kirim Masukan
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-500 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                        <x-player.icon name="logout" class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
