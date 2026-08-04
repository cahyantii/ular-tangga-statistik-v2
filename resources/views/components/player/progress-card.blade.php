@props([
    'kategori',
    'totalDijawab',
    'akurasi',
])

@php
    $selesai = $totalDijawab > 0;
    $width = min(100, max(0, (float) $akurasi));
@endphp

<div>
    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2 text-sm">
        <span class="font-semibold text-slate-700 dark:text-dark-text">{{ $kategori }}</span>

        <span class="flex items-center gap-2">
            <span class="text-xs text-slate-400 dark:text-dark-muted">
                {{ $selesai ? $akurasi.'% akurasi' : 'Belum ada soal dijawab' }}
            </span>
            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $selesai ? 'bg-secondary-50 text-secondary-600 dark:bg-secondary-900/20 dark:text-secondary-400' : 'bg-slate-100 text-slate-500 dark:bg-dark-surface-hover dark:text-dark-muted' }}">
                {{ $selesai ? 'Selesai' : 'Belum dimainkan' }}
            </span>
        </span>
    </div>

    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-dark-surface-hover">
        <div
            class="h-2.5 rounded-full bg-gradient-to-r from-secondary-500 to-secondary-600 transition-all duration-700 ease-out"
            style="width: {{ $width }}%"
        ></div>
    </div>
</div>
