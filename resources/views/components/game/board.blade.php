{{--
    Papan permainan sepenuhnya dinamis: jumlah kotak, jenis kotak, posisi ular
    & tangga semua berasal dari $papan->petak / $papan->papanKonektor (database).
    Tidak ada angka/posisi hardcode — admin mengubah data, tampilan otomatis berubah.

    Layout kotak memakai algoritma boustrophedon (zig-zag) yang sama dengan
    board-renderer.js (dipakai admin board editor) supaya papan gameplay dan
    preview admin selalu konsisten.
--}}
@props(['papan'])

@php
    $jumlahKolom = max((int) $papan->jumlah_kolom, 1);
    $jumlahPetak = (int) $papan->jumlah_petak;
    $totalRows = max((int) ceil($jumlahPetak / $jumlahKolom), 1);

    $cellPosition = static function (int $posisi) use ($jumlahKolom, $totalRows): array {
        $rowIndexFromBottom = intdiv($posisi - 1, $jumlahKolom);
        $posInRow = ($posisi - 1) % $jumlahKolom;
        $isEvenRowFromBottom = $rowIndexFromBottom % 2 === 0;
        $colIndex = $isEvenRowFromBottom ? $posInRow : $jumlahKolom - 1 - $posInRow;

        return ['row' => $totalRows - $rowIndexFromBottom, 'col' => $colIndex + 1];
    };

    $positions = [];
    foreach ($papan->petak as $t) {
        $positions[$t->posisi] = $cellPosition($t->posisi);
    }

    $konektorByStart = $papan->papanKonektor->keyBy('posisi_awal');
@endphp

<div
    id="game-board"
    data-jumlah-kolom="{{ $jumlahKolom }}"
    data-total-rows="{{ $totalRows }}"
    data-jumlah-petak="{{ $jumlahPetak }}"
    class="relative rounded-2xl bg-gradient-to-b from-app-bg to-white p-1.5 shadow-inner sm:p-2.5"
    style="display: grid; grid-template-columns: repeat({{ $jumlahKolom }}, minmax(0, 1fr)); grid-template-rows: repeat({{ $totalRows }}, minmax(0, 1fr)); aspect-ratio: {{ $jumlahKolom }} / {{ $totalRows }};"
>
    {{-- Ular & tangga: SVG selaras 1:1 dengan grid via viewBox (responsif tanpa JS) --}}
    <svg
        class="pointer-events-none absolute inset-0 h-full w-full"
        viewBox="0 0 {{ $jumlahKolom }} {{ $totalRows }}"
        preserveAspectRatio="none"
        style="grid-row: 1 / -1; grid-column: 1 / -1;"
    >
        @foreach ($papan->papanKonektor as $k)
            @php
                $from = $positions[$k->posisi_awal] ?? null;
                $to = $positions[$k->posisi_akhir] ?? null;
            @endphp
            @if ($from && $to)
                @if ($k->jenis->value === 'ular')
                    <x-game.snake :from="$from" :to="$to" />
                @else
                    <x-game.ladder :from="$from" :to="$to" />
                @endif
            @endif
        @endforeach
    </svg>

    {{-- Pion pemain: dirender & dianimasikan JS (posisi berubah tanpa reload) --}}
    <div id="pawn-layer" class="pointer-events-none absolute inset-0 z-20" style="grid-row: 1 / -1; grid-column: 1 / -1;"></div>

    {{-- Kotak papan --}}
    @foreach ($papan->petak as $tile)
        <x-game.tile
            :tile="$tile"
            :row="$positions[$tile->posisi]['row']"
            :col="$positions[$tile->posisi]['col']"
            :connector="$konektorByStart->get($tile->posisi)"
        />
    @endforeach
</div>

{{-- Legenda jenis kotak --}}
<div class="mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-slate-500">
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-primary-500"></span> Soal</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-accent-500"></span> Bonus</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-violet-500"></span> Mystery</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-600"></span> Ular</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-secondary-200"></span> Tangga</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-rose-300"></span> Penalti</span>
    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-gradient-to-br from-violet-600 to-amber-400"></span> Finish</span>
</div>
