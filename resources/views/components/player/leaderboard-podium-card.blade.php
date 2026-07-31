@props(['rank', 'row'])

@php
    $isFirst = $rank === 1;

    $medalTone = match ($rank) {
        1 => 'bg-accent-500',
        2 => 'bg-slate-400',
        default => 'bg-amber-700',
    };

    // These CSS classes are defined in app.css (not Tailwind dynamic classes)
    // so they always render correctly in dark mode
    $cardClass = match ($rank) {
        1 => 'podium-card-gold',
        2 => 'podium-card-silver',
        default => 'podium-card-bronze',
    };

    $avatarUser = (object) ['name' => $row['nama'], 'avatar_url' => $row['avatar_url']];
@endphp

<div class="relative flex flex-col items-center rounded-3xl border p-5 pt-9 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft {{ $cardClass }} {{ $isFirst ? 'sm:-mt-6 sm:pb-8 sm:pt-11' : 'sm:pt-9' }}">

    @if ($isFirst)
        <span class="absolute -top-6 flex h-11 w-11 items-center justify-center" aria-hidden="true">
            <x-player.icon name="crown" class="h-9 w-9 text-accent-500 drop-shadow-[0_2px_8px_rgba(245,158,11,0.5)]" />
        </span>
    @endif

    <div class="relative">
        <x-player.avatar
            :user="$avatarUser"
            :size="$isFirst ? 'h-20 w-20' : 'h-16 w-16'"
            :text-size="$isFirst ? 'text-2xl' : 'text-xl'"
            class="ring-4 ring-white shadow-md dark:ring-slate-700"
        />
        <span class="absolute -bottom-1.5 -left-1.5 flex h-7 w-7 items-center justify-center rounded-full {{ $medalTone }} text-xs font-bold text-white ring-2 ring-white dark:ring-slate-800">
            {{ $rank }}
        </span>
    </div>

    {{-- Name uses .podium-name so CSS can target it for dark mode --}}
    <p class="podium-name mt-3 max-w-full truncate font-bold text-slate-800 {{ $isFirst ? 'text-lg' : 'text-base' }}">
        {{ $row['nama'] }}
    </p>

    <x-player.badge :label="$row['badge']" :color="$row['badge_color']" class="mt-1.5" />

    {{-- Score: .podium-score for CSS dark targeting; fallback Tailwind for non-first --}}
    <p class="podium-score mt-3 flex items-center gap-1.5 font-extrabold {{ $isFirst ? 'text-2xl text-accent-600' : 'text-xl text-slate-700 dark:text-slate-200' }}">
        <x-player.icon name="star" class="{{ $isFirst ? 'h-5 w-5' : 'h-4 w-4' }} text-accent-500" />
        {{ number_format($row['total_skor'], 0, ',', '.') }}
    </p>
</div>
