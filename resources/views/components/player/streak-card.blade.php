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

        <img
            src="{{ asset('images/brand/logo-ularr.png') }}"
            alt=""
            aria-hidden="true"
            class="h-[90px] w-[90px] shrink-0 select-none object-contain sm:h-[100px] sm:w-[100px] lg:h-[110px] lg:w-[110px]"
        >
    </div>

    <p class="mt-3 text-xs font-medium text-slate-400">
        {{ $streak > 0 ? 'Pertahankan terus streak belajarmu!' : 'Ayo mulai streak-mu hari ini!' }}
    </p>
</div>
