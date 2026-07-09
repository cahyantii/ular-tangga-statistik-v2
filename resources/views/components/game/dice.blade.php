{{--
    Dadu 3D (CSS cube, 6 wajah nyata) — nilai akhir ditentukan dengan memutar
    kubus (#dice-3d) lewat JS sampai wajah yang benar menghadap depan, dipicu
    SETELAH server mengembalikan nilai dadu yang sah (game-play.js).
--}}
@props(['id' => 'dice-3d'])

@php
    $pipMap = [
        1 => [[2, 2]],
        2 => [[1, 1], [3, 3]],
        3 => [[1, 1], [2, 2], [3, 3]],
        4 => [[1, 1], [1, 3], [3, 1], [3, 3]],
        5 => [[1, 1], [1, 3], [2, 2], [3, 1], [3, 3]],
        6 => [[1, 1], [1, 3], [2, 1], [2, 3], [3, 1], [3, 3]],
    ];
@endphp

<div class="dice-3d-wrap mx-auto h-20 w-20 sm:h-24 sm:w-24" id="{{ $id }}-wrap">
    <div class="dice-3d" id="{{ $id }}">
        @foreach ($pipMap as $value => $pips)
            <div class="dice-face dice-face-{{ $value }}">
                @for ($r = 1; $r <= 3; $r++)
                    @for ($c = 1; $c <= 3; $c++)
                        @if (in_array([$r, $c], $pips, true))
                            <span class="dice-pip" style="grid-row: {{ $r }}; grid-column: {{ $c }};"></span>
                        @else
                            <span style="grid-row: {{ $r }}; grid-column: {{ $c }};"></span>
                        @endif
                    @endfor
                @endfor
            </div>
        @endforeach
    </div>
</div>
