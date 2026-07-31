@php
    $earnedItems = $achievements->where('earned', true)->values();
    $inProgressItems = $achievements->where('earned', false)->where('current', '>', 0)->values();
    $lockedItems = $achievements->where('earned', false)->where('current', '<=', 0)->values();
@endphp

<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 sm:text-2xl">Achievement</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Kumpulkan semua pencapaian dan jadi master statistik! &#127942;</p>
    </x-slot>

    <div class="space-y-6">
        <x-player.achievement-summary-card :totalDiraih="$totalDiraih" :totalAchievements="$totalAchievements" />

        @if ($earnedItems->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($earnedItems as $item)
                    <x-player.achievement-card
                        :achievement="$item['achievement']"
                        :earned="$item['earned']"
                        :earnedAt="$item['earned_at']"
                        :current="$item['current']"
                        :target="$item['target']"
                        :percent="$item['percent']"
                    />
                @endforeach
            </div>
        @endif

        @if ($inProgressItems->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                @foreach ($inProgressItems as $item)
                    <x-player.achievement-card
                        :achievement="$item['achievement']"
                        :earned="$item['earned']"
                        :earnedAt="$item['earned_at']"
                        :current="$item['current']"
                        :target="$item['target']"
                        :percent="$item['percent']"
                    />
                @endforeach
            </div>
        @endif

        @if ($lockedItems->isNotEmpty())
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lockedItems as $item)
                    <x-player.achievement-card
                        :achievement="$item['achievement']"
                        :earned="$item['earned']"
                        :earnedAt="$item['earned_at']"
                        :current="$item['current']"
                        :target="$item['target']"
                        :percent="$item['percent']"
                    />
                @endforeach
            </div>
        @endif

        <div class="achievement-tips-banner flex flex-col gap-4 rounded-2xl border p-4 text-sm sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:p-5">
            <p class="text-primary-700 sm:flex-1">
                <span class="font-bold">Tips:</span>
                Semakin banyak achievement yang kamu raih, semakin banyak poin yang bisa kamu kumpulkan!
            </p>

            <img
                src="{{ asset('images/brand/logo-buku.png') }}"
                alt=""
                aria-hidden="true"
                class="h-auto w-[90px] shrink-0 select-none self-center object-contain transition duration-300 hover:-translate-y-1 hover:scale-[1.03] sm:w-[100px] sm:self-auto lg:w-[110px]"
            >
        </div>
    </div>
</x-player-layout>
