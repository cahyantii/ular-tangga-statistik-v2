@php
    $papan = $gameSession->papan;
    $boardConfig = [
        'jumlah_kolom' => $papan->jumlah_kolom,
        'jumlah_petak' => $papan->jumlah_petak,
        'petak' => $papan->petak->map(fn ($p) => [
            'id' => $p->id,
            'posisi' => $p->posisi,
            'jenis_petak' => $p->jenis_petak->value,
            'label' => $p->label,
        ])->values(),
        'konektor' => $papan->papanKonektor->map(fn ($k) => [
            'posisi_awal' => $k->posisi_awal,
            'posisi_akhir' => $k->posisi_akhir,
        ])->values(),
    ];
@endphp

<x-player-layout>
    @push('scripts')
        @vite(['resources/js/game-play.js'])
    @endpush

    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Permainan #{{ $gameSession->id }} &mdash; {{ $papan->nama }}</h2>
    </x-slot>

    @if ($gameSession->status->value === 'playing')
        <div id="game-in-progress"></div>
    @endif

    <div id="game-toast" class="hidden mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></div>
    <div id="game-finished-banner" class="hidden mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-lg font-semibold text-emerald-800"></div>

    {{-- Lawan terputus koneksi (Multiplayer, Tahap 12a/4): countdown 60 detik masa tenggang reconnect. --}}
    <div id="game-paused-banner" class="hidden mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-center text-sm text-amber-800">
        Lawan terputus koneksi. Permainan dijeda &mdash; menunggu reconnect
        <span id="game-paused-timer" class="font-semibold"></span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <p id="turn-indicator" class="text-lg font-semibold text-slate-800"></p>
                <div class="flex items-center gap-3">
                    <span id="dice-value" class="hidden inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-lg font-bold text-white"></span>
                    <button id="roll-dice-button" type="button" class="hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        Lempar Dadu
                    </button>
                </div>
            </div>

            <div id="board-grid" class="rounded-2xl border border-slate-200 bg-white p-4"></div>
        </div>

        <div class="space-y-4">
            <div id="player-list" class="space-y-3"></div>

            <button id="leave-game-button" type="button" class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                Keluar dari Permainan
            </button>
        </div>
    </div>

    {{-- Modal Soal --}}
    <div id="question-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white p-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">Soal</h3>
                <span id="question-timer" class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600"></span>
            </div>
            <p id="question-text" class="mb-4 text-slate-700"></p>
            <div id="question-options" class="space-y-2"></div>
            <p id="question-feedback" class="hidden mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-700"></p>
        </div>
    </div>

    <div id="game-play-data"
         data-board="{{ json_encode($boardConfig) }}"
         data-state-url="{{ route('game.state', $gameSession) }}"
         data-roll-url="{{ route('game.roll', $gameSession) }}"
         data-answer-url="{{ route('game.answer', $gameSession) }}"
         data-leave-url="{{ route('game.leave', $gameSession) }}"
         data-heartbeat-url="{{ route('game.heartbeat', $gameSession) }}"
         data-current-user-id="{{ auth()->id() }}"></div>
</x-player-layout>
