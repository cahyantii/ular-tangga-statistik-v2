import { renderBoardGrid } from './board-renderer';

/**
 * Editor visual Petak: memakai renderBoardGrid() yang sama persis dengan
 * preview/admin-board-editor (tidak diubah), hanya mengganti onTileClick agar
 * membuka panel Alpine (lihat petak/index.blade.php) lewat CustomEvent,
 * bukan navigasi halaman penuh. Tidak ada state Alpine di sini supaya modul
 * ini tidak bergantung pada urutan registrasi Alpine.data()/Alpine.start().
 */

const NON_EDITABLE_JENIS = ['start', 'finish', 'tangga', 'ular'];

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
            if (NON_EDITABLE_JENIS.includes(tile.jenis_petak)) {
                window.dispatchEvent(new CustomEvent('petak:blocked', { detail: { tile } }));
                return;
            }

            window.dispatchEvent(new CustomEvent('petak:selected', { detail: { tile } }));
        },
    });

    window.dispatchEvent(new CustomEvent('petak:grid-ready', { detail: { config } }));
});
