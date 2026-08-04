{{-- Progress permainan (posisi pion Anda terhadap total kotak papan) — nilai diisi JS dari game.state, jumlah kotak selalu mengikuti papan aktif (tidak hardcode). --}}
<div class="animate-fade-in-up rounded-2xl bg-white dark:bg-dark-surface p-4 shadow-sm sm:p-5">
    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-dark-text">
        <x-player.icon name="flag" class="h-4 w-4 text-primary-500" />
        Progress Permainan
    </h3>

    <div class="mt-3 flex items-end justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <span class="text-lg font-bold text-slate-800 dark:text-dark-text" id="progress-posisi">0</span>
            / <span id="progress-total">0</span> Kotak
        </p>
        <p class="text-sm font-bold text-primary-600 dark:text-primary-400" id="progress-percent">0%</p>
    </div>
</div>
