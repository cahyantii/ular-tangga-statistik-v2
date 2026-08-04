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

<div class="dice-3d-wrap mx-auto h-20 w-20 sm:h-24 sm:w-24 relative" id="{{ $id }}-wrap">
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

    {{-- Disabled Overlay SVG --}}
    <div id="{{ $id }}-disabled-overlay" class="absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-slate-100/60 dark:bg-slate-900/60 backdrop-blur-[2px] transition-all duration-300">
        <svg class="h-8 w-8 text-slate-500 dark:text-slate-400 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
    </div>
</div>
