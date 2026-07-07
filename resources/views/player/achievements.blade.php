<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Achievement</h2>
    </x-slot>

    <p class="mb-6 text-sm text-slate-500">{{ $totalDiraih }} dari {{ $achievements->count() }} achievement telah diraih.</p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($achievements as $item)
            @php $achievement = $item['achievement']; @endphp
            <div class="rounded-2xl border p-4 {{ $item['earned'] ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-white opacity-70' }}">
                <div class="flex items-start justify-between">
                    <p class="text-base font-bold text-slate-800">{{ $achievement->nama }}</p>
                    @if ($item['earned'])
                        <span class="rounded-full bg-emerald-600 px-2 py-0.5 text-xs font-medium text-white">Diraih</span>
                    @else
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Belum</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $achievement->deskripsi }}</p>
                @if ($item['earned'] && $item['earned_at'])
                    <p class="mt-2 text-xs text-emerald-700">Diraih pada {{ $item['earned_at']->translatedFormat('d F Y') }}</p>
                @endif
            </div>
        @endforeach
    </div>
</x-player-layout>
