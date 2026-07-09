{{-- Ular: badan berkelok SVG dari kepala (posisi_awal) ke ekor (posisi_akhir) — keduanya murni dari database, tidak hardcode. --}}
@props(['from', 'to'])

@php
    $x1 = $from['col'] - 0.5; // kepala
    $y1 = $from['row'] - 0.5;
    $x2 = $to['col'] - 0.5; // ekor
    $y2 = $to['row'] - 0.5;

    $dx = $x2 - $x1;
    $dy = $y2 - $y1;
    $len = max(sqrt($dx ** 2 + $dy ** 2), 0.001);
    $ux = $dx / $len;
    $uy = $dy / $len;
    $px = -$uy;
    $py = $ux;

    $wave = min($len * 0.32, 1.1);

    $c1x = $x1 + $ux * $len * 0.25 + $px * $wave;
    $c1y = $y1 + $uy * $len * 0.25 + $py * $wave;
    $c2x = $x1 + $ux * $len * 0.75 - $px * $wave;
    $c2y = $y1 + $uy * $len * 0.75 - $py * $wave;

    $pathId = 'snake-path-' . $from['col'] . '-' . $from['row'] . '-' . $to['col'] . '-' . $to['row'];
@endphp

<g class="snake-group">
    <path
        id="{{ $pathId }}"
        d="M {{ $x1 }} {{ $y1 }} C {{ $c1x }} {{ $c1y }}, {{ $c2x }} {{ $c2y }}, {{ $x2 }} {{ $y2 }}"
        fill="none"
        stroke="#BE123C"
        stroke-width="0.22"
        stroke-linecap="round"
    />
    <path
        d="M {{ $x1 }} {{ $y1 }} C {{ $c1x }} {{ $c1y }}, {{ $c2x }} {{ $c2y }}, {{ $x2 }} {{ $y2 }}"
        fill="none"
        stroke="#FB7185"
        stroke-width="0.06"
        stroke-linecap="round"
        stroke-dasharray="0.02 0.16"
        opacity="0.85"
    />

    {{-- Ekor --}}
    <circle cx="{{ $x2 }}" cy="{{ $y2 }}" r="0.09" fill="#BE123C" />

    {{-- Kepala --}}
    <circle cx="{{ $x1 }}" cy="{{ $y1 }}" r="0.24" fill="#9F1239" />
    <circle cx="{{ $x1 - 0.08 }}" cy="{{ $y1 - 0.06 }}" r="0.045" fill="#ffffff" />
    <circle cx="{{ $x1 + 0.08 }}" cy="{{ $y1 - 0.06 }}" r="0.045" fill="#ffffff" />
    <circle cx="{{ $x1 - 0.08 }}" cy="{{ $y1 - 0.06 }}" r="0.02" fill="#1e1b1b" />
    <circle cx="{{ $x1 + 0.08 }}" cy="{{ $y1 - 0.06 }}" r="0.02" fill="#1e1b1b" />
    <path d="M {{ $x1 }} {{ $y1 + 0.16 }} l -0.05 0.12 M {{ $x1 }} {{ $y1 + 0.16 }} l 0.05 0.12" stroke="#7f1d1d" stroke-width="0.025" stroke-linecap="round" />
</g>
