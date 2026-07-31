@props([
    'achievement',
    'earned' => false,
    'earnedAt' => null,
    'current' => 0,
    'target' => 0,
    'percent' => 0,
])

@php
    use App\Enums\AchievementCriteriaType;

    // icon color for earned icon badge
    $iconColor = match ($achievement->warna_badge) {
        'green'  => 'bg-secondary-500',
        'amber'  => 'bg-accent-500',
        'purple' => 'bg-violet-500',
        'rose'   => 'bg-rose-500',
        default  => 'bg-primary-500',
    };

    // bar color for progress bar
    $barColor = match ($achievement->warna_badge) {
        'green'  => 'bg-secondary-500',
        'amber'  => 'bg-accent-500',
        'purple' => 'bg-violet-500',
        'rose'   => 'bg-rose-500',
        default  => 'bg-primary-500',
    };

    // earned date text color (light mode only — dark mode handled by CSS)
    $earnedTextColor = match ($achievement->warna_badge) {
        'green'  => 'text-secondary-600',
        'amber'  => 'text-accent-600',
        'purple' => 'text-violet-600',
        'rose'   => 'text-rose-600',
        default  => 'text-primary-600',
    };

    // CSS class for card background/border — defined in app.css to guarantee dark mode
    $cardClass = $earned
        ? 'achievement-card-earned-' . $achievement->warna_badge
        : 'achievement-card-locked';

    $isInProgress = ! $earned && $current > 0;
    $isLocked = ! $earned && $current <= 0;

    $isPercentType = $achievement->syarat_type === AchievementCriteriaType::AkurasiKeseluruhan;
    $fraction = $isPercentType
        ? "{$current}% / {$target}%"
        : "{$current} / {$target}";
@endphp

<div class="group relative overflow-hidden rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft {{ $cardClass }}">
    <div class="flex items-start gap-4">
        @if ($achievement->kode === 'WIN_10')
            <img
                src="{{ asset('images/brand/logo-master.png') }}"
                alt=""
                aria-hidden="true"
                class="h-14 w-14 shrink-0 select-none self-center object-contain transition duration-[250ms] ease-out group-hover:scale-[1.06] drop-shadow-[0_6px_14px_rgba(255,193,7,0.22)] sm:h-16 sm:w-16 lg:h-[72px] lg:w-[72px]"
            >
        @elseif ($achievement->kode === 'FIRST_WIN')
            <img
                src="{{ asset('images/brand/logo-piala.png') }}"
                alt=""
                aria-hidden="true"
                class="h-14 w-14 shrink-0 select-none self-center object-contain transition duration-[250ms] ease-out group-hover:scale-[1.06] drop-shadow-[0_8px_16px_rgba(255,193,7,0.28)] sm:h-16 sm:w-16 lg:h-[72px] lg:w-[72px]"
            >
        @else
            <span
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-transform duration-200 group-hover:scale-105
                    {{ $isLocked ? 'bg-slate-200 text-slate-400 dark:bg-slate-700/80 dark:text-slate-500' : "{$iconColor} text-white" }}"
            >
                <x-player.icon :name="$achievement->icon ?? 'trophy'" class="h-7 w-7" />
            </span>
        @endif

        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800 dark:text-slate-100">{{ $achievement->nama }}</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $achievement->deskripsi }}</p>
        </div>
    </div>

    @if ($isInProgress)
        <div class="mt-4 flex items-center justify-between text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-2.5 py-1 font-semibold text-slate-500 dark:border-slate-600 dark:text-slate-400">
                <x-player.icon name="lock" class="h-3 w-3" />
                Belum Diraih
            </span>
            <span class="font-semibold text-slate-500 dark:text-slate-400">{{ $fraction }}</span>
        </div>

        <div class="mt-2 flex items-center gap-3">
            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700/80">
                <div class="h-2.5 rounded-full {{ $barColor }} transition-all duration-700 ease-out" style="width: {{ $percent }}%"></div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1 text-xs font-bold text-accent-600 dark:text-amber-400">
                <x-player.icon name="coin" class="h-3.5 w-3.5" />
                +{{ $achievement->reward_poin }} Poin
            </span>
        </div>
    @else
        <div class="mt-4 flex items-center justify-between">
            @if ($earned)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-500 px-2.5 py-1 text-xs font-semibold text-white">
                    <x-player.icon name="check" class="h-3 w-3" />
                    Diraih
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-700/80 dark:text-slate-300">
                    <x-player.icon name="lock" class="h-3 w-3" />
                    Terkunci
                </span>
            @endif

            <span class="inline-flex items-center gap-1 text-xs font-bold text-accent-600 dark:text-amber-400">
                <x-player.icon name="coin" class="h-3.5 w-3.5" />
                +{{ $achievement->reward_poin }} Poin
            </span>
        </div>

        @if ($earned && $earnedAt)
            <p class="mt-2 text-xs font-medium {{ $earnedTextColor }} dark:text-slate-300">Diraih pada {{ $earnedAt->translatedFormat('d F Y') }}</p>
        @endif
    @endif
</div>
