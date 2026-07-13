@props(['streak'])

<div class="animate-fade-in-up rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700">
        <x-player.icon name="flame" class="h-5 w-5 text-rose-500" />
        Streak Belajar
    </h3>

    <div class="mt-4 flex items-center justify-between gap-3">
        <div>
            <p class="text-3xl font-extrabold text-rose-500">
                {{ $streak }} <span class="text-sm font-semibold text-slate-400">hari</span>
            </p>
            <p class="text-xs text-slate-400">berturut-turut</p>
        </div>

        <svg viewBox="0 0 60 60" class="h-14 w-14 shrink-0 select-none" aria-hidden="true">
            <path d="M8 44c40-25 8-32 20-40" stroke="#FDE4C8" stroke-width="4" stroke-linecap="round" fill="none" />
            <g transform="translate(6 22)">
                <path d="M0 22c0-12 20-12 20-24 0-6-6-8-11-5" stroke="#F68B1F" stroke-width="6" stroke-linecap="round" fill="none" />
                <circle cx="10.5" cy="-7" r="3.5" fill="#F68B1F" />
                <circle cx="9.5" cy="-8" r="0.7" fill="#0A317A" />
            </g>
        </svg>
    </div>

    <p class="mt-3 text-xs font-medium text-slate-400">
        {{ $streak > 0 ? 'Pertahankan terus streak belajarmu!' : 'Ayo mulai streak-mu hari ini!' }}
    </p>
</div>
