@php
    $earnedItems = $achievements->where('earned', true)->values();
    $inProgressItems = $achievements->where('earned', false)->where('current', '>', 0)->values();
    $lockedItems = $achievements->where('earned', false)->where('current', '<=', 0)->values();
@endphp

<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Achievement</h1>
        <p class="mt-0.5 text-sm text-slate-500">Kumpulkan semua pencapaian dan jadi master statistik! &#127942;</p>
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

        <div class="flex items-center gap-3 rounded-2xl border border-primary-100 bg-primary-50 p-4 text-sm sm:p-5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-white">
                <x-player.icon name="help" class="h-5 w-5" />
            </span>
            <p class="text-primary-700">
                <span class="font-bold">Tips:</span>
                Semakin banyak achievement yang kamu raih, semakin banyak poin yang bisa kamu kumpulkan!
            </p>
        </div>
    </div>
</x-player-layout>
