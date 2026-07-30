import { createPawnElement } from './board/pawnElement.js';

/**
 * Preview papan admin memakai komponen papan yang PERSIS sama dengan yang
 * dilihat pemain (<x-game.board>, lihat preview.blade.php) - tidak ada
 * renderer terpisah lagi di sini. Satu-satunya hal tambahan yang murni
 * milik halaman ini adalah 1 pion contoh statis di petak Start, supaya
 * admin bisa lihat bentuk pion tanpa perlu sesi permainan sungguhan.
 */
document.addEventListener('DOMContentLoaded', () => {
    window.addEventListener('board-visuals-ready', (event) => {
        const renderer = event.detail;
        const pawnLayer = document.getElementById('pawn-layer');
        if (!renderer?.coord || !pawnLayer) return;

        const { left, top } = renderer.coord.cellCenterPercent(1);
        const el = createPawnElement({
            bg: '#1d4ed8',
            title: 'Contoh pion pemain',
            widthPercent: (100 / renderer.jumlahKolom) * 0.45,
            heightPercent: (100 / renderer.totalRows) * 0.8,
        });
        el.style.left = `${left}%`;
        el.style.top = `${top}%`;
        pawnLayer.appendChild(el);
    }, { once: true });
});
