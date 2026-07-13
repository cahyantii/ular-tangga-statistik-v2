@php
    $papan = $gameSession->papan;
@endphp

<x-player-layout>
    @push('scripts')
        @vite(['resources/js/game-play.js'])
    @endpush

    <x-slot name="header">
        <x-game.header :gameSession="$gameSession" :papan="$papan" />
    </x-slot>

    @if ($gameSession->status->value === 'playing')
        <div id="game-in-progress"></div>
    @endif

    {{-- Toast notifikasi ringan --}}
    <div id="game-toast" class="fixed left-1/2 top-20 z-50 hidden -translate-x-1/2 animate-fade-in-up rounded-xl border border-accent-200 bg-white px-4 py-3 text-sm font-medium text-accent-700 shadow-soft-lg"></div>

    {{-- Lawan terputus koneksi (Multiplayer): countdown masa tenggang reconnect --}}
    <div id="game-paused-banner" class="mb-4 hidden items-center gap-2 rounded-2xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-700">
        <x-player.icon name="clock" class="h-4 w-4 shrink-0" />
        Lawan terputus koneksi. Permainan dijeda &mdash; menunggu reconnect
        <span id="game-paused-timer" class="font-bold"></span>
    </div>

    <x-game.board-stats :papan="$papan" class="mb-6" />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Kolom kiri/tengah: giliran, dadu, papan, tips --}}
        <div class="space-y-5 xl:col-span-2">
            <div class="animate-fade-in-up rounded-3xl bg-white p-4 shadow-sm sm:p-6">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <span id="turn-avatar" class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-500 text-sm font-bold text-white"></span>
                        <div>
                            <p id="turn-indicator" class="text-base font-bold text-slate-800 sm:text-lg"></p>
                            <p id="turn-subtext" class="text-xs text-slate-400 sm:text-sm"></p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-2">
                        <div id="dice-glow-wrap" class="rounded-full p-1 transition-all duration-300">
                            <x-game.dice id="dice-3d" />
                        </div>
                        <button
                            id="roll-dice-button"
                            type="button"
                            class="hidden items-center gap-2 rounded-xl bg-secondary-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-secondary-600 hover:shadow-soft disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                        >
                            <x-player.icon name="dice" class="h-4 w-4" />
                            Lempar Dadu
                        </button>
                        <p class="text-[11px] text-slate-400">Klik untuk melempar dadu</p>
                    </div>
                </div>
            </div>

            <div class="animate-fade-in-up rounded-3xl bg-white p-3 shadow-sm sm:p-5">
                <x-game.board :papan="$papan" />
            </div>

            <x-game.tips-card :papan="$papan" />
        </div>

        {{-- Kolom kanan: panel pemain/robot, progress, log --}}
        <div class="space-y-5">
            <div id="player-panel-list" class="space-y-3"></div>

            <x-game.progress-card />

            <x-game.log-card />

            <button
                id="leave-game-button"
                type="button"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition-all duration-200 hover:bg-rose-50"
            >
                Keluar dari Permainan
            </button>
        </div>
    </div>

    {{-- Template kartu (di-clone JS, lihat komentar di masing-masing file komponen) --}}
    <x-game.player-card />
    <x-game.robot-card />

    {{-- Modal Soal --}}
    <x-player.modal name="question-modal">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                <x-player.icon name="book" class="h-5 w-5 text-primary-500" />
                Soal
            </h3>
            <span id="question-timer" class="rounded-full bg-primary-50 px-3 py-1 text-sm font-bold text-primary-600"></span>
        </div>
        <p id="question-text" class="mb-4 text-sm leading-relaxed text-slate-700"></p>
        <div id="question-options" class="space-y-2"></div>
        <p id="question-feedback" class="mt-4 hidden rounded-xl bg-slate-50 p-3 text-sm text-slate-700"></p>
    </x-player.modal>

    {{-- Modal Hasil Akhir --}}
    <x-player.modal name="game-finished-modal">
        <div class="text-center">
            <span id="finished-icon" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-accent-500 text-white shadow-soft">
                <x-player.icon name="trophy" class="h-8 w-8" />
            </span>
            <p id="finished-title" class="mt-4 text-xl font-bold text-slate-800"></p>
            <p id="finished-subtitle" class="mt-1 text-sm text-slate-500"></p>
            <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                Kembali ke Dashboard
            </a>
        </div>
    </x-player.modal>

    <div id="game-play-data"
         data-state-url="{{ route('game.state', $gameSession) }}"
         data-roll-url="{{ route('game.roll', $gameSession) }}"
         data-answer-url="{{ route('game.answer', $gameSession) }}"
         data-leave-url="{{ route('game.leave', $gameSession) }}"
         data-heartbeat-url="{{ route('game.heartbeat', $gameSession) }}"
         data-current-user-id="{{ auth()->id() }}"
         data-jumlah-petak="{{ $papan->jumlah_petak }}"></div>
</x-player-layout>
