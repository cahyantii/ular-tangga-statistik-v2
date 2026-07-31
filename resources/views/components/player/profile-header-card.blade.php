@props(['user', 'summary'])

<div class="relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm dark:bg-slate-800 sm:p-8">
    <div
        class="pointer-events-none absolute inset-0 select-none bg-cover bg-right"
        style="background-image: url('{{ asset('images/brand/logo-back.png') }}');"
        aria-hidden="true"
    ></div>

    {{-- Mobile: konten stack & center, jadi overlay memusat dari atas supaya teks tetap terbaca --}}
    <div
        class="pointer-events-none absolute inset-0 block sm:hidden"
        style="background: linear-gradient(180deg, rgba(255,255,255,0.94) 0%, rgba(255,255,255,0.9) 55%, rgba(255,255,255,0.8) 100%);"
        x-bind:style="$store.playerTheme.isDark
            ? 'background: linear-gradient(180deg, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.9) 55%, rgba(15,23,42,0.8) 100%);'
            : 'background: linear-gradient(180deg, rgba(255,255,255,0.94) 0%, rgba(255,255,255,0.9) 55%, rgba(255,255,255,0.8) 100%);'"
        aria-hidden="true"
    ></div>

    {{-- Tablet & desktop: konten rata kiri, jadi overlay memudar dari kiri ke kanan --}}
    <div
        class="pointer-events-none absolute inset-0 hidden sm:block"
        style="background: linear-gradient(90deg, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.78) 35%, rgba(255,255,255,0.35) 70%, rgba(255,255,255,0.10) 100%);"
        x-bind:style="$store.playerTheme.isDark
            ? 'background: linear-gradient(90deg, rgba(15,23,42,0.95) 0%, rgba(15,23,42,0.82) 35%, rgba(15,23,42,0.45) 70%, rgba(15,23,42,0.15) 100%);'
            : 'background: linear-gradient(90deg, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.78) 35%, rgba(255,255,255,0.35) 70%, rgba(255,255,255,0.10) 100%);'"
        aria-hidden="true"
    ></div>

    <div class="relative z-10 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
        <div class="relative shrink-0">
            {{-- Soft blue glow behind the avatar frame --}}
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -m-2 rounded-full bg-primary-400/10 blur-md"></div>

            <div class="relative h-[110px] w-[110px] rounded-full bg-blue-50 p-1.5 shadow-md ring-1 ring-primary-400/20 transition-shadow duration-300 hover:shadow-lg dark:bg-slate-700 md:h-[130px] md:w-[130px] lg:h-[140px] lg:w-[140px]">
                <div class="h-full w-full overflow-hidden rounded-full border-4 border-white dark:border-slate-800">
                    <x-player.avatar :user="$user" size="h-full w-full" textSize="text-3xl" class="scale-125" />
                </div>
            </div>
            <a
                href="#avatar-picker"
                aria-label="Ubah avatar"
                class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-primary-500 text-white shadow-sm transition hover:bg-primary-600 dark:border-slate-800"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" />
                </svg>
            </a>
        </div>

        <div class="min-w-0 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $user->name }}</h2>
                <x-player.badge :label="$summary['level_name']" :color="$summary['badge_color']" />
            </div>

            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pemain sejak {{ $user->created_at->translatedFormat('d F Y') }}</p>

            <div class="mt-2.5 flex flex-wrap items-center justify-center gap-4 text-sm sm:justify-start">
                <span class="flex items-center gap-1.5 font-semibold text-accent-600 dark:text-amber-400">
                    <x-player.icon name="star" class="h-4 w-4" />
                    Level {{ $summary['level'] }}
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-accent-600 dark:text-amber-400">
                    <x-player.icon name="trophy" class="h-4 w-4" />
                    {{ $summary['poin'] }} Poin
                </span>
            </div>
        </div>
    </div>
</div>
