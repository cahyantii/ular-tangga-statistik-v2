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

    <div id="game-fullscreen-container" class="transition-colors duration-300 relative group w-full h-full overflow-y-auto overflow-x-hidden p-0 xl:bg-transparent">

        {{-- Style centering papan mobile — tidak bisa pakai Tailwind karena calc(100svh) tidak masuk safelist --}}
        <style>
            @media (max-width: 1279px) {
                #board-centering-wrapper {
                    min-height: calc(100svh - 136px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding-bottom: 15%;
                }
            }
        </style>
        
        {{-- Toast notifikasi ringan --}}
        <div id="game-toast" class="fixed left-1/2 top-20 z-50 hidden -translate-x-1/2 animate-fade-in-up rounded-xl border border-accent-200 bg-white px-4 py-3 text-sm font-medium text-accent-700 shadow-soft-lg dark:border-amber-700/50 dark:bg-slate-800 dark:text-amber-400"></div>

        {{-- Lawan terputus koneksi (Multiplayer): countdown masa tenggang reconnect --}}
        <div id="game-paused-banner" class="mb-4 hidden items-center gap-2 rounded-2xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-700 w-full max-w-4xl mx-auto dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-400">
            <x-player.icon name="clock" class="h-4 w-4 shrink-0" />
            Lawan terputus koneksi. Permainan dijeda &mdash; menunggu reconnect
            <span id="game-paused-timer" class="font-bold"></span>
        </div>

        {{--
            Layout v2 (desktop &ge;1280px = 4 kolom eksplisit, di bawahnya = 1
            kolom ditumpuk + panel Pemain/Progress/Log disembunyikan di balik FAB
            mobile):
        --}}
        {{-- ============================================================
             Panel overlay mobile: Pemain, Log, Progress
             Di desktop (xl+) panel ini tidak dipakai — masing-masing
             sudah tampil sebagai kolom grid. Di mobile panel muncul
             sebagai overlay saat tombol FAB diklik.
        ============================================================ --}}

        {{-- Overlay: Pemain (mobile) --}}
        <div
            x-data="{ open: false }"
            @open-mobile-panel.window="open = ($event.detail === 'pemain')"
            @close-mobile-panel.window="open = false"
            class="xl:hidden"
        >
            <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-slate-900/50"></div>
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed inset-x-4 top-20 bottom-24 z-50 overflow-y-auto rounded-3xl bg-white p-4 shadow-xl dark:bg-slate-800"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="users" class="h-4 w-4 text-primary-500" />
                        Pemain
                    </h3>
                    <button @click="open = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-player.icon name="close" class="h-4 w-4" />
                    </button>
                </div>
                <div id="player-panel-list-mobile" class="space-y-3"></div>
            </div>
        </div>

        {{-- Overlay: Log (mobile) --}}
        <div
            x-data="{ open: false }"
            @open-mobile-panel.window="open = ($event.detail === 'log')"
            @close-mobile-panel.window="open = false"
            class="xl:hidden"
        >
            <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-slate-900/50"></div>
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed inset-x-4 top-20 bottom-24 z-50 overflow-y-auto rounded-3xl bg-white p-4 shadow-xl dark:bg-slate-800"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="clock" class="h-4 w-4 text-primary-500" />
                        Log Permainan
                    </h3>
                    <button @click="open = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-player.icon name="close" class="h-4 w-4" />
                    </button>
                </div>
                <ul id="game-log-list-mobile" class="space-y-2 text-xs">
                    <li class="text-slate-400" id="game-log-empty-mobile">Belum ada aktivitas.</li>
                </ul>
            </div>
        </div>

        {{-- Overlay: Progress (mobile) --}}
        <div
            x-data="{ open: false }"
            @open-mobile-panel.window="open = ($event.detail === 'progress')"
            @close-mobile-panel.window="open = false"
            class="xl:hidden"
        >
            <div x-show="open" x-cloak x-transition.opacity @click="open = false" class="fixed inset-0 z-40 bg-slate-900/50"></div>
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="fixed inset-x-4 top-20 bottom-24 z-50 overflow-y-auto rounded-3xl bg-white p-4 shadow-xl dark:bg-slate-800"
            >
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="flag" class="h-4 w-4 text-primary-500" />
                        Progress Permainan
                    </h3>
                    <button @click="open = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <x-player.icon name="close" class="h-4 w-4" />
                    </button>
                </div>
                <div id="progress-panel-mobile" class="space-y-3">
                    <p class="text-xs text-slate-400">Memuat data progress...</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[280px_1fr_280px] xl:gap-4 items-start w-full">
        {{-- Kolom 1 (desktop saja): Pemain + Log --}}
        <div class="max-xl:hidden xl:col-start-1 xl:row-start-1 flex flex-col gap-4">
            {{-- Panel Pemain (desktop) --}}
            <div class="animate-fade-in-up rounded-3xl bg-white p-4 shadow-sm dark:bg-slate-800 sm:p-5">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="users" class="h-4 w-4 text-primary-500" />
                        Pemain
                    </h3>
                </div>
                <div id="player-panel-list" class="space-y-3"></div>
            </div>

            {{-- Log Permainan (desktop saja) --}}
            <div class="animate-fade-in-up">
                <x-game.log-card />
            </div>
        </div>

        {{-- Kolom 2 (tengah): Papan --}}
        <div class="max-xl:order-2 xl:col-start-2 xl:row-start-1">
            {{-- Centering wrapper: di mobile papan di-tengahkan vertikal mengisi sisa layar --}}
            <div id="board-centering-wrapper">
                <div id="board-card" class="animate-fade-in-up rounded-3xl bg-white p-3 shadow-sm dark:bg-slate-800 sm:p-4 w-full" style="aspect-ratio: 1 / 1;">
                {{--
                    Toggle tema visual ular/perosotan - PURE client-side
                    (localStorage, lihat board-visuals.js initBoardThemeToggle()
                    & BoardRenderer.getBoardTheme()/setBoardTheme()). Data
                    konektor & animasi gerak pion TIDAK berubah sama sekali,
                    cuma renderer visual mana yang dipakai untuk jenis=ular.
                --}}
                <div class="mb-3 relative flex justify-center gap-2 items-center">
                    <div id="board-theme-toggle" class="flex gap-2">
                        <button type="button" data-board-theme="ular" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150 dark:border-slate-600 dark:text-slate-400">
                            🐍 Ular
                        </button>
                        <button type="button" data-board-theme="perosotan" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150 dark:border-slate-600 dark:text-slate-400">
                            🛝 Perosotan
                        </button>
                    </div>
                    <button id="toggle-fullscreen-btn" type="button" class="hidden md:flex absolute right-0 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors duration-150" title="Toggle Fullscreen">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                </div>

                <x-game.board :papan="$papan" />
            </div>
            </div>{{-- /centering wrapper mobile --}}
        </div>


        {{-- Kolom 3 (kanan): Giliran+Dadu — disembunyikan di mobile, hanya tampil di desktop (xl ke atas) --}}
        <div id="turn-dice-card" class="max-xl:hidden animate-fade-in-up rounded-[2rem] bg-white/90 backdrop-blur-md border border-white shadow-soft-xl p-5 xl:col-start-3 xl:row-start-1 relative overflow-hidden dark:bg-slate-800/90 dark:border-slate-700/50">
            <!-- Dekorasi gradient background ringan -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-100 rounded-full mix-blend-multiply dark:mix-blend-screen filter blur-3xl opacity-50 pointer-events-none dark:bg-primary-900/20"></div>
            
            <div class="flex flex-col items-center gap-5 relative z-10">
                <div class="flex items-center gap-3 self-start w-full">
                    <div class="relative">
                        <span id="turn-avatar" class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-sm font-black text-white shadow-md"></span>
                        <div id="turn-avatar-ring" class="absolute inset-0 rounded-full border-2 border-primary-400 opacity-0 transition-all duration-700"></div>
                    </div>
                    <div class="flex-1">
                        <p id="turn-indicator" class="text-base font-bold text-slate-800 flex items-center gap-2">
                            Giliran
                            <span id="turn-active-dot" class="hidden relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                        </p>
                        <p id="turn-subtext" class="text-xs font-medium text-slate-400"></p>
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

                    @if(app()->environment('local'))
                        <div class="mt-2 text-center border-t border-slate-100 pt-2 w-full dark:border-slate-700">
                            <label class="text-[10px] text-slate-500 block mb-1 uppercase font-bold tracking-wider">Dev Cheat: Force Roll</label>
                            <input type="number" id="forced-roll-input" min="1" max="100" class="w-20 rounded-md border border-slate-300 py-1 px-2 text-center text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" placeholder="Auto">
                        </div>
                    @endif

                    {{-- UI Inventory --}}
                    <div id="player-inventory-container" class="mt-4 w-full border-t border-slate-100 pt-3 dark:border-slate-700">
                        <h4 class="text-xs font-bold text-slate-500 mb-2 uppercase tracking-wide text-center">Item Anda</h4>
                        <div id="player-inventory-list" class="flex flex-wrap gap-2 justify-center">
                            <!-- Item akan dirender oleh JS -->
                            <p class="text-xs text-slate-400 italic" id="empty-inventory-text">Kosong</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom 4: Tips, di bawah Giliran+Dadu --}}
        

        {{-- Kolom 3: Keluar (mobile: disembunyikan) --}}
        <button
            id="leave-game-button"
            type="button"
            class="max-xl:hidden flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition-all duration-200 hover:bg-rose-50 xl:col-start-3 xl:row-start-2 dark:bg-slate-800 dark:border-rose-800/50 dark:hover:bg-rose-900/20"
        >
            Keluar dari Permainan
        </button>
    </div>

    {{--
        FAB aksi mobile (<1280px, konsisten dengan breakpoint kolom di atas):
        5 tombol — Progress/Pemain buka panel di kiri, Dadu di tengah (lebih
        besar, meneruskan klik ke #roll-dice-button asli), Log/Keluar di
        kanan (Keluar meneruskan klik ke #leave-game-button asli). Satu-
        satunya sumber state (disabled/hidden dadu, konfirmasi keluar) tetap
        elemen aslinya masing-masing — FAB ini murni "remote control", tidak
        ada logic yang diduplikasi (lihat initMobileActionFab() di game-play.js).
    --}}
    <div id="mobile-action-fab" class="fixed inset-x-3 bottom-3 z-50 mx-auto flex max-w-sm items-end justify-between gap-1 rounded-[28px] bg-white/95 p-2 shadow-soft-lg backdrop-blur-md xl:hidden dark:bg-slate-800/95">
        <button type="button" data-open-panel="progress" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 text-slate-500 transition-colors duration-150 hover:bg-slate-100 active:bg-slate-100">
            <x-player.icon name="flag" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">Progress</span>
        </button>
        <button type="button" data-open-panel="pemain" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 text-slate-500 transition-colors duration-150 hover:bg-slate-100 active:bg-slate-100">
            <x-player.icon name="users" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">Pemain</span>
        </button>
        <button
            id="mobile-fab-dice"
            type="button"
            class="-mt-6 flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-full bg-secondary-500 text-white shadow-lg shadow-secondary-900/30 transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
        >
            <x-player.icon name="dice" class="h-7 w-7" />
        </button>
        <button type="button" data-open-panel="log" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 text-slate-500 transition-colors duration-150 hover:bg-slate-100 active:bg-slate-100">
            <x-player.icon name="clock" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">Log</span>
        </button>
        <button id="mobile-fab-leave" type="button" class="flex flex-1 flex-col items-center gap-0.5 rounded-2xl py-2 text-rose-500 transition-colors duration-150 hover:bg-rose-50 active:bg-rose-50">
            <x-player.icon name="logout" class="h-5 w-5" />
            <span class="text-[10px] font-semibold">Keluar</span>
        </button>
    </div>

    {{-- Jarak ekstra bawah supaya konten terakhir (log, dsb.) tidak ketutup FAB mobile --}}
    <div class="h-24 xl:hidden"></div>

    {{-- Template kartu (di-clone JS, lihat komentar di masing-masing file komponen) --}}
    <x-game.player-card />
    <x-game.robot-card />

    {{-- Modal Gacha Power-Up --}}
    <x-player.modal name="gacha-modal">
        <div class="mb-2 flex flex-col items-center justify-center text-center animate-fade-in-up">
            <h3 class="text-xl font-black text-violet-600 uppercase tracking-widest mb-4">
                Petak Misteri!
            </h3>
            
            <div id="gacha-image-container" class="relative w-32 h-32 mb-4 mx-auto animate-float">
                <div class="absolute inset-0 bg-violet-400 rounded-full opacity-20 blur-xl animate-pulse"></div>
                <img id="gacha-image" src="" alt="Power Up" style="transform: scale(0);" class="relative z-10 w-full h-full object-contain drop-shadow-2xl transition-transform duration-500 delay-200">
            </div>

            <h4 id="gacha-item-name" class="text-lg font-bold text-slate-800 mb-1 opacity-0 transition-opacity duration-500 delay-500"></h4>
            <p id="gacha-item-desc" class="text-sm text-slate-500 opacity-0 transition-opacity duration-500 delay-700"></p>
            
            <div id="gacha-actions-normal" class="w-full mt-6 opacity-0 transition-opacity duration-500 delay-1000">
                <button type="button" id="gacha-klaim-btn" class="w-full rounded-xl bg-violet-500 px-4 py-2 text-white font-bold hover:bg-violet-600 transition-colors">
                    Klaim
                </button>
            </div>
            
            <div id="gacha-actions-resolve" class="w-full mt-6 hidden opacity-0 flex-col gap-2 transition-opacity duration-500 delay-1000">
                <p class="text-xs text-rose-500 font-bold mb-1">Inventory Penuh (Maks 3)!</p>
                <button type="button" id="gacha-keep-btn" class="w-full rounded-xl bg-violet-500 px-4 py-2 text-white font-bold hover:bg-violet-600 transition-colors">
                    Simpan & Buang Terlama
                </button>
                <button type="button" id="gacha-discard-btn" class="w-full rounded-xl border-2 border-rose-500 bg-white px-4 py-2 text-rose-500 font-bold hover:bg-rose-50 transition-colors dark:bg-transparent dark:hover:bg-rose-900/20">
                    Buang Item Ini
                </button>
            </div>
        </div>
    </x-player.modal>

    {{-- Modal Info Power-Up --}}
    <x-player.modal name="powerup-use-modal">
        <div class="mb-2 flex flex-col items-center justify-center text-center">
            <h3 class="text-xl font-black text-violet-600 uppercase tracking-widest mb-4">
                Pakai Item?
            </h3>
            
            <div class="relative w-24 h-24 mb-4 mx-auto">
                <img id="powerup-use-image" src="" alt="Power Up" class="w-full h-full object-contain drop-shadow-lg">
            </div>

            <h4 id="powerup-use-name" class="text-lg font-bold text-slate-800 mb-2"></h4>
            <p id="powerup-use-desc" class="text-sm text-slate-600 mb-6"></p>
            
            <div class="w-full flex gap-2">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', {detail: 'powerup-use-modal'}))" class="flex-1 rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-slate-500 font-bold hover:bg-slate-50 transition-colors dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600">
                    Batal
                </button>
                <button type="button" id="powerup-use-btn" class="flex-1 rounded-xl bg-violet-500 px-4 py-2 text-white font-bold hover:bg-violet-600 transition-colors">
                    Pakai
                </button>
            </div>
        </div>
    </x-player.modal>

    {{-- Modal Soal --}}
    <x-player.modal name="question-modal">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                <x-player.icon name="book" class="h-5 w-5 text-primary-500" />
                <span id="question-title-text">Soal</span>
            </h3>
            <span id="question-timer" class="rounded-full bg-primary-50 px-3 py-1 text-sm font-bold text-primary-600 dark:bg-primary-900/30 dark:text-primary-400"></span>
        </div>
        <p id="question-text" class="mb-4 text-sm leading-relaxed text-slate-700"></p>
        <div id="question-options" class="space-y-2"></div>
        <p id="question-feedback" class="mt-4 hidden rounded-xl bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-700/50 dark:text-slate-300"></p>
    </x-player.modal>

    {{-- Modal Soal Bot (spectator view) --}}
    <x-player.modal name="bot-question-modal">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
                <x-player.icon name="bot" class="h-5 w-5 text-violet-500" />
                Bot Sedang Menjawab
            </h3>
        </div>
        <p id="bot-question-text" class="mb-4 text-sm leading-relaxed text-slate-700"></p>
        <div id="bot-question-options" class="space-y-2"></div>
        <p id="bot-question-feedback" class="mt-4 hidden rounded-xl bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-700/50 dark:text-slate-300"></p>
    </x-player.modal>

    {{-- Modal Duel --}}
    <x-player.modal name="duel-modal" maxWidth="max-w-4xl">
        <div class="mb-4 flex flex-col items-center justify-center">
            <h3 class="flex items-center gap-2 text-xl font-black text-rose-600 uppercase tracking-widest mb-1">
                <x-player.icon name="swords" class="h-6 w-6" />
                Duel!
            </h3>
            <p id="duel-status-text" class="text-sm font-medium text-slate-500">Pertanyaan <span id="duel-question-number">1</span> dari 3</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Kiri (Anda) -->
            <div class="md:border-r md:border-slate-100 md:pr-6 dark:md:border-slate-700">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-md font-bold text-slate-800">Soal Anda</h4>
                    <span id="duel-timer" class="rounded-full bg-rose-50 px-3 py-1 text-sm font-bold text-rose-600 dark:bg-rose-900/30 dark:text-rose-400"></span>
                </div>
                <p id="duel-text" class="mb-4 text-sm leading-relaxed text-slate-700"></p>
                <div id="duel-options" class="space-y-2"></div>
                <div id="duel-feedback" class="mt-4 hidden rounded-xl bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-700/50 dark:text-slate-300"></div>
            </div>

            <!-- Kanan (Lawan) -->
            <div class="md:pl-2">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-md font-bold text-slate-800 flex items-center gap-2">
                        <span id="duel-opponent-name">Lawan</span>
                    </h4>
                    <span id="duel-opponent-status" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500 uppercase tracking-wider dark:bg-slate-700 dark:text-slate-400">Menjawab...</span>
                </div>
                
                <div id="duel-opponent-content">
                    <p id="duel-opponent-text" class="mb-4 text-sm leading-relaxed text-slate-700 text-center italic">Menunggu lawan...</p>
                    <div id="duel-opponent-options" class="space-y-2"></div>
                    <div id="duel-opponent-feedback" class="mt-4 hidden rounded-xl bg-slate-50 p-3 text-sm text-center font-medium text-slate-700 dark:bg-slate-700/50 dark:text-slate-300"></div>
                </div>
            </div>
        </div>
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
            <p id="achievement-unlock-poin" class="relative mt-3 inline-flex items-center gap-1.5 rounded-full bg-accent-50 px-4 py-1.5 text-sm font-bold text-accent-600 dark:bg-amber-900/20 dark:text-amber-400"></p>
            <button id="achievement-unlock-next" type="button"
                    class="relative mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                Lanjut
            </button>
        </div>
    </x-player.modal>

    {{-- Modal Preview Petak --}}
    <x-player.modal name="preview-tile-modal">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 shadow-sm mb-4">
                <x-player.icon name="info" class="h-7 w-7 text-slate-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-800">Preview Petak Khusus</h3>
            <p id="preview-tile-text" class="mt-2 text-sm leading-relaxed text-slate-600 whitespace-pre-line"></p>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" class="mt-6 w-full rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600">Tutup</button>
        </div>
    </x-player.modal>

    <script>
        window.addEventListener('preview-tile', function (e) {
            const data = e.detail;
            let message = `Ini adalah petak ${data.jenis.toUpperCase()} di posisi ${data.posisi}.\n\n`;
            
            if (data.text) {
                message += data.text;
            } else {
                switch (data.jenis) {
                    case 'soal':
                        message += 'Jika Anda mendarat di petak ini, Anda harus menjawab pertanyaan dengan benar untuk mendapatkan poin.';
                        break;
                    case 'bonus':
                        message += 'Anda akan mendapatkan bonus poin jika berhenti di petak ini.';
                        break;
                    case 'penalti':
                        message += 'Poin Anda akan dikurangi jika berhenti di petak ini.';
                        break;
                    case 'ular':
                    case 'tangga':
                        message += `Petak ini adalah jalur ${data.jenis}.`;
                        break;
                    case 'mystery':
                        message += 'Petak misteri bisa memberikan Anda bonus poin atau penalti!';
                        break;
                }
            }
            
            document.getElementById('preview-tile-text').textContent = message;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'preview-tile-modal' }));
        });
    </script>

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
