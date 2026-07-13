@props(['user', 'summary'])

<div class="relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm sm:p-8">
    <div class="relative z-10 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
        <div class="relative shrink-0">
            <x-player.avatar :user="$user" size="h-24 w-24" textSize="text-3xl" />
            <a
                href="#avatar-picker"
                aria-label="Ubah avatar"
                class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-primary-500 text-white shadow-sm transition hover:bg-primary-600"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" />
                </svg>
            </a>
        </div>

        <div class="min-w-0 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                <h2 class="text-xl font-bold text-slate-800">{{ $user->name }}</h2>
                <x-player.badge :label="$summary['level_name']" :color="$summary['badge_color']" />
            </div>

            <p class="mt-1 text-sm text-slate-500">Pemain sejak {{ $user->created_at->translatedFormat('d F Y') }}</p>

            <div class="mt-2.5 flex flex-wrap items-center justify-center gap-4 text-sm sm:justify-start">
                <span class="flex items-center gap-1.5 font-semibold text-accent-600">
                    <x-player.icon name="star" class="h-4 w-4" />
                    Level {{ $summary['level'] }}
                </span>
                <span class="flex items-center gap-1.5 font-semibold text-accent-600">
                    <x-player.icon name="trophy" class="h-4 w-4" />
                    {{ $summary['poin'] }} Poin
                </span>
            </div>
        </div>
    </div>

    <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[420px] select-none lg:block" aria-hidden="true">
        <svg viewBox="0 0 420 160" class="h-full w-full" preserveAspectRatio="xMaxYMid slice">
            <path d="M0 160V100c50-30 100-20 150-5s110 10 160-15 70-10 110 5V160Z" fill="#EAF1FC" />
            <path d="M0 160V120c60-20 120-8 170 5s110 2 160-18 60-6 90 4V160Z" fill="#E5F8EE" />
            <circle cx="130" cy="68" r="18" fill="#EAF1FC" />
            <circle cx="155" cy="62" r="14" fill="#EAF1FC" />
            <circle cx="60" cy="92" r="13" fill="#00A65A" fill-opacity="0.45" />
            <circle cx="90" cy="102" r="9" fill="#00A65A" fill-opacity="0.35" />
            <circle cx="352" cy="112" r="11" fill="#00A65A" fill-opacity="0.35" />
            <g transform="translate(300 58)">
                <rect x="0" y="20" width="40" height="42" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="2" />
                <rect x="-7" y="10" width="12" height="16" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="2" />
                <rect x="35" y="10" width="12" height="16" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="2" />
                <path d="M0 20 20 4 40 20Z" fill="#F68B1F" />
                <rect x="15" y="38" width="10" height="24" fill="#0F4CBA" fill-opacity="0.2" />
                <line x1="0" y1="4" x2="0" y2="14" stroke="#0F4CBA" stroke-width="2" />
                <path d="M0 4h8l-8 6Z" fill="#EF4444" />
            </g>
            <g transform="translate(216 66)">
                <path d="M-8 0h16v8a8 8 0 0 1-16 0V0Z" fill="#FCD34D" stroke="#F68B1F" stroke-width="1.5" />
                <path d="M-8 2h-6v2a6 6 0 0 0 6 6M8 2h6v2a6 6 0 0 1-6 6" stroke="#F68B1F" stroke-width="1.5" fill="none" />
                <rect x="-3" y="16" width="6" height="6" fill="#F68B1F" />
                <path d="M-7 26h14l-2 4h-10Z" fill="#F68B1F" />
            </g>
        </svg>
    </div>
</div>
