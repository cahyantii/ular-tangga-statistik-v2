@props(['current', 'totalPemain'])

@php
    $items = [
        ['icon' => 'star', 'tone' => 'bg-accent-50 dark:bg-accent-900/20 text-accent-500 dark:text-accent-400', 'label' => 'Total Skor', 'value' => number_format($current['total_skor'] ?? 0, 0, ',', '.')],
        ['icon' => 'trophy', 'tone' => 'bg-secondary-50 dark:bg-secondary-900/20 text-secondary-500 dark:text-secondary-400', 'label' => 'Total Menang', 'value' => $current['total_menang'] ?? 0],
        ['icon' => 'gamepad', 'tone' => 'bg-primary-50 dark:bg-primary-900/20 text-primary-500 dark:text-primary-400', 'label' => 'Total Main', 'value' => $current['total_main'] ?? 0],
        ['icon' => 'medal', 'tone' => 'bg-violet-50 dark:bg-violet-900/20 text-violet-500 dark:text-violet-400', 'label' => 'Rank Global', 'value' => $current ? "{$current['rank']} / {$totalPemain}" : 'Belum Peringkat'],
    ];
@endphp

<div class="rounded-3xl bg-white dark:bg-dark-surface p-5 shadow-sm sm:p-6">
    <h3 class="text-base font-bold text-slate-800 dark:text-dark-text">Statistik Kamu</h3>

    <dl class="mt-4 divide-y divide-slate-100 dark:divide-dark-border">
        @foreach ($items as $item)
            <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                <dt class="flex items-center gap-2.5 text-sm text-slate-500 dark:text-dark-muted">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['tone'] }}">
                        <x-player.icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    {{ $item['label'] }}
                </dt>
                <dd class="shrink-0 text-sm font-semibold text-slate-700 dark:text-dark-text">{{ $item['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</div>
