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

    $palette = [
        'blue' => ['bg' => 'bg-primary-50', 'border' => 'border-primary-200', 'icon' => 'bg-primary-500', 'bar' => 'bg-primary-500', 'text' => 'text-primary-600'],
        'green' => ['bg' => 'bg-secondary-50', 'border' => 'border-secondary-200', 'icon' => 'bg-secondary-500', 'bar' => 'bg-secondary-500', 'text' => 'text-secondary-600'],
        'amber' => ['bg' => 'bg-accent-50', 'border' => 'border-accent-200', 'icon' => 'bg-accent-500', 'bar' => 'bg-accent-500', 'text' => 'text-accent-600'],
        'purple' => ['bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'icon' => 'bg-violet-500', 'bar' => 'bg-violet-500', 'text' => 'text-violet-600'],
        'rose' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'icon' => 'bg-rose-500', 'bar' => 'bg-rose-500', 'text' => 'text-rose-600'],
    ];

    $tone = $palette[$achievement->warna_badge] ?? $palette['blue'];
    $isInProgress = ! $earned && $current > 0;
    $isLocked = ! $earned && $current <= 0;

    $isPercentType = $achievement->syarat_type === AchievementCriteriaType::AkurasiKeseluruhan;
    $fraction = $isPercentType
        ? "{$current}% / {$target}%"
        : "{$current} / {$target}";
@endphp

<div
    class="group relative overflow-hidden rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-1 hover:shadow-soft
        {{ $earned ? "{$tone['bg']} {$tone['border']}" : 'border-slate-200 bg-white' }}"
>
    <div class="flex items-start gap-4">
        <span
            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-transform duration-200 group-hover:scale-105
                {{ $isLocked ? 'bg-slate-200 text-slate-400' : "{$tone['icon']} text-white" }}"
        >
            <x-player.icon :name="$achievement->icon ?? 'trophy'" class="h-7 w-7" />
        </span>

        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800">{{ $achievement->nama }}</p>
            <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $achievement->deskripsi }}</p>
        </div>
    </div>

    @if ($isInProgress)
        <div class="mt-4 flex items-center justify-between text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-300 px-2.5 py-1 font-semibold text-slate-500">
                <x-player.icon name="lock" class="h-3 w-3" />
                Belum Diraih
            </span>
            <span class="font-semibold text-slate-500">{{ $fraction }}</span>
        </div>

        <div class="mt-2 flex items-center gap-3">
            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                <div class="h-2.5 rounded-full {{ $tone['bar'] }} transition-all duration-700 ease-out" style="width: {{ $percent }}%"></div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1 text-xs font-bold text-accent-600">
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
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                    <x-player.icon name="lock" class="h-3 w-3" />
                    Terkunci
                </span>
            @endif

            <span class="inline-flex items-center gap-1 text-xs font-bold text-accent-600">
                <x-player.icon name="coin" class="h-3.5 w-3.5" />
                +{{ $achievement->reward_poin }} Poin
            </span>
        </div>

        @if ($earned && $earnedAt)
            <p class="mt-2 text-xs font-medium {{ $tone['text'] }}">Diraih pada {{ $earnedAt->translatedFormat('d F Y') }}</p>
        @endif
    @endif
</div>
