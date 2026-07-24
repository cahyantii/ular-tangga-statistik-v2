@php
    $papan = $gameSession->papan;
@endphp

<x-player-layout>
    @push('scripts')
        @vite(['resources/js/board-visuals.js', 'resources/js/game-play.js'])
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

    {{--
        Urutan MOBILE (<768px) vs desktop/tablet (>=768px) sengaja dibuat
        BEDA tanpa duplikasi komponen apa pun — teknik CSS murni:

        1) Kedua div kolom di bawah ("kiri" & "kanan") diberi `max-md:contents`
           — di bawah 768px, div itu sendiri "menghilang" dari layout (CSS
           display:contents) sehingga ANAK-anaknya menjadi child langsung
           dari grid terluar ini, ikut aturan grid-cols-1 & order milik grid
           terluar. Di >=768px, `max-md:contents` tidak berlaku sama sekali
           (kedua div kembali jadi block/grid-item normal persis seperti
           sebelumnya) — jadi tablet & desktop 100% tidak berubah.
        2) Begitu jadi child langsung grid (khusus mobile), <x-game.tips-card>
           tinggal diberi `max-md:order-last` supaya pindah ke paling akhir
           — urutan sisanya (giliran+dadu, papan, pemain, progress, log,
           tombol keluar) otomatis mengikuti urutan DOM apa adanya karena
           order default mereka tetap 0.
        3) Spacing mobile pakai gap grid (`gap-5`=20px, dalam rentang 16-20px
           yang diminta) — `md:gap-6` mengembalikan gap 24px asli untuk
           tablet/desktop (nilai yang sama seperti sebelum perubahan ini).
           `space-y-5` pada kedua div kolom diberi prefix `md:` juga supaya
           TIDAK dobel dengan gap grid saat mobile (di mobile spacing
           murni dari gap grid, bukan lagi margin space-y).
    --}}
    <div class="grid grid-cols-1 gap-5 md:gap-6 xl:grid-cols-3">
        {{-- Kolom kiri/tengah: giliran, dadu, papan, tips --}}
        <div class="max-md:contents md:space-y-5 xl:col-span-2">
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

            <x-game.tips-card :papan="$papan" class="max-md:order-last" />
        </div>

        {{-- Kolom kanan: panel pemain/robot, progress, log --}}
        <div class="max-md:contents md:space-y-5">
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

    {{--
        Modal Achievement Terbuka: konten (nama/deskripsi/poin/warna/icon) 100%
        dinamis, diisi game-play.js dari respons roll/answer (lihat
        GameSessionService::attachNewlyUnlockedAchievements() — bukan hardcode).
        Icon di-clone dari template tersembunyi di bawah (bukan re-implementasi
        SVG terpisah di JS) supaya identik dengan <x-player.icon> yang sama
        dipakai di seluruh aplikasi; fallback ke "trophy" untuk nama icon lain.
    --}}
    <x-player.modal name="achievement-unlock-modal">
        <div class="relative overflow-hidden text-center">
            <div id="achievement-confetti-layer" class="pointer-events-none absolute inset-0 -m-6 overflow-hidden"></div>
            <p class="text-xs font-bold uppercase tracking-widest text-accent-500">Achievement Terbuka!</p>
            <span id="achievement-unlock-badge" class="relative mx-auto mt-3 flex h-20 w-20 items-center justify-center rounded-2xl text-white shadow-soft"></span>
            <p id="achievement-unlock-kode" class="relative mt-3 text-xs font-bold uppercase tracking-wide"></p>
            <p id="achievement-unlock-nama" class="relative mt-0.5 text-xl font-bold text-slate-800"></p>
            <p id="achievement-unlock-deskripsi" class="relative mt-1 text-sm text-slate-500"></p>
            <p id="achievement-unlock-poin" class="relative mt-3 inline-flex items-center gap-1.5 rounded-full bg-accent-50 px-4 py-1.5 text-sm font-bold text-accent-600"></p>
            <button id="achievement-unlock-next" type="button"
                    class="relative mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                Lanjut
            </button>
        </div>
    </x-player.modal>

    {{-- Template icon achievement (dikloning JS, lihat catatan di atas) --}}
    <div id="achievement-icon-templates" class="hidden">
        <template data-icon="trophy"><x-player.icon name="trophy" class="h-10 w-10" /></template>
        <template data-icon="flame"><x-player.icon name="flame" class="h-10 w-10" /></template>
        <template data-icon="crown"><x-player.icon name="crown" class="h-10 w-10" /></template>
        <template data-icon="target"><x-player.icon name="target" class="h-10 w-10" /></template>
        <template data-icon="users"><x-player.icon name="users" class="h-10 w-10" /></template>
        <template data-icon="dice"><x-player.icon name="dice" class="h-10 w-10" /></template>
        <template data-icon="wreath"><x-player.icon name="wreath" class="h-10 w-10" /></template>
        <template data-icon="shield"><x-player.icon name="shield" class="h-10 w-10" /></template>
        <template data-icon="star"><x-player.icon name="star" class="h-10 w-10" /></template>
        <template data-icon="medal"><x-player.icon name="medal" class="h-10 w-10" /></template>
    </div>

    <div id="game-play-data"
         data-state-url="{{ route('game.state', $gameSession) }}"
         data-roll-url="{{ route('game.roll', $gameSession) }}"
         data-answer-url="{{ route('game.answer', $gameSession) }}"
         data-leave-url="{{ route('game.leave', $gameSession) }}"
         data-heartbeat-url="{{ route('game.heartbeat', $gameSession) }}"
         data-current-user-id="{{ auth()->id() }}"
         data-jumlah-petak="{{ $papan->jumlah_petak }}"></div>
</x-player-layout>
