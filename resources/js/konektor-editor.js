import { renderBoardGrid } from './board-renderer';

/**
 * Editor visual Konektor: memakai renderBoardGrid() yang sama persis dengan
 * editor Petak/preview (tidak diubah). Semua logika pemilihan asal/tujuan,
 * validasi, dan preview kurva SVG ada di komponen Alpine (lihat
 * konektor/index.blade.php) — modul ini hanya merender grid dan meneruskan
 * klik petak lewat CustomEvent, supaya tidak bergantung pada urutan
 * registrasi Alpine.data()/Alpine.start().
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('board-editor-data');
    if (!root) {
        return;
    }

    const config = JSON.parse(root.dataset.board);
    const container = document.getElementById('board-grid');

    renderBoardGrid(container, {
        jumlahKolom: config.jumlah_kolom,
        jumlahPetak: config.jumlah_petak,
        petak: config.petak,
        konektor: config.konektor,
        onTileClick: (tile) => {
            window.dispatchEvent(new CustomEvent('konektor:tile-click', { detail: { tile } }));
        },
    });

    window.dispatchEvent(new CustomEvent('konektor:grid-ready', { detail: { config } }));
});
