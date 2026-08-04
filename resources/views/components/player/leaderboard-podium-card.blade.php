@props(['rank', 'row'])

@php
    $isFirst = $rank === 1;

    $medalTone = match ($rank) {
        1 => 'bg-accent-500',
        2 => 'bg-slate-300',
        default => 'bg-amber-700',
    };

    $cardTone = match ($rank) {
        1 => 'bg-gradient-to-b from-accent-100 to-accent-50 border-accent-200 dark:from-accent-900/30 dark:to-accent-900/10 dark:border-accent-800/50',
        2 => 'bg-gradient-to-b from-primary-50 to-white border-primary-100 dark:from-primary-900/30 dark:to-dark-surface dark:border-primary-800/50',
        default => 'bg-gradient-to-b from-orange-100 to-orange-50 border-orange-200 dark:from-orange-900/30 dark:to-orange-900/10 dark:border-orange-800/50',
    };

    $avatarUser = (object) ['name' => $row['nama'], 'avatar_url' => $row['avatar_url']];
@endphp

<div
    class="relative flex flex-col items-center rounded-3xl border p-5 pt-9 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft {{ $cardTone }} {{ $isFirst ? 'sm:-mt-6 sm:pb-8 sm:pt-11' : 'sm:pt-9' }}"
>
    @if ($isFirst)
        <span class="absolute -top-6 flex h-11 w-11 items-center justify-center" aria-hidden="true">
            <x-player.icon name="crown" class="h-9 w-9 text-accent-500 drop-shadow" />
        </span>
    @endif

    <div class="relative">
        <x-player.avatar
            :user="$avatarUser"
            :size="$isFirst ? 'h-20 w-20' : 'h-16 w-16'"
            :text-size="$isFirst ? 'text-2xl' : 'text-xl'"
            class="ring-4 ring-white shadow-md"
        />
        <span class="absolute -bottom-1.5 -left-1.5 flex h-7 w-7 items-center justify-center rounded-full {{ $medalTone }} text-xs font-bold text-white ring-2 ring-white">
            {{ $rank }}
        </span>
    </div>

    <p class="mt-3 max-w-full truncate font-bold text-slate-800 dark:text-dark-text {{ $isFirst ? 'text-lg' : 'text-base' }}">
        {{ $row['nama'] }}
    </p>

    <x-player.badge :label="$row['badge']" :color="$row['badge_color']" class="mt-1.5" />

    <p class="mt-3 flex items-center gap-1.5 font-extrabold {{ $isFirst ? 'text-2xl text-accent-600 dark:text-accent-400' : 'text-xl text-slate-700 dark:text-dark-text' }}">
        <x-player.icon name="star" class="{{ $isFirst ? 'h-5 w-5' : 'h-4 w-4' }} text-accent-500" />
        {{ number_format($row['total_skor'], 0, ',', '.') }}
    </p>
</div>
