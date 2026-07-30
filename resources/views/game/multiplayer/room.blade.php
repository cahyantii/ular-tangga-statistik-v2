@php
    $isCreator = auth()->id() === $room->created_by;
    $pemain = $room->gameSession?->players ?? collect();
    $jumlahPemain = $room->jumlah_pemain;
    $sisaSlot = max(0, $jumlahPemain - $pemain->count());
@endphp

<x-player-layout>
    @push('scripts')
        @vite(['resources/js/room-realtime.js'])
    @endpush

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Waiting Room</h2>
    </x-slot>

    <div class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-700 dark:bg-slate-800">
        @if ($room->tipe->value === 'private')
            <p class="text-sm text-slate-500 dark:text-slate-400">Bagikan kode ini ke teman Anda:</p>
            <div class="mt-2 flex items-center justify-center gap-2">
                <span id="kode-room" class="rounded-lg bg-slate-100 px-4 py-2 text-2xl font-bold tracking-[0.3em] text-slate-800 dark:bg-slate-700 dark:text-slate-100">{{ $room->kode_room }}</span>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $room->kode_room }}')"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                    Salin
                </button>
            </div>
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400">Quick Match</p>
        @endif

        <p class="mt-6 text-lg font-semibold text-emerald-700 dark:text-emerald-400">
            {{ $pemain->count() }}/{{ $jumlahPemain }} pemain &mdash;
            @if ($sisaSlot > 0)
                menunggu {{ $sisaSlot }} pemain lagi&hellip;
            @else
                penuh, memulai permainan&hellip;
            @endif
        </p>

        <div class="mt-4 space-y-2 text-left">
            @foreach ($pemain as $p)
                <div class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-300">
                    {{ $p->user?->name ?? 'Pemain' }}
                </div>
            @endforeach
            @for ($i = 0; $i < $sisaSlot; $i++)
                <div class="rounded-lg border border-dashed border-slate-200 px-4 py-2 text-sm text-slate-400 dark:border-slate-700 dark:text-slate-500">
                    Menunggu pemain&hellip;
                </div>
            @endfor
        </div>

        <a href="{{ route('game.room.show', $room) }}"
           class="mt-6 inline-block rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
            Segarkan Status
        </a>

        @if ($isCreator)
            <form method="POST" action="{{ route('game.room.cancel', $room) }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
                    Batalkan Room
                </button>
            </form>
        @endif
    </div>

    <div id="room-realtime-data"
         data-room-id="{{ $room->id }}"
         data-room-url="{{ route('game.room.show', $room) }}"></div>
</x-player-layout>
