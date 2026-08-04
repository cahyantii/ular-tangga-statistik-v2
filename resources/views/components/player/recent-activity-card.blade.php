@props(['activities'])

@php
    $palette = [
        'blue' => 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400',
        'green' => 'bg-secondary-50 dark:bg-secondary-900/20 text-secondary-600 dark:text-secondary-400',
        'amber' => 'bg-accent-50 dark:bg-accent-900/20 text-accent-600 dark:text-accent-400',
        'purple' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400',
        'rose' => 'bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400',
    ];
@endphp

<div class="animate-fade-in-up rounded-3xl bg-white dark:bg-dark-surface p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
    <h3 class="mb-5 flex items-center gap-2 text-base font-bold text-slate-700 dark:text-dark-text">
        <x-player.icon name="clock" class="h-5 w-5 text-primary-500" />
        Aktivitas Terakhir
    </h3>

    @if ($activities->isEmpty())
        <div class="flex flex-col items-center gap-3 py-8 text-center sm:flex-row sm:gap-5 sm:py-6 sm:text-left">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                <x-player.icon name="clipboard-search" class="h-8 w-8" />
            </span>
            <div>
                <p class="font-bold text-slate-700 dark:text-dark-text">Belum ada aktivitas</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-dark-muted">Mulai mengerjakan soal untuk melihat riwayat aktivitasmu di sini.</p>
            </div>
        </div>
    @else
        <ul class="divide-y divide-slate-100 dark:divide-dark-border">
            @foreach ($activities as $activity)
                <li class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $palette[$activity['color']] ?? $palette['blue'] }}">
                        <x-player.icon :name="$activity['icon']" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-700 dark:text-dark-text">{{ $activity['title'] }}</p>
                        <p class="text-xs text-slate-400 dark:text-dark-muted">{{ $activity['subtitle'] }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-400 dark:text-dark-muted">{{ $activity['at']->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
