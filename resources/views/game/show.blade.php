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

    <x-game.board-stats :papan="$papan" class="mb-5 xl:mb-6" />

    {{--
        Layout v2 (desktop &ge;1280px = 4 kolom eksplisit, di bawahnya = 1
        kolom ditumpuk + panel Pemain/Progress/Log disembunyikan di balik FAB
        mobile):

        1) SEMUA item di bawah ini adalah child LANGSUNG dari grid terluar,
           ditempatkan eksplisit lewat `xl:col-start-*`/`xl:row-start-*`
           (BUKAN auto-placement/`order` polos — dengan 4 kolom & tinggi antar
           kolom yang beda-beda, auto-placement gampang salah taruh child ke
           kolom yang tidak diinginkan). Kolom 1 = Pemain+Progress, kolom 2-3
           = Papan (col-span-2, sengaja TIDAK di-row-span supaya tingginya
           independen, papan hampir selalu lebih tinggi dari sidebar kanan/
           kiri), kolom 4 = Giliran+Dadu, Tips, Keluar.
        2) Di bawah xl (mobile+tablet, disatukan supaya konsisten dengan versi
           sebelumnya yang juga baru pecah kolom di breakpoint xl): Pemain/
           Progress/Log **disembunyikan** (`max-xl:hidden`) dari alur normal —
           cuma muncul sebagai panel mengambang saat tombol FAB terkait
           ditekan (lihat #mobile-action-fab & initMobilePanels() di
           game-play.js). Papan+Giliran+Tips tetap tampil inline, urutannya
           diatur `max-xl:order-*` (giliran dulu, baru papan, baru tips) -
           tombol Keluar disembunyikan inline (`max-xl:hidden`) karena sudah
           terwakili tombol "Keluar" di FAB.
    --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-4 xl:gap-6">
        {{-- Kolom 1 (desktop saja): Pemain --}}
        <div
            x-data="{ open: false }"
            @open-mobile-panel.window="open = ($event.detail === 'pemain')"
            @close-mobile-panel.window="open = false"
            class="xl:col-start-1 xl:row-start-1"
        >
            {{--
                Backdrop & panel SENGAJA berhenti di `bottom-24` (bukan
                `inset-0`/`inset-4` penuh) supaya area FAB di bawah
                (#mobile-action-fab) tetap terlihat & bisa diklik walau panel
                lain sedang terbuka — dulu backdrop/panel menutupi seluruh
                layar termasuk FAB, jadi tidak bisa pindah panel tanpa
                menutup dulu satu-satu.
            --}}
            <div x-show="open" x-cloak @click="open = false" class="fixed inset-x-0 top-0 bottom-24 z-40 bg-slate-900/50 xl:hidden"></div>
            <div
                :class="open ? 'max-xl:fixed max-xl:inset-x-4 max-xl:top-20 max-xl:bottom-24 max-xl:z-40 max-xl:overflow-y-auto' : 'max-xl:hidden'"
                class="animate-fade-in-up rounded-3xl bg-white p-4 shadow-sm sm:p-5"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <x-player.icon name="users" class="h-4 w-4 text-primary-500" />
                        Pemain
                    </h3>
                    <button @click="open = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 xl:hidden">
                        <x-player.icon name="close" class="h-4 w-4" />
                    </button>
                </div>
                <div id="player-panel-list" class="space-y-3"></div>
            </div>
        </div>

        {{-- Kolom 1 (desktop saja): Progress, di bawah Pemain --}}
        <div
            x-data="{ open: false }"
            @open-mobile-panel.window="open = ($event.detail === 'progress')"
            @close-mobile-panel.window="open = false"
            class="xl:col-start-1 xl:row-start-2"
        >
            <div x-show="open" x-cloak @click="open = false" class="fixed inset-x-0 top-0 bottom-24 z-40 bg-slate-900/50 xl:hidden"></div>
            <div :class="open ? 'max-xl:fixed max-xl:inset-x-4 max-xl:top-20 max-xl:bottom-24 max-xl:z-40 max-xl:overflow-y-auto' : 'max-xl:hidden'">
                <button @click="open = false" class="mb-2 ml-auto flex rounded-lg p-1 text-slate-400 hover:bg-slate-100 xl:hidden">
                    <x-player.icon name="close" class="h-4 w-4" />
                </button>
                <x-game.progress-card />
            </div>
        </div>

        {{-- Kolom 2-3: Papan --}}
        <div class="max-xl:order-2 space-y-5 xl:col-start-2 xl:col-span-2 xl:row-start-1 xl:space-y-6">
            <div class="animate-fade-in-up rounded-3xl bg-white p-3 shadow-sm sm:p-5">
                {{--
                    Toggle tema visual ular/perosotan - PURE client-side
                    (localStorage, lihat board-visuals.js initBoardThemeToggle()
                    & BoardRenderer.getBoardTheme()/setBoardTheme()). Data
                    konektor & animasi gerak pion TIDAK berubah sama sekali,
                    cuma renderer visual mana yang dipakai untuk jenis=ular.
                --}}
                <div id="board-theme-toggle" class="mb-3 flex justify-center gap-2">
                    <button type="button" data-board-theme="ular" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150">
                        🐍 Ular
                    </button>
                    <button type="button" data-board-theme="perosotan" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150">
                        🛝 Perosotan
                    </button>
                </div>

                <x-game.board :papan="$papan" />
            </div>
        </div>

        {{-- Kolom 4: Giliran+Dadu --}}
        <div id="turn-dice-card" class="max-xl:order-1 animate-fade-in-up rounded-3xl bg-white p-4 shadow-sm xl:col-start-4 xl:row-start-1">
            <div class="flex flex-col items-center gap-4">
                <div class="flex items-center gap-3 self-start">
                    <span id="turn-avatar" class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-500 text-sm font-bold text-white"></span>
                    <div>
                        <p id="turn-indicator" class="text-base font-bold text-slate-800"></p>
                        <p id="turn-subtext" class="text-xs text-slate-400"></p>
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

        {{-- Kolom 4: Tips, di bawah Giliran+Dadu --}}
        <x-game.tips-card :papan="$papan" class="max-xl:order-3 xl:col-start-4 xl:row-start-2" />

        {{-- Kolom 4: Keluar, di bawah Tips (mobile: disembunyikan, sudah terwakili tombol Keluar di FAB) --}}
        <button
            id="leave-game-button"
            type="button"
            class="max-xl:hidden flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition-all duration-200 hover:bg-rose-50 xl:col-start-4 xl:row-start-3"
        >
            Keluar dari Permainan
        </button>
    </div>

    {{-- Log Permainan: lebar penuh di bawah grid (desktop), panel FAB (mobile) --}}
    <div
        x-data="{ open: false }"
        @open-mobile-panel.window="open = ($event.detail === 'log')"
        @close-mobile-panel.window="open = false"
        class="mt-5 xl:mt-6"
    >
        <div x-show="open" x-cloak @click="open = false" class="fixed inset-x-0 top-0 bottom-24 z-40 bg-slate-900/50 xl:hidden"></div>
        <div :class="open ? 'max-xl:fixed max-xl:inset-x-4 max-xl:top-20 max-xl:bottom-24 max-xl:z-40 max-xl:overflow-y-auto' : 'max-xl:hidden'">
            <button @click="open = false" class="mb-2 ml-auto flex rounded-lg p-1 text-slate-400 hover:bg-slate-100 xl:hidden">
                <x-player.icon name="close" class="h-4 w-4" />
            </button>
            <x-game.log-card />
        </div>
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
    <div id="mobile-action-fab" class="fixed inset-x-3 bottom-3 z-50 mx-auto flex max-w-sm items-end justify-between gap-1 rounded-[28px] bg-white/95 p-2 shadow-soft-lg backdrop-blur-md xl:hidden">
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

    {{-- Modal Preview Petak --}}
    <x-player.modal name="preview-tile-modal">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 shadow-sm mb-4">
                <x-player.icon name="info" class="h-7 w-7 text-slate-500" />
            </div>
            <h3 class="text-lg font-bold text-slate-800">Preview Petak Khusus</h3>
            <p id="preview-tile-text" class="mt-2 text-sm leading-relaxed text-slate-600"></p>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal'))" class="mt-6 w-full rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-600">Tutup</button>
        </div>
    </x-player.modal>

    <script>
        window.addEventListener('preview-tile', function (e) {
            const data = e.detail;
            let message = `Ini adalah petak ${data.jenis.toUpperCase()} di posisi ${data.posisi}. `;
            
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

    <div id="game-play-data"
         data-state-url="{{ route('game.state', $gameSession) }}"
         data-roll-url="{{ route('game.roll', $gameSession) }}"
         data-answer-url="{{ route('game.answer', $gameSession) }}"
         data-leave-url="{{ route('game.leave', $gameSession) }}"
         data-heartbeat-url="{{ route('game.heartbeat', $gameSession) }}"
         data-current-user-id="{{ auth()->id() }}"
         data-jumlah-petak="{{ $papan->jumlah_petak }}"></div>
</x-player-layout>
