<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Achievement</h2>
    </x-slot>

    <p class="mb-6 text-sm text-slate-500">{{ $totalDiraih }} dari {{ $achievements->count() }} achievement telah diraih.</p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($achievements as $item)
            @php $achievement = $item['achievement']; @endphp
            <x-player.achievement-card
                :nama="$achievement->nama"
                :deskripsi="$achievement->deskripsi"
                :earned="$item['earned']"
                :earnedAt="$item['earned_at']"
            />
        @endforeach
    </div>
</x-player-layout>
