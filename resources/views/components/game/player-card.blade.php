{{--
    Template kartu pemain (manusia). Data sebenarnya (skor, posisi, akurasi,
    giliran) selalu diambil live dari endpoint game.state oleh game-play.js —
    komponen ini hanya menyediakan markup yang di-clone & diisi oleh JS supaya
    tetap konsisten dengan arsitektur "server = satu-satunya sumber kebenaran"
    yang sudah ada di game engine ini.
--}}
<template id="player-card-template">
    <div class="player-card relative overflow-hidden rounded-2xl border-2 border-transparent bg-white p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-soft dark:bg-slate-800">
        <span class="turn-glow pointer-events-none absolute inset-0 rounded-2xl opacity-0"></span>

        <div class="relative flex items-center gap-3">
            <span class="player-avatar flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-500 text-sm font-bold text-white shadow-sm" data-field="avatar"></span>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100" data-field="nama"></p>
                <div class="mt-0.5 flex items-center gap-1.5">
                    <span class="me-badge hidden rounded-full bg-primary-50 px-1.5 py-0.5 text-[10px] font-semibold text-primary-600 dark:bg-primary-500/15 dark:text-primary-400">Anda</span>
                    <span class="turn-badge hidden items-center gap-1 rounded-full bg-secondary-500 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span> Giliran
                    </span>
                </div>
            </div>
        </div>

        <div class="relative mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div>
                <p class="text-slate-400 dark:text-slate-500">Skor</p>
                <p class="mt-0.5 flex items-center justify-center gap-1 font-bold text-accent-600 dark:text-accent-400">
                    <x-player.icon name="star" class="h-3 w-3" />
                    <span data-field="skor">0</span>
                </p>
            </div>
            <div>
                <p class="text-slate-400 dark:text-slate-500">Posisi</p>
                <p class="mt-0.5 flex items-center justify-center gap-1 font-bold text-primary-600 dark:text-primary-400">
                    <x-player.icon name="target" class="h-3 w-3" />
                    <span data-field="posisi">0</span>
                </p>
            </div>
            <div>
                <p class="text-slate-400 dark:text-slate-500">Akurasi</p>
                <p class="mt-0.5 font-bold text-secondary-600 dark:text-secondary-400" data-field="akurasi">&mdash;</p>
            </div>
        </div>

        <div class="relative mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
            <div class="h-2 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 transition-all duration-700 ease-out" data-field="progress-bar" style="width: 0%"></div>
        </div>
    </div>
</template>
