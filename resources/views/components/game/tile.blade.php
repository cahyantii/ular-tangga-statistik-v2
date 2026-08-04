@props(['tile', 'row', 'col', 'connector' => null])

@php
    $palette = [
        'start'   => ['color' => 'text-secondary-500', 'icon' => 'flag'],
        'finish'  => ['color' => 'text-violet-600',    'icon' => 'trophy'],
        'mystery' => ['color' => 'text-violet-600',    'icon' => 'help'],
        'ular'    => ['color' => 'text-rose-600',      'icon' => null],
        'tangga'  => ['color' => 'text-secondary-600', 'icon' => null],
        'biasa'   => ['color' => 'text-slate-400',     'icon' => null],
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
    $baseBgClass = $isRed ? 'bg-[#FFE2E2] dark:bg-red-950' : 'bg-[#FBEFEF] dark:bg-red-900';
    $baseTextClass = $isRed ? 'text-red-700' : 'text-red-700'; // Untuk angka raksasa (watermark)



    $inlineStyle = 'grid-row: '.$row.'; grid-column: '.$col.';';
    $cellInlineStyle = trim(
        ($customBackground ? 'background: '.$customBackground.';' : '')
    );

    $previewText = '';
    if ($type === 'mystery') {
        $previewText = 'Petak Misteri: Anda akan mendapatkan Power-Up acak!';
    }
@endphp

<div
    data-posisi="{{ $tile->posisi }}"
    data-jenis="{{ $type }}"
    style="{{ $inlineStyle }}"
    class="relative z-10 aspect-square"
>
    {{-- Menghilangkan margin/inset dan border-radius agar tile saling menempel tanpa gap --}}
    <div
        class="tile-cell absolute inset-0 flex flex-col items-center justify-center overflow-hidden {{ $customBackground ? '' : $baseBgClass }} transition-all duration-200"
        style="container-type: inline-size; {{ $cellInlineStyle }}"
        title="{{ $tile->label ?? $tile->jenis_petak->label() }}"
    >
        {{-- Angka memenuhi tile (Watermark/Cartoon) --}}
        <span class="absolute inset-0 flex items-center justify-center font-black pointer-events-none {{ $isRed ? 'text-red-500 drop-shadow-md' : 'text-red-500 drop-shadow-sm' }}" style="font-size: 30cqi; line-height: 1; font-family: 'Comic Sans MS', 'Chalkboard SE', 'Marker Felt', cursive;">{{ $tile->posisi }}</span>

        {{-- Badge Logo di Pojok Kanan Atas (Bisa diklik) --}}
        @if ($icon)
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('preview-tile', { detail: { jenis: '{{ $type }}', posisi: {{ $tile->posisi }}, text: '{{ $previewText }}' } }))" class="absolute flex items-center justify-center bg-transparent z-20 transition-transform hover:scale-110 active:scale-95 cursor-pointer" style="top: 5cqi; right: 5cqi; width: 30cqi; height: 30cqi;">
                <x-player.icon :name="$icon" class="drop-shadow-md {{ $style['color'] }}" style="width: 22cqi; height: 22cqi;" />
            </button>
        @endif

        {{-- Label Tipe (Opsional) --}}
        @if (! in_array($type, ['biasa', 'start'], true))
            <span class="absolute bottom-1 hidden text-[8px] font-bold uppercase tracking-wider opacity-90 sm:block {{ $isRed ? 'text-red-100' : 'text-slate-500 dark:text-slate-400' }} drop-shadow-sm z-10">{{ $type }}</span>
        @endif

        {{-- Cincin glow, diaktifkan JS saat kotak ini adalah giliran berjalan --}}
        <span class="tile-active-ring pointer-events-none absolute inset-0 opacity-0 ring-4 ring-inset ring-accent-400"></span>
    </div>
</div>
