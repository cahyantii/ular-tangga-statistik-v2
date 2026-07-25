import { CoordinateHelper } from './CoordinateHelper.js';
import { BoardGeometry } from './BoardGeometry.js';
import { SnakeRenderer } from './SnakeRenderer.js';
import { SlideRenderer } from './SlideRenderer.js';
import { LadderRenderer } from './LadderRenderer.js';
import { ParticleController } from './ParticleController.js';
import { AnimationController } from './AnimationController.js';

/**
 * Tema visual untuk konektor `jenis=ular`: 'ular' (SnakeRenderer, default)
 * atau 'perosotan' (SlideRenderer) - preferensi PURE CLIENT-SIDE (localStorage),
 * TIDAK ada kolom database/enum baru. `jenis` konektor di data tetap 'ular'
 * apa adanya untuk animasi gerak pion (lihat game-play.js) - cuma renderer
 * VISUAL yang berganti.
 */
const BOARD_THEME_KEY = 'ular-tangga-board-theme';

export function getBoardTheme() {
    try {
        return localStorage.getItem(BOARD_THEME_KEY) === 'perosotan' ? 'perosotan' : 'ular';
    } catch {
        return 'ular';
    }
}

export function setBoardTheme(theme) {
    try {
        localStorage.setItem(BOARD_THEME_KEY, theme === 'perosotan' ? 'perosotan' : 'ular');
    } catch {
        // localStorage tidak tersedia (mode privat dsb) - abaikan, fallback ke default 'ular'.
    }
}

/**
 * Titik masuk sistem visual papan v3 — orkestrator satu-satunya yang tahu
 * cara merakit BoardGeometry + SnakeRenderer + LadderRenderer + Particle/
 * AnimationController jadi satu papan utuh. Murni presentational: satu-
 * satunya "data" yang dibaca adalah data-attribute pada #game-board
 * (jumlah-kolom/total-rows/konektor — sudah disediakan board.blade.php,
 * sumbernya tetap database, bukan hardcode).
 *
 * PENTING (arsitektur v3): layer di bawah ini ("snakeLayer"/"ladderLayer")
 * HANYA wadah struktural kosong (position:absolute; inset:0; TIDAK
 * menggambar apa pun sendiri, tidak ada background/gambar/viewBox) — persis
 * seperti #pawn-layer yang sudah ada. Setiap ular/tangga adalah ELEMEN
 * OVERLAY INDIVIDUAL miliknya sendiri (dibangun oleh SnakeRenderer/
 * LadderRenderer, masing-masing dengan bounding box & <svg> LOKAL sendiri)
 * yang ditumpuk sebagai child biasa — BUKAN satu kanvas SVG besar yang
 * membentang seluruh papan yang isinya digambar lewat sistem koordinat
 * internal. Ini supaya ular/tangga betul-betul jadi "object di atas papan",
 * bukan "background papan".
 *
 * Diekspos ke window.BoardVisuals supaya game-play.js (yang sengaja TIDAK
 * mengimpor modul papan — lihat catatan di game-play.js) bisa memicu efek
 * reaktif (ular menggigit / tangga bersinar) lewat pemanggilan opsional
 * (optional chaining) tanpa dependensi build-time apa pun. Kalau modul ini
 * gagal/tidak termuat, gameplay tetap jalan normal tanpa efek visual ini.
 */
export class BoardRenderer {
    constructor(boardEl) {
        this.boardEl = boardEl;
        this.jumlahKolom = parseInt(boardEl.dataset.jumlahKolom, 10);
        this.totalRows = parseInt(boardEl.dataset.totalRows, 10);
        this.konektor = JSON.parse(boardEl.dataset.konektor || '[]');

        this.coord = new CoordinateHelper(this.jumlahKolom, this.totalRows);
        this.geometry = new BoardGeometry(this.coord);

        this.snakes = new Map();
        this.ladders = new Map();

        this._mountLayers();
        this._buildConnectors();

        this.animation = new AnimationController(boardEl);
        this.animation.observeVisibility();
    }

    _mountLayers() {
        // Wadah struktural KOSONG — sama polanya dengan #pawn-layer yang
        // sudah ada di board.blade.php. z-index: tile=1 (lihat tile.blade.php),
        // tangga & ular=20, pion=25 (SENGAJA di atas tangga/ular — pion
        // harus selalu terlihat walau sedang berdiri di kotak yang dilalui
        // jalur ular/tangga), confetti=30, dadu=40.
        this.ladderLayer = document.createElement('div');
        this.ladderLayer.className = 'pointer-events-none absolute inset-0';
        this.ladderLayer.style.zIndex = '20';

        this.snakeLayer = document.createElement('div');
        this.snakeLayer.className = 'pointer-events-none absolute inset-0';
        this.snakeLayer.style.zIndex = '20';

        this.particleLayer = document.createElement('div');
        this.particleLayer.className = 'pointer-events-none absolute inset-0';
        this.particleLayer.style.zIndex = '26';

        // Disisipkan SEBELUM #pawn-layer supaya urutan DOM tetap konsisten
        // dengan urutan z-index (pion tetap di atas lewat z-index eksplisit,
        // lihat game-play.js untuk z-index #pawn-layer).
        const pawnLayer = this.boardEl.querySelector('#pawn-layer');
        [this.ladderLayer, this.snakeLayer, this.particleLayer].forEach((el) => {
            this.boardEl.insertBefore(el, pawnLayer);
        });

        this.particles = new ParticleController(this.particleLayer);
    }

    _buildConnectors() {
        let snakeIndex = 0;
        let ladderIndex = 0;
        const theme = getBoardTheme();

        this.konektor.forEach((k) => {
            if (k.jenis === 'ular') {
                const RendererClass = theme === 'perosotan' ? SlideRenderer : SnakeRenderer;
                const renderer = new RendererClass(this.snakeLayer, this.geometry, {
                    start: k.posisi_awal,
                    end: k.posisi_akhir,
                    themeIndex: snakeIndex++,
                    cols: this.jumlahKolom,
                    rows: this.totalRows,
                });
                this.snakes.set(k.posisi_awal, renderer);
            } else {
                const renderer = new LadderRenderer(this.ladderLayer, this.geometry, {
                    start: k.posisi_awal,
                    end: k.posisi_akhir,
                    themeIndex: ladderIndex++,
                    cols: this.jumlahKolom,
                    rows: this.totalRows,
                });
                this.ladders.set(k.posisi_awal, renderer);
            }
        });
    }

    /** Reaksi ular saat pion mendarat di kotak `startPosisi` — no-op kalau bukan kepala ular. */
    async reactSnake(startPosisi) {
        const snake = this.snakes.get(startPosisi);
        if (!snake) return;
        const { left, top } = this.coord.cellCenterPercent(startPosisi);
        this.particles.burst(left, top, 'dust', 6);
        await snake.reactToLanding();
    }

    /** Reaksi tangga saat pion mulai naik dari kotak `startPosisi` — no-op kalau bukan kaki tangga. */
    async reactLadder(startPosisi) {
        const ladder = this.ladders.get(startPosisi);
        if (!ladder) return;
        const { left, top } = this.coord.cellCenterPercent(startPosisi);
        this.particles.burst(left, top, 'sparkle', 10);
        await ladder.reactToClimb();
    }
}

/** Auto-init untuk halaman gameplay (lihat resources/js/board-visuals.js). */
export function initBoardVisuals() {
    const boardEl = document.getElementById('game-board');
    if (!boardEl || boardEl.dataset.visualsMounted) {
        return null;
    }
    boardEl.dataset.visualsMounted = '1';

    const renderer = new BoardRenderer(boardEl);
    window.BoardVisuals = renderer;
    return renderer;
}
