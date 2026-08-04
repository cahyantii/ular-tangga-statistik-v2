{{--
    Papan permainan sepenuhnya dinamis: jumlah kotak, jenis kotak, posisi ular
    & tangga semua berasal dari $papan->petak / $papan->papanKonektor (database).
    Tidak ada angka/posisi hardcode — admin mengubah data, tampilan otomatis berubah.

    Layout kotak memakai algoritma boustrophedon (zig-zag) yang sama dengan
    board-renderer.js (dipakai admin board editor) supaya papan gameplay dan
    preview admin selalu konsisten.

    ------------------------------------------------------------------------
    ULAR & TANGGA: sistem visual v3 — 100% dirender CLIENT-SIDE lewat
    resources/js/board/BoardRenderer.js. Setiap ular/tangga adalah OBJECT
    OVERLAY INDIVIDUAL (bounding box & <svg> lokal sendiri per konektor,
    posisi dihitung dari cell-center kotak asal/tujuan) — BUKAN satu kanvas/
    background yang membentang seluruh papan. Blade di sini HANYA
    menyediakan data mentah ($konektorForJs, sudah ada) lewat data-konektor
    pada #game-board — sama persis data yang sudah dipakai game-play.js
    untuk menganimasikan jalur pion. Tidak ada geometri/sprite/posisi
    ular-tangga dihitung di PHP lagi.
    ------------------------------------------------------------------------
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

    // Dibaca game-play.js (animasi jalur pion) DAN BoardRenderer.js (menggambar
    // ular/tangga) — satu sumber data yang sama, murni dari database.
    $konektorForJs = $papan->papanKonektor->map(fn ($k) => [
        'posisi_awal' => $k->posisi_awal,
        'posisi_akhir' => $k->posisi_akhir,
        'jenis' => $k->jenis->value,
    ])->values();
@endphp

<div
    id="game-board"
    data-jumlah-kolom="{{ $jumlahKolom }}"
    data-total-rows="{{ $totalRows }}"
    data-jumlah-petak="{{ $jumlahPetak }}"
    data-konektor="{{ json_encode($konektorForJs) }}"
    class="relative z-[1] overflow-hidden rounded-2xl bg-gradient-to-b from-app-bg to-white dark:from-dark-bg dark:to-dark-surface p-1.5 shadow-inner sm:p-2.5"
    style="display: grid; grid-template-columns: repeat({{ $jumlahKolom }}, minmax(0, 1fr)); grid-template-rows: repeat({{ $totalRows }}, minmax(0, 1fr)); aspect-ratio: {{ $jumlahKolom }} / {{ $totalRows }};"
>
    {{-- Kotak papan (di bawah ular & tangga — lihat catatan layering di BoardRenderer.js) --}}
    @foreach ($papan->petak as $tile)
        <x-game.tile
            :tile="$tile"
            :row="$positions[$tile->posisi]['row']"
            :col="$positions[$tile->posisi]['col']"
            :connector="$konektorByStart->get($tile->posisi)"
        />
    @endforeach

    {{--
        Ular & tangga: BoardRenderer.js menyisipkan 2 wadah struktural KOSONG
        di sini secara dinamis (satu untuk tangga, satu untuk ular — masing-
        masing cuma position:absolute;inset:0, tidak menggambar apa pun
        sendiri) tepat SEBELUM #pawn-layer, lalu mengisinya dengan object
        overlay individual per konektor. Urutan layer: petak=10,
        tangga/ular=20, pion=25, particle=26. Pion SENGAJA di atas tangga/
        ular (lihat resources/js/board/BoardRenderer.js::_mountLayers())
        supaya token pemain tidak pernah tertutup jalur ular/tangga yang
        lewat di kotak yang sama.
    --}}
    <div id="pawn-layer" class="pointer-events-none absolute inset-0 z-[25]" style="grid-row: 1 / -1; grid-column: 1 / -1;"></div>
</div>

