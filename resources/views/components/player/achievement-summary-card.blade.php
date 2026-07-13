@props([
    'totalDiraih',
    'totalAchievements',
])

@php
    $percent = $totalAchievements > 0 ? min(100, (int) round($totalDiraih / $totalAchievements * 100)) : 0;
@endphp

<div class="animate-fade-in-up flex flex-col gap-5 rounded-2xl border border-secondary-200 bg-secondary-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
    <div class="flex items-center gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-accent-500 text-white shadow-sm">
            <x-player.icon name="star" class="h-7 w-7" />
        </span>

        <div class="min-w-0">
            <p class="font-bold text-slate-800">
                <span class="text-secondary-600">{{ $totalDiraih }} dari {{ $totalAchievements }}</span> achievement telah diraih
            </p>
            <div class="mt-2 h-2.5 w-56 max-w-full overflow-hidden rounded-full bg-white/70 sm:w-72">
                <div class="h-2.5 rounded-full bg-secondary-500 transition-all duration-700 ease-out" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 sm:pl-4">
        <svg viewBox="0 0 64 64" class="h-14 w-14 shrink-0 drop-shadow-sm" aria-hidden="true">
            <rect x="10" y="26" width="44" height="30" rx="4" fill="#7C3AED" />
            <rect x="10" y="26" width="44" height="10" fill="#8B5CF6" />
            <rect x="28" y="26" width="8" height="30" fill="#F59E0B" />
            <path d="M32 26c-6 0-10-4-10-8a6 6 0 0 1 10-4 6 6 0 0 1 10 4c0 4-4 8-10 8Z" fill="#FBBF24" />
            <path d="M22 16a5 5 0 0 1 10 2" stroke="#F59E0B" stroke-width="1.5" fill="none" stroke-linecap="round" />
        </svg>
        <div class="text-right sm:text-left">
            <p class="text-sm font-bold text-slate-800">Terus bermain dan</p>
            <p class="text-sm font-bold text-slate-800">raih semua pencapaian!</p>
        </div>
    </div>
</div>
