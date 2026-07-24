/**
 * Matematika posisi kotak papan (layout boustrophedon/zig-zag) — SATU-SATUNYA
 * sumber kebenaran dipakai bersama oleh seluruh sistem visual papan
 * (BoardRenderer/SnakeRenderer/LadderRenderer) maupun animasi pion di
 * game-play.js, supaya tidak ada dua rumus konversi posisi->koordinat yang
 * bisa saling meleset.
 *
 * Semua satuan di sini adalah UNIT KOTAK (1 unit = 1 kotak papan), bukan
 * pixel ataupun persen — pemanggil yang mengonversi ke unit yang mereka
 * butuhkan (SVG viewBox unit = unit kotak apa adanya, CSS % = dibagi
 * jumlahKolom/totalRows). Ini membuat geometrinya independen resolusi layar.
 */
export class CoordinateHelper {
    constructor(jumlahKolom, totalRows) {
        this.jumlahKolom = jumlahKolom;
        this.totalRows = totalRows;
    }

    /** Baris (dari atas, 1-indexed) & kolom (1-indexed) satu posisi kotak. */
    cellRowCol(posisi) {
        const rowIndexFromBottom = Math.floor((posisi - 1) / this.jumlahKolom);
        const posInRow = (posisi - 1) % this.jumlahKolom;
        const isEvenRowFromBottom = rowIndexFromBottom % 2 === 0;
        const colIndex = isEvenRowFromBottom ? posInRow : this.jumlahKolom - 1 - posInRow;

        return { row: this.totalRows - rowIndexFromBottom, col: colIndex + 1 };
    }

    /** Titik tengah kotak, satuan KOTAK (mis. kotak ke-1 -> x=0.5). */
    cellCenterUnit(posisi) {
        const clamped = Math.max(1, posisi);
        const { row, col } = this.cellRowCol(clamped);
        return { x: col - 0.5, y: row - 0.5 };
    }

    /** Sama seperti cellCenterUnit tapi dalam PERSEN (0-100) terhadap board. */
    cellCenterPercent(posisi) {
        const { x, y } = this.cellCenterUnit(posisi);
        return { left: (x / this.jumlahKolom) * 100, top: (y / this.totalRows) * 100 };
    }
}
