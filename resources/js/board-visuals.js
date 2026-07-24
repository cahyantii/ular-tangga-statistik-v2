/**
 * Entry point Vite untuk sistem visual papan v2 (ular/tangga procedural
 * SVG). Lihat resources/js/board/BoardRenderer.js untuk orkestrasinya.
 *
 * Dimuat terpisah dari game-play.js (bukan diimpor olehnya) supaya gagal-
 * muat modul ini tidak pernah menjabat gameplay — game-play.js memanggil
 * efek reaktifnya lewat `window.BoardVisuals?.xxx()` (optional chaining).
 */
import { initBoardVisuals } from './board/BoardRenderer.js';

document.addEventListener('DOMContentLoaded', () => {
    initBoardVisuals();
});
