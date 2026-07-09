@props(['tile', 'row', 'col', 'connector' => null])

@php
    $palette = [
        'start' => ['bg' => 'bg-secondary-500', 'text' => 'text-white', 'icon' => 'flag'],
        'finish' => ['bg' => 'bg-gradient-to-br from-violet-600 via-violet-500 to-amber-400', 'text' => 'text-white', 'icon' => 'trophy'],
        'soal' => ['bg' => 'bg-primary-500', 'text' => 'text-white', 'icon' => 'book'],
        'bonus' => ['bg' => 'bg-accent-500', 'text' => 'text-white', 'icon' => 'star'],
        'mystery' => ['bg' => 'bg-violet-500', 'text' => 'text-white', 'icon' => 'help'],
        'ular' => ['bg' => 'bg-rose-600', 'text' => 'text-white', 'icon' => 'snake'],
        'tangga' => ['bg' => 'bg-secondary-200', 'text' => 'text-secondary-700', 'icon' => 'ladder'],
        'penalti' => ['bg' => 'bg-rose-300', 'text' => 'text-rose-800', 'icon' => 'alert'],
        'biasa' => ['bg' => 'bg-white', 'text' => 'text-slate-400', 'icon' => null],
    ];

    $type = $tile->jenis_petak->value;
    $style = $palette[$type] ?? $palette['biasa'];
    $delta = $connector ? $connector->posisi_akhir - $connector->posisi_awal : null;
@endphp

<div
    data-posisi="{{ $tile->posisi }}"
    data-jenis="{{ $type }}"
    style="grid-row: {{ $row }}; grid-column: {{ $col }};"
    class="relative aspect-square"
>
    <div
        class="tile-cell absolute inset-[2px] flex flex-col items-center justify-center gap-0.5 overflow-hidden rounded-lg {{ $style['bg'] }} {{ $style['text'] }} shadow-sm ring-1 ring-black/5 transition-all duration-200 hover:z-10 hover:scale-[1.08] hover:shadow-soft sm:inset-1 sm:rounded-xl"
        title="{{ $tile->label ?? $tile->jenis_petak->label() }}"
    >
        <span class="text-[9px] font-bold leading-none opacity-90 sm:text-[11px]">{{ $tile->posisi }}</span>

        @if ($style['icon'])
            <x-player.icon :name="$style['icon']" class="h-2.5 w-2.5 opacity-90 sm:h-3.5 sm:w-3.5" />
        @endif

        @if (! in_array($type, ['biasa', 'start'], true))
            <span class="hidden text-[7px] font-medium leading-none opacity-85 sm:block">{{ $type }}</span>
        @endif

        @if ($delta !== null)
            <span class="text-[7px] font-bold leading-none sm:text-[8px]">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</span>
        @endif

        {{-- Cincin glow, diaktifkan JS saat kotak ini adalah giliran berjalan --}}
        <span class="tile-active-ring pointer-events-none absolute inset-0 rounded-lg opacity-0 ring-2 ring-accent-400 sm:rounded-xl"></span>
    </div>
</div>
