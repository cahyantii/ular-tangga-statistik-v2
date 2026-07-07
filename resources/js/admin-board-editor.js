import { renderBoardGrid } from './board-renderer';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('board-editor-data');
    if (!root) {
        return;
    }

    const config = JSON.parse(root.dataset.board);
    const container = document.getElementById('board-grid');
    const editBaseUrl = root.dataset.editBaseUrl;

    renderBoardGrid(container, {
        jumlahKolom: config.jumlah_kolom,
        jumlahPetak: config.jumlah_petak,
        petak: config.petak,
        konektor: config.konektor,
        onTileClick: editBaseUrl
            ? (tile) => {
                if (['start', 'finish', 'tangga', 'ular'].includes(tile.jenis_petak)) {
                    return;
                }
                window.location.href = `${editBaseUrl}/${tile.id}/edit`;
            }
            : null,
    });
});
