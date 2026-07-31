@props(['user', 'summary'])

@php
    $items = [
        ['icon' => 'calendar', 'tone' => 'bg-violet-50 text-violet-500 dark:bg-violet-950/60 dark:text-violet-400', 'label' => 'Bergabung Sejak', 'value' => $user->created_at->translatedFormat('d F Y')],
        ['icon' => 'gamepad', 'tone' => 'bg-primary-50 text-primary-500 dark:bg-primary-950/60 dark:text-primary-400', 'label' => 'Total Permainan', 'value' => $summary['total_permainan']],
        ['icon' => 'chart-bar', 'tone' => 'bg-secondary-50 text-secondary-500 dark:bg-secondary-950/60 dark:text-secondary-400', 'label' => 'Rata-rata Akurasi', 'value' => $summary['rata_rata_akurasi'].'%'],
        ['icon' => 'clock', 'tone' => 'bg-accent-50 text-accent-500 dark:bg-amber-950/60 dark:text-amber-400', 'label' => 'Waktu Belajar', 'value' => $summary['waktu_belajar_label']],
    ];
@endphp

<div class="rounded-3xl bg-white p-5 shadow-sm dark:bg-slate-800 sm:p-6">
    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Informasi Tambahan</h3>

    <dl class="mt-4 divide-y divide-slate-100 dark:divide-slate-700/50">
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
