{{-- Tangga: dua rel + anak tangga, digambar murni dari titik pusat kotak posisi_awal & posisi_akhir (dari database, tidak hardcode). --}}
@props(['from', 'to'])

@php
    $x1 = $from['col'] - 0.5;
    $y1 = $from['row'] - 0.5;
    $x2 = $to['col'] - 0.5;
    $y2 = $to['row'] - 0.5;

    $dx = $x2 - $x1;
    $dy = $y2 - $y1;
    $len = max(sqrt($dx ** 2 + $dy ** 2), 0.001);
    $ux = $dx / $len;
    $uy = $dy / $len;
    $px = -$uy;
    $py = $ux;

    $offset = 0.13;
    $rail1 = [$x1 + $px * $offset, $y1 + $py * $offset, $x2 + $px * $offset, $y2 + $py * $offset];
    $rail2 = [$x1 - $px * $offset, $y1 - $py * $offset, $x2 - $px * $offset, $y2 - $py * $offset];
    $rungCount = max((int) round($len / 0.45), 2);
@endphp

<g class="ladder-group">
    <line x1="{{ $rail1[0] }}" y1="{{ $rail1[1] }}" x2="{{ $rail1[2] }}" y2="{{ $rail1[3] }}" stroke="#92400E" stroke-width="0.1" stroke-linecap="round" />
    <line x1="{{ $rail2[0] }}" y1="{{ $rail2[1] }}" x2="{{ $rail2[2] }}" y2="{{ $rail2[3] }}" stroke="#92400E" stroke-width="0.1" stroke-linecap="round" />

    @for ($i = 0; $i <= $rungCount; $i++)
        @php
            $t = $i / $rungCount;
            $rx1 = $rail1[0] + ($rail1[2] - $rail1[0]) * $t;
            $ry1 = $rail1[1] + ($rail1[3] - $rail1[1]) * $t;
            $rx2 = $rail2[0] + ($rail2[2] - $rail2[0]) * $t;
            $ry2 = $rail2[1] + ($rail2[3] - $rail2[1]) * $t;
        @endphp
        <line x1="{{ $rx1 }}" y1="{{ $ry1 }}" x2="{{ $rx2 }}" y2="{{ $ry2 }}" stroke="#D97706" stroke-width="0.07" stroke-linecap="round" />
    @endfor
</g>
