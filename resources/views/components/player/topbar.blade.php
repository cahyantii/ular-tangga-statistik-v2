@props(['score' => null])

@php
    $user = auth()->user();
    $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
@endphp

<header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-slate-100 bg-white/80 px-4 py-3 backdrop-blur-md sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="sidebarOpen = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
            <x-player.icon name="menu" class="h-6 w-6" />
        </button>

        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        @if (! is_null($score))
            <div class="hidden items-center gap-2 rounded-full bg-accent-50 px-3.5 py-2 text-accent-600 sm:flex">
                <x-player.icon name="star" class="h-4 w-4" />
                <span class="text-sm font-semibold">{{ $score }} Poin</span>
            </div>
        @endif

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" class="relative rounded-full p-2.5 text-slate-500 transition hover:bg-slate-100">
                <x-player.icon name="bell" class="h-5 w-5" />
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
                class="absolute right-0 z-30 mt-2 w-64 origin-top-right rounded-2xl border border-slate-100 bg-white p-4 text-sm text-slate-500 shadow-soft-lg"
            >
                <p class="font-semibold text-slate-700">Notifikasi</p>
                <p class="mt-1">Belum ada notifikasi baru.</p>
            </div>
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 transition hover:bg-slate-100 sm:pr-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-500 text-sm font-bold text-white">
                    {{ $initial }}
                </span>
                <span class="hidden text-left leading-tight sm:block">
                    <span class="block text-sm font-semibold text-slate-700">{{ $user->name }}</span>
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
                class="absolute right-0 z-30 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white py-2 shadow-soft-lg"
            >
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    <x-player.icon name="user" class="h-4 w-4" /> Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-500 hover:bg-red-50">
                        <x-player.icon name="logout" class="h-4 w-4" /> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
