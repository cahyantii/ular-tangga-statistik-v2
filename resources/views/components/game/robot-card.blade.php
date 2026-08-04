{{-- Template kartu Robot — sama sumber data (game.state), tema warna slate agar terlihat berbeda dari kartu pemain manusia. --}}
<template id="robot-card-template">
    <div class="robot-card relative overflow-hidden rounded-2xl border-2 border-transparent bg-slate-50 dark:bg-slate-800 p-4 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-soft">
        <span class="turn-glow pointer-events-none absolute inset-0 rounded-2xl opacity-0"></span>

        <div class="relative flex items-center gap-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-700 text-white shadow-sm">
                <x-player.icon name="cpu" class="h-5 w-5" />
            </span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-slate-800 dark:text-dark-text">Robot</p>
                <div class="mt-0.5 flex items-center gap-1.5">
                    <span class="turn-badge hidden items-center gap-1 rounded-full bg-slate-700 px-1.5 py-0.5 text-[10px] font-semibold text-white">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span> Giliran
                    </span>
                </div>
            </div>
        </div>

        <div class="relative mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div>
                <p class="text-slate-400 dark:text-dark-muted">Skor</p>
                <p class="mt-0.5 flex items-center justify-center gap-1 font-bold text-accent-600 dark:text-accent-400">
                    <x-player.icon name="star" class="h-3 w-3" />
                    <span data-field="skor">0</span>
                </p>
            </div>
            <div>
                <p class="text-slate-400 dark:text-dark-muted">Posisi</p>
                <p class="mt-0.5 flex items-center justify-center gap-1 font-bold text-slate-600 dark:text-slate-400">
                    <x-player.icon name="target" class="h-3 w-3" />
                    <span data-field="posisi">0</span>
                </p>
            </div>
            <div>
                <p class="text-slate-400 dark:text-dark-muted">Akurasi</p>
                <p class="mt-0.5 font-bold text-slate-600 dark:text-slate-400" data-field="akurasi">&mdash;</p>
            </div>
        </div>

        <div class="relative mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
            <div class="h-2 rounded-full bg-slate-500 transition-all duration-700 ease-out" data-field="progress-bar" style="width: 0%"></div>
        </div>
    </div>
</template>
