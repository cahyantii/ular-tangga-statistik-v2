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
        <h2 class="text-xl font-semibold text-slate-800 dark:text-dark-text">Waiting Room</h2>
    </x-slot>

    <div class="mx-auto max-w-lg rounded-2xl border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface p-6 text-center shadow-sm">
        @if ($room->tipe->value === 'private')
            <p class="text-sm text-slate-500 dark:text-dark-muted">Bagikan kode ini ke teman Anda:</p>
            <div class="mt-2 flex items-center justify-center gap-2">
                <span id="kode-room" class="rounded-lg bg-slate-100 dark:bg-dark-surface2 px-4 py-2 text-2xl font-bold tracking-[0.3em] text-slate-800 dark:text-dark-text">{{ $room->kode_room }}</span>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $room->kode_room }}')"
                        class="rounded-lg border border-slate-300 dark:border-dark-border px-3 py-2 text-xs font-medium text-slate-600 dark:text-dark-muted hover:bg-slate-50 dark:hover:bg-dark-surface2 transition-colors">
                    Salin
                </button>
            </div>
        @else
            <p class="text-sm text-slate-500 dark:text-dark-muted">Quick Match</p>
        @endif

        <p id="room-status-text" class="mt-6 text-lg font-semibold text-emerald-700 dark:text-emerald-400">
            {{ $pemain->count() }}/{{ $jumlahPemain }} pemain &mdash;
            @if ($sisaSlot > 0)
                menunggu {{ $sisaSlot }} pemain lagi&hellip;
            @else
                penuh, memulai permainan&hellip;
            @endif
        </p>

        <div id="room-players-list" class="mt-4 space-y-2 text-left">
            @foreach ($pemain as $p)
                <div class="rounded-lg border border-slate-200 dark:border-dark-border bg-slate-50/50 dark:bg-dark-surface2 px-4 py-2 text-sm text-slate-700 dark:text-dark-text flex items-center justify-between">
                    <span>{{ $p->user?->name ?? 'Pemain' }}</span>
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
            @endforeach
            @for ($i = 0; $i < $sisaSlot; $i++)
                <div class="rounded-lg border border-dashed border-slate-200 dark:border-dark-border px-4 py-2 text-sm text-slate-400 dark:text-dark-muted">
                    Menunggu pemain&hellip;
                </div>
            @endfor
        </div>

        <a href="{{ route('game.room.show', $room) }}"
           class="mt-6 inline-block rounded-lg border border-slate-300 dark:border-dark-border px-4 py-2 text-sm font-medium text-slate-700 dark:text-dark-text hover:bg-slate-50 dark:hover:bg-dark-surface2 transition-colors">
            Segarkan Status
        </a>

        @if ($isCreator)
            <form method="POST" action="{{ route('game.room.cancel', $room) }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-red-200 dark:border-red-900/50 px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                    Batalkan Room
                </button>
            </form>
        @endif
    </div>

    <div id="room-realtime-data"
         data-room-id="{{ $room->id }}"
         data-room-url="{{ route('game.room.show', $room) }}"
         data-status-url="{{ route('game.room.status', $room) }}"></div>
</x-player-layout>
