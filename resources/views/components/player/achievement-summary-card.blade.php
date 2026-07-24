@props([
    'totalDiraih',
    'totalAchievements',
])

@php
    $percent = $totalAchievements > 0 ? min(100, (int) round($totalDiraih / $totalAchievements * 100)) : 0;
@endphp

<div class="animate-fade-in-up flex flex-col gap-5 rounded-2xl border border-secondary-200 bg-secondary-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
    <div class="flex flex-col items-center gap-5 text-center sm:flex-row sm:text-left">
        <img
            src="{{ asset('images/brand/logo-bintang.png') }}"
            alt=""
            aria-hidden="true"
            class="block h-[110px] w-[110px] min-w-[110px] shrink-0 select-none object-contain transition-all duration-300 ease-out hover:-translate-y-1 hover:scale-105 drop-shadow-[0_10px_24px_rgba(255,193,7,0.35)]"
        >

        <div class="min-w-0">
            <p class="font-bold text-slate-800">
                <span class="text-secondary-600">{{ $totalDiraih }} dari {{ $totalAchievements }}</span> achievement telah diraih
            </p>
            <div class="mx-auto mt-2 h-2.5 w-56 max-w-full overflow-hidden rounded-full bg-white/70 sm:mx-0 sm:w-72">
                <div class="h-2.5 rounded-full bg-secondary-500 transition-all duration-700 ease-out" style="width: {{ $percent }}%"></div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 sm:pl-4">
        <img
            src="{{ asset('images/brand/logo-hadia.png') }}"
            alt=""
            aria-hidden="true"
            class="h-[76px] w-[76px] shrink-0 select-none object-contain drop-shadow-sm animate-float sm:h-20 sm:w-20 lg:h-[88px] lg:w-[88px]"
        >
        <div class="text-right sm:text-left">
            <p class="text-sm font-bold text-slate-800">Terus bermain dan</p>
            <p class="text-sm font-bold text-slate-800">raih semua pencapaian!</p>
        </div>
    </div>
</div>
