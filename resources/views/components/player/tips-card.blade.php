@props([
    'tip',
    'title' => 'Tips Hari Ini',
    'illustration' => null,
    'illustrationPosition' => 'bottom',
    'premium' => false,
])

<div @class([
    'relative animate-fade-in-up p-5 sm:p-6',
    'cert-card cert-card--cream lg:p-7' => $premium,
    'rounded-3xl bg-accent-50 dark:bg-accent-900/20 shadow-sm transition-shadow duration-300 hover:shadow-soft' => ! $premium,
])>
    @if ($illustration && $illustrationPosition === 'right')
        <div class="pr-[110px] sm:pr-[120px] lg:pr-[130px]">
            <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-dark-text">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-accent-500 text-white">
                    <x-player.icon name="lightbulb" class="h-4 w-4" />
                </span>
                {{ $title }}
            </h3>

            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-dark-muted">{{ $tip }}</p>
        </div>

        <img
            src="{{ $illustration }}"
            alt=""
            aria-hidden="true"
            class="pointer-events-none absolute bottom-4 right-4 h-auto w-[90px] select-none object-contain sm:w-[100px] lg:w-[110px]"
        >
    @else
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-dark-text">
            <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-accent-500 text-white">
                @if ($premium && $illustration)
                    <img
                        src="{{ $illustration }}"
                        alt=""
                        aria-hidden="true"
                        class="pointer-events-none absolute left-1/2 top-1/2 h-14 w-14 -translate-x-1/2 -translate-y-1/2 select-none object-contain opacity-5"
                    >
                @endif
                <x-player.icon name="lightbulb" class="relative h-4 w-4" />
            </span>
            {{ $title }}
        </h3>

        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-dark-muted">{{ $tip }}</p>

        @if ($illustration)
            <img
                src="{{ $illustration }}"
                alt=""
                aria-hidden="true"
                class="mx-auto mt-4 block h-auto w-[96px] select-none object-contain sm:w-[120px] lg:w-[150px]"
            >
        @endif
    @endif
</div>
