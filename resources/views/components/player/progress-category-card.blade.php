@props([
    'kategori',
    'icon' => 'book',
    'totalDijawab',
    'totalSoal',
    'akurasi',
    'progressPercent',
    'status',
    'color' => 'blue',
    'delay' => 0,
])

@php
    $palette = [
        'blue' => ['icon' => 'bg-primary-500 text-white', 'pill' => 'bg-primary-50 text-primary-600', 'bar' => 'from-primary-500 to-primary-600', 'text' => 'text-primary-600'],
        'green' => ['icon' => 'bg-secondary-500 text-white', 'pill' => 'bg-secondary-50 text-secondary-600', 'bar' => 'from-secondary-500 to-secondary-600', 'text' => 'text-secondary-600'],
        'amber' => ['icon' => 'bg-accent-500 text-white', 'pill' => 'bg-accent-50 text-accent-600', 'bar' => 'from-accent-500 to-accent-600', 'text' => 'text-accent-600'],
        'purple' => ['icon' => 'bg-violet-500 text-white', 'pill' => 'bg-violet-50 text-violet-600', 'bar' => 'from-violet-500 to-violet-600', 'text' => 'text-violet-600'],
        'rose' => ['icon' => 'bg-rose-500 text-white', 'pill' => 'bg-rose-50 text-rose-600', 'bar' => 'from-rose-500 to-rose-600', 'text' => 'text-rose-600'],
    ];

    $tone = $palette[$color] ?? $palette['blue'];

    $statusLabel = match ($status) {
        'selesai' => 'Selesai',
        'sedang_belajar' => 'Sedang belajar',
        default => 'Belum dimulai',
    };

    $subtitle = match ($status) {
        'selesai' => "{$totalDijawab} soal dijawab &middot; akurasi {$akurasi}%",
        'sedang_belajar' => "{$totalDijawab} dari {$totalSoal} soal dijawab",
        default => 'Belum ada soal dijawab',
    };
@endphp

<div style="animation-delay: {{ $delay }}ms" class="animate-fade-in-up">
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tone['icon'] }}">
                <x-player.icon :name="$icon" class="h-4 w-4" />
            </span>
            <span class="truncate font-semibold text-slate-700">{{ $kategori }}</span>
        </div>
        <span class="shrink-0 font-bold {{ $tone['text'] }}">{{ $progressPercent }}%</span>
    </div>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
        <span class="text-xs text-slate-400">{!! $subtitle !!}</span>
        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tone['pill'] }}">
            {{ $statusLabel }}
        </span>
    </div>

    <div class="mt-2.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
        <div
            class="h-2.5 rounded-full bg-gradient-to-r {{ $tone['bar'] }} transition-all duration-700 ease-out"
            style="width: {{ $progressPercent }}%"
        ></div>
    </div>
</div>
