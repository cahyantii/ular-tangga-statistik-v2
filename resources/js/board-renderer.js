/**
 * Modul render papan ular tangga (vanilla JS, dipakai bersama oleh editor visual
 * admin, halaman Preview Board, dan (nanti) papan gameplay live pada tahap Game
 * Engine — lihat catatan keputusan Tahap 16/9c: "reuse the same component").
 *
 * Layout boustrophedon (zig-zag): posisi 1 di kiri-bawah, baris genap (dari
 * bawah) bergerak kiri->kanan, baris ganjil bergerak kanan->kiri, meniru papan
 * ular tangga fisik.
 */

const TILE_COLORS = {
    start: '#059669',
    finish: '#7c3aed',
    biasa: '#f1f5f9',
    tangga: '#10b981',
    ular: '#e11d48',
    mystery: '#6366f1',
};

const LIGHT_TEXT_TILES = new Set(['start', 'finish', 'tangga', 'ular', 'mystery']);

export function computeCellPosition(posisi, jumlahKolom, totalRows) {
    const rowIndexFromBottom = Math.floor((posisi - 1) / jumlahKolom);
    const posInRow = (posisi - 1) % jumlahKolom;
    const isEvenRowFromBottom = rowIndexFromBottom % 2 === 0;
    const colIndex = isEvenRowFromBottom ? posInRow : jumlahKolom - 1 - posInRow;

    return {
        gridRow: totalRows - rowIndexFromBottom,
        gridColumn: colIndex + 1,
    };
}

/**
 * @param {HTMLElement} container
 * @param {{ jumlahKolom: number, jumlahPetak: number, petak: Array, konektor: Array, onTileClick: ?Function }} config
 */
export function renderBoardGrid(container, { jumlahKolom, jumlahPetak, petak, konektor = [], onTileClick = null }) {
    const totalRows = Math.ceil(jumlahPetak / jumlahKolom);
    const konektorByStart = new Map(konektor.map((k) => [k.posisi_awal, k]));

    container.innerHTML = '';
    container.style.display = 'grid';
    container.style.gridTemplateColumns = `repeat(${jumlahKolom}, minmax(0, 1fr))`;
    container.style.gridTemplateRows = `repeat(${totalRows}, minmax(0, 1fr))`;
    container.style.gap = '4px';

    petak.forEach((tile) => {
        const isActive = tile.is_active !== false;
        const effectiveJenis = isActive ? tile.jenis_petak : 'biasa';
        const { gridRow, gridColumn } = computeCellPosition(tile.posisi, jumlahKolom, totalRows);
        const cell = document.createElement('div');
        cell.style.gridRow = String(gridRow);
        cell.style.gridColumn = String(gridColumn);
        cell.style.backgroundColor = TILE_COLORS[effectiveJenis] ?? TILE_COLORS.biasa;
        cell.style.color = LIGHT_TEXT_TILES.has(effectiveJenis) ? '#ffffff' : '#1e293b';
        cell.style.position = 'relative';
        cell.style.opacity = isActive ? '1' : '0.45';
        cell.className = 'board-tile flex flex-col items-center justify-center rounded-md text-xs font-semibold p-1 min-h-[2.5rem]';
        cell.title = tile.label || tile.jenis_petak;
        cell.dataset.posisi = String(tile.posisi);

        const posisiEl = document.createElement('span');
        posisiEl.textContent = tile.posisi;
        cell.appendChild(posisiEl);

        if (!isActive) {
            const labelEl = document.createElement('span');
            labelEl.className = 'text-[10px] font-normal opacity-90';
            labelEl.textContent = 'nonaktif';
            cell.appendChild(labelEl);
        } else if (tile.jenis_petak !== 'biasa') {
            const labelEl = document.createElement('span');
            labelEl.className = 'text-[10px] font-normal opacity-90';
            labelEl.textContent = tile.jenis_petak;
            cell.appendChild(labelEl);
        }

        const konektorDariSini = konektorByStart.get(tile.posisi);
        if (konektorDariSini) {
            const arrowEl = document.createElement('span');
            arrowEl.className = 'text-[10px] font-normal opacity-90';
            arrowEl.textContent = `→ ${konektorDariSini.posisi_akhir}`;
            cell.appendChild(arrowEl);
        }

        if (typeof onTileClick === 'function') {
            cell.style.cursor = 'pointer';
            cell.addEventListener('click', () => onTileClick(tile));
        }

        container.appendChild(cell);
    });
}

/**
 * Render ulang penanda pion di atas grid yang sudah ada (tanpa membangun ulang
 * seluruh petak) — dipanggil setiap kali state permainan berubah.
 *
 * @param {HTMLElement} container
 * @param {Array<{id: number, posisi_pion: number, pawn_color: ?string, is_robot: boolean}>} pemain
 */
export function renderPawns(container, pemain) {
    container.querySelectorAll('.pawn-marker').forEach((el) => el.remove());

    const byPosisi = new Map();
    pemain.forEach((p) => {
        if (p.posisi_pion < 1) {
            return;
        }
        const list = byPosisi.get(p.posisi_pion) ?? [];
        list.push(p);
        byPosisi.set(p.posisi_pion, list);
    });

    byPosisi.forEach((players, posisi) => {
        const cell = container.querySelector(`[data-posisi="${posisi}"]`);
        if (!cell) {
            return;
        }

        players.forEach((p, index) => {
            const pawn = document.createElement('span');
            pawn.className = 'pawn-marker absolute h-3 w-3 rounded-full border-2 border-white shadow';
            pawn.style.backgroundColor = p.pawn_color || (p.is_robot ? '#475569' : '#1d4ed8');
            pawn.style.bottom = '2px';
            pawn.style.left = `${4 + index * 12}px`;
            pawn.title = p.is_robot ? 'Robot' : (p.nama ?? 'Pemain');
            cell.appendChild(pawn);
        });
    });
}
