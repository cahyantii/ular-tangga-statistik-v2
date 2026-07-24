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

    // Glow lembut hanya untuk jenis petak yang "istimewa" — bukan soal/penalti/biasa,
    // supaya efeknya tidak berlebihan (sesuai permintaan: glow tipis, tidak semua kotak).
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
    <div
        class="tile-cell absolute inset-[2px] flex flex-col items-center justify-center gap-0.5 overflow-hidden rounded-lg {{ $customBackground ? '' : $style['bg'] }} {{ $style['text'] }} {{ $glowClass }} shadow-sm ring-1 ring-black/5 transition-all duration-200 hover:z-10 hover:scale-[1.08] hover:shadow-soft sm:inset-1 sm:rounded-xl"
        @if ($cellInlineStyle) style="{{ $cellInlineStyle }}" @endif
        title="{{ $tile->label ?? $tile->jenis_petak->label() }}"
    >
        <span class="text-[9px] font-bold leading-none opacity-90 sm:text-[11px]">{{ $tile->posisi }}</span>

        @if ($icon)
            <x-player.icon :name="$icon" class="h-2.5 w-2.5 opacity-90 sm:h-3.5 sm:w-3.5" />
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
