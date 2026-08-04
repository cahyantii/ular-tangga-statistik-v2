@props([
    'row',
    'highlight' => false,
])

@php
    $avatarUser = (object) ['name' => $row['nama'], 'avatar_url' => $row['avatar_url']];
@endphp

<tr class="transition-colors duration-200 {{ $highlight ? 'bg-gradient-to-r from-primary-50 to-violet-50 dark:from-primary-900/30 dark:to-violet-900/30' : 'hover:bg-slate-50 dark:hover:bg-dark-surface-hover' }}">
    <td class="px-4 py-3.5 sm:px-6">
        @if ($highlight)
            <span class="inline-flex flex-col items-center justify-center rounded-xl bg-primary-500 px-3 py-1.5 leading-tight text-white">
                <span class="text-[10px] font-semibold uppercase tracking-wide">Anda</span>
                <span class="text-sm font-bold">{{ $row['rank'] }}</span>
            </span>
        @else
            <span class="font-semibold text-slate-500 dark:text-dark-muted">{{ $row['rank'] }}</span>
        @endif
    </td>

    <td class="px-4 py-3.5 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <x-player.avatar :user="$avatarUser" size="h-9 w-9" />
            <span class="truncate font-semibold text-slate-800 dark:text-dark-text">{{ $row['nama'] }}</span>
        </div>
    </td>

    <td class="px-4 py-3.5 sm:px-6">
        <x-player.badge :label="$row['badge']" :color="$row['badge_color']" />
    </td>

    <td class="px-4 py-3.5 text-right sm:px-6">
        <span class="inline-flex items-center gap-1.5 font-bold text-accent-600 dark:text-accent-400">
            <x-player.icon name="star" class="h-4 w-4" />
            {{ number_format($row['total_skor'], 0, ',', '.') }}
        </span>
    </td>

    <td class="px-4 py-3.5 text-right sm:px-6">
        <span class="inline-flex items-center gap-1.5 font-semibold text-secondary-600 dark:text-secondary-400">
            <x-player.icon name="trophy" class="h-4 w-4" />
            {{ $row['total_menang'] }}
        </span>
    </td>

    <td class="px-4 py-3.5 text-right sm:px-6">
        <span class="inline-flex items-center gap-1.5 font-semibold text-primary-600 dark:text-primary-400">
            <x-player.icon name="gamepad" class="h-4 w-4" />
            {{ $row['total_main'] }}
        </span>
    </td>
</tr>
