/**
 * Entry point Vite untuk sistem visual papan v2 (ular/tangga procedural
 * SVG). Lihat resources/js/board/BoardRenderer.js untuk orkestrasinya.
 *
 * Dimuat terpisah dari game-play.js (bukan diimpor olehnya) supaya gagal-
 * muat modul ini tidak pernah menjabat gameplay — game-play.js memanggil
 * efek reaktifnya lewat `window.BoardVisuals?.xxx()` (optional chaining).
 */
import { initBoardVisuals, getBoardTheme, setBoardTheme } from './board/BoardRenderer.js';

document.addEventListener('DOMContentLoaded', () => {
    initBoardVisuals();
    initBoardThemeToggle();
});

/**
 * Toggle "Ular"/"Perosotan" (lihat #board-theme-toggle di show.blade.php) -
 * cuma tandai tema aktif di UI & simpan preferensi, lalu reload halaman
 * supaya BoardRenderer di-init ulang dengan renderer yang benar (papan cuma
 * dibangun sekali per load, tidak perlu dukung rebuild live).
 */
function initBoardThemeToggle() {
    const wrap = document.getElementById('board-theme-toggle');
    if (!wrap) return;

    const buttons = wrap.querySelectorAll('[data-board-theme]');
    const active = getBoardTheme();

    buttons.forEach((btn) => {
        btn.classList.toggle('is-active', btn.dataset.boardTheme === active);
        btn.addEventListener('click', () => {
            if (btn.dataset.boardTheme === getBoardTheme()) return;
            setBoardTheme(btn.dataset.boardTheme);
            window.location.reload();
        });
    });
}
