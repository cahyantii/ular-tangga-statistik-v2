@props(['current', 'totalPemain'])

@php
    $items = [
        ['icon' => 'star', 'tone' => 'bg-accent-50 text-accent-500', 'label' => 'Total Skor', 'value' => number_format($current['total_skor'] ?? 0, 0, ',', '.')],
        ['icon' => 'trophy', 'tone' => 'bg-secondary-50 text-secondary-500', 'label' => 'Total Menang', 'value' => $current['total_menang'] ?? 0],
        ['icon' => 'gamepad', 'tone' => 'bg-primary-50 text-primary-500', 'label' => 'Total Main', 'value' => $current['total_main'] ?? 0],
        ['icon' => 'medal', 'tone' => 'bg-violet-50 text-violet-500', 'label' => 'Rank Global', 'value' => $current ? "{$current['rank']} / {$totalPemain}" : 'Belum Peringkat'],
    ];
@endphp

<div class="rounded-3xl bg-white p-5 shadow-sm sm:p-6">
    <h3 class="text-base font-bold text-slate-800">Statistik Kamu</h3>

    <dl class="mt-4 divide-y divide-slate-100">
        @foreach ($items as $item)
            <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                <dt class="flex items-center gap-2.5 text-sm text-slate-500">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $item['tone'] }}">
                        <x-player.icon :name="$item['icon']" class="h-4 w-4" />
                    </span>
                    {{ $item['label'] }}
                </dt>
                <dd class="shrink-0 text-sm font-semibold text-slate-700">{{ $item['value'] }}</dd>
            </div>
        @endforeach
    </dl>
</div>
