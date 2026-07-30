@props(['current', 'totalPemain'])

@php
    $items = [
        ['icon' => 'star', 'tone' => 'bg-accent-50 text-accent-500 dark:bg-accent-500/10', 'label' => 'Total Skor', 'value' => number_format($current['total_skor'] ?? 0, 0, ',', '.')],
        ['icon' => 'trophy', 'tone' => 'bg-secondary-50 text-secondary-500 dark:bg-secondary-500/10', 'label' => 'Total Menang', 'value' => $current['total_menang'] ?? 0],
        ['icon' => 'gamepad', 'tone' => 'bg-primary-50 text-primary-500 dark:bg-primary-500/10', 'label' => 'Total Main', 'value' => $current['total_main'] ?? 0],
        ['icon' => 'medal', 'tone' => 'bg-violet-50 text-violet-500 dark:bg-violet-500/10', 'label' => 'Rank Global', 'value' => $current ? "{$current['rank']} / {$totalPemain}" : 'Belum Peringkat'],
    ];
@endphp

<div class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-800 dark:shadow-none sm:p-6">
    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Statistik Kamu</h3>

    <dl class="mt-4 divide-y divide-slate-100 dark:divide-slate-700">
        @foreach ($items as $item)
            <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                <dt class="flex items-center gap-2.5 text-sm text-slate-500 dark:text-slate-400">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['tone'] }}">
                        <x-player.icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    {{ $item['label'] }}
                </dt>
                <dd class="shrink-0 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $item['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</div>
