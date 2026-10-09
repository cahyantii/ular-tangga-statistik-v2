import { renderBoardGrid } from './board-renderer';
import { CoordinateHelper } from './board/CoordinateHelper.js';
import { BoardGeometry } from './board/BoardGeometry.js';
import { SnakeRenderer } from './board/SnakeRenderer.js';
import { LadderRenderer } from './board/LadderRenderer.js';

/**
 * Editor visual Konektor: memakai renderBoardGrid() yang sama persis dengan
 * editor Petak/preview (tidak diubah). Semua logika pemilihan asal/tujuan,
 * validasi, dan preview kurva SVG ada di komponen Alpine (lihat
 * konektor/index.blade.php) — modul ini hanya merender grid dan meneruskan
 * klik petak lewat CustomEvent, supaya tidak bergantung pada urutan
 * registrasi Alpine.data()/Alpine.start().
 *
 * `window.BoardVisualsKit` mengekspos kelas render ular/tangga "asli" yang
 * sama persis dipakai papan gameplay sungguhan (lihat board/BoardRenderer.js)
 * supaya komponen Alpine di konektor/index.blade.php (masih classic <script>,
 * BUKAN modul ES — lihat catatan di sana soal urutan Alpine.start()) bisa
 * memakainya tanpa duplikasi kode gambar ular/tangga yang lebih sederhana
 * (keluhan admin: versi lama tidak semenarik logo). Aman dari race condition
 * karena baru benar-benar dipakai di dalam listener `konektor:grid-ready`,
 * yang terjadi jauh setelah modul ini selesai dieksekusi.
 */
window.BoardVisualsKit = { CoordinateHelper, BoardGeometry, SnakeRenderer, LadderRenderer };
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
