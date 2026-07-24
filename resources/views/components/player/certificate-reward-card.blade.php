@props(['rewardPoin'])

<div class="cert-card cert-card--reward animate-fade-in-up bg-gradient-to-br from-primary-600 to-violet-700 p-5 text-white sm:p-6 lg:p-7">
    <h3 class="flex items-center gap-2 text-base font-bold">
        <x-player.icon name="trophy" class="h-5 w-5 text-accent-400" />
        Hadiah Sertifikat
    </h3>

    <svg viewBox="0 0 160 120" class="mx-auto mt-4 w-32 select-none drop-shadow-lg" aria-hidden="true">
        <ellipse cx="80" cy="108" rx="46" ry="8" fill="#000000" fill-opacity="0.15" />
        <rect x="34" y="78" width="92" height="26" rx="4" fill="#0D3F9C" />
        <rect x="46" y="24" width="68" height="58" rx="6" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="2" />
        <rect x="54" y="34" width="52" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.3" />
        <rect x="54" y="44" width="36" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.2" />
        <rect x="54" y="52" width="42" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.2" />
        <circle cx="80" cy="66" r="11" fill="#F68B1F" />
        <path d="M75 66l4 4 7-8" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
        <path d="M62 10 66 20 76 22 66 24 62 34 58 24 48 22 58 20Z" fill="#FCD34D" />
        <circle cx="112" cy="16" r="3" fill="#FCD34D" />
        <circle cx="120" cy="30" r="2" fill="#FCD34D" />
    </svg>

    <div class="mt-4 space-y-3">
        <div class="flex items-start gap-2.5">
            <x-player.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-secondary-400" />
            <div class="min-w-0">
                <p class="text-sm font-bold leading-tight">E-Sertifikat Digital</p>
                <p class="text-xs text-white/70">Penguasaan Statistik Dasar</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2">
            <x-player.icon name="coin" class="h-4 w-4 text-accent-400" />
            <span class="text-sm font-bold">+{{ $rewardPoin }} Poin</span>
        </div>
    </div>
</div>
