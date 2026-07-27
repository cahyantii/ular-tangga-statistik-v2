@props(['tile', 'row', 'col', 'connector' => null])

@php
    $palette = [
        'start' => ['color' => 'text-secondary-500', 'icon' => 'flag'],
        'finish' => ['color' => 'text-violet-600', 'icon' => 'trophy'],
        'soal' => ['color' => 'text-primary-600', 'icon' => 'book'],
        'bonus' => ['color' => 'text-accent-500', 'icon' => 'star'],
        'mystery' => ['color' => 'text-violet-600', 'icon' => 'help'],
        'ular' => ['color' => 'text-rose-600', 'icon' => 'snake'],
        'tangga' => ['color' => 'text-secondary-600', 'icon' => 'ladder'],
        'penalti' => ['color' => 'text-rose-500', 'icon' => 'alert'],
        'biasa' => ['color' => 'text-slate-400', 'icon' => null],
    ];

    // Petak yang di-nonaktifkan admin (is_active=false) diperlakukan visual &
    // fungsional seperti petak biasa — jenis_petak aslinya tetap tersimpan di
    // database supaya bisa diaktifkan kembali tanpa kehilangan konfigurasi.
    $type = $tile->is_active ? $tile->jenis_petak->value : 'biasa';
    $style = $palette[$type] ?? $palette['biasa'];
    $delta = $connector ? $connector->posisi_akhir - $connector->posisi_awal : null;

    // Kustomisasi per-petak dari Editor Petak admin (warna/border_warna/icon)
    // selalu diutamakan di atas palet default jenis petak bila diisi.
    $customBackground = $tile->is_active ? $tile->warna : null;
    $customBorder = $tile->is_active ? $tile->border_warna : null;
    $customIcon = $tile->is_active ? $tile->icon : null;
    $icon = $customIcon ?: $style['icon'];

    // Tema Checkerboard Merah Putih
    $isRed = ($row + $col) % 2 === 0;
    $baseBgClass = $isRed ? 'bg-[#FFE2E2]' : 'bg-[#FBEFEF]';
    $baseTextClass = $isRed ? 'text-red-700' : 'text-red-700'; // Untuk angka raksasa (watermark)
    $baseBorderClass = $isRed ? 'border-[#FFE2E2]' : 'border-[#FFE2E2]';

    // Glow lembut hanya untuk jenis petak yang "istimewa"
    $glowClass = [
        'finish' => 'tile-glow-finish',
        'bonus' => 'tile-glow-bonus',
        'mystery' => 'tile-glow-mystery',
        'tangga' => 'tile-glow-tangga',
        'ular' => 'tile-glow-ular',
    ][$type] ?? '';

    $inlineStyle = 'grid-row: '.$row.'; grid-column: '.$col.';';
    $cellInlineStyle = trim(
        ($customBackground ? 'background: '.$customBackground.';' : '')
        .($customBorder ? ' border: '.$customBorder.';' : '')
    );
@endphp

<div
    data-posisi="{{ $tile->posisi }}"
    data-jenis="{{ $type }}"
    style="{{ $inlineStyle }}"
    class="relative z-10 aspect-square"
>
    {{-- Menghilangkan margin/inset dan border-radius agar tile saling menempel tanpa gap --}}
    <div
        class="tile-cell absolute inset-0 flex flex-col items-center justify-center overflow-hidden border {{ $customBackground ? '' : $baseBgClass }} {{ $customBorder ? '' : $baseBorderClass }} {{ $glowClass }} transition-all duration-200"
        @if ($cellInlineStyle) style="{{ $cellInlineStyle }}" @endif
        title="{{ $tile->label ?? $tile->jenis_petak->label() }}"
    >
        {{-- Angka memenuhi tile (Watermark/Cartoon) --}}
        <span class="absolute inset-0 flex items-center justify-center text-[20px] sm:text-[25px] lg:text-[35px] font-black pointer-events-none {{ $isRed ? 'text-red-500 drop-shadow-md' : 'text-red-500 drop-shadow-sm' }}" style="line-height: 1; font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', cursive;">{{ $tile->posisi }}</span>

        {{-- Badge Logo di Pojok Kanan Atas (Bisa diklik) --}}
        @if ($icon)
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('preview-tile', { detail: { jenis: '{{ $type }}', posisi: {{ $tile->posisi }} } }))" class="absolute top-1 right-1 sm:top-1.5 sm:right-1.5 h-5 w-5 sm:h-6 sm:w-6 flex items-center justify-center bg-transparent z-20 transition-transform hover:scale-110 active:scale-95 cursor-pointer">
                <x-player.icon :name="$icon" class="h-4 w-4 sm:h-5 sm:w-5 drop-shadow-md {{ $style['color'] }}" />
            </button>
        @endif

        {{-- Label Tipe (Opsional) --}}
        @if (! in_array($type, ['biasa', 'start'], true))
            <span class="absolute bottom-1 hidden text-[8px] font-bold uppercase tracking-wider opacity-90 sm:block {{ $isRed ? 'text-red-100' : 'text-slate-500' }} drop-shadow-sm z-10">{{ $type }}</span>
        @endif

        {{-- Indikator Ular/Tangga --}}
        @if ($delta !== null)
            <span class="absolute bottom-1 left-1 text-[9px] font-black sm:text-[11px] {{ $isRed ? 'text-white' : 'text-red-500' }} z-10 px-1 rounded drop-shadow-sm">{{ $delta > 0 ? '+' : '' }}{{ $delta }}</span>
        @endif

        {{-- Cincin glow, diaktifkan JS saat kotak ini adalah giliran berjalan --}}
        <span class="tile-active-ring pointer-events-none absolute inset-0 opacity-0 ring-4 ring-inset ring-accent-400"></span>
    </div>
</div>
