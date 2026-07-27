import { LADDER_THEMES, LADDER_THEME_NAMES } from './spriteData.js';
import { AnimationController } from './AnimationController.js';

const SVG_NS = 'http://www.w3.org/2000/svg';

/**
 * Jarak antar 2 rel (satuan kotak) & jarak antar anak tangga — tetap,
 * panjang keseluruhan otomatis mengikuti jarak asal->tujuan.
 *
 * Gaya v8 "flat/playful": rel & anak tangga tebal & jelas TANPA end-cap
 * bulat di ujung (percobaan v7 dengan end-cap terlihat terlalu blocky/kaku
 * dibanding referensi — cukup `stroke-linecap: round` pada garis rel/rung
 * sendiri untuk ujung yang membulat natural) — guratan serat kayu v6 tetap
 * dihapus (terlalu halus untuk gaya flat, banyak node tanpa payoff visual).
 */
const RAIL_GAP = 0.36;
const RUNG_SPACING = 0.20;
const RAIL_THICKNESS = 0.05;
const RUNG_THICKNESS = 0.065;
/** Outline tipis di bawah tiap rel/anak tangga — supaya tetap terbaca kontras di kotak putih/terang (bukan cuma drop-shadow). */
const OUTLINE_EXTRA = 0.055;

function svgEl(tag, attrs = {}) {
    const el = document.createElementNS(SVG_NS, tag);
    Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
    return el;
}

function fmt(n) {
    return n.toFixed(3);
}

let uidCounter = 0;

/**
 * Satu tangga = SATU OBJECT OVERLAY individual (BUKAN bagian dari kanvas
 * yang membentang seluruh papan) — anchor di cell-center kotak bawah (kaki)
 * dan memanjang ke cell-center kotak atas (ujung) lewat rotate() di sekitar
 * titik anchor itu. 100% SVG (tidak ada gambar sama sekali), viewBox LOKAL
 * (hanya untuk elemen ini sendiri) — 2 rel + anak tangga, panjang & jumlah
 * anak tangga DIHITUNG OTOMATIS dari jarak kotak asal->tujuan, jadi tidak
 * pernah blur/pecah/stretch pada jarak berapa pun.
 */
export class LadderRenderer {
    /**
     * @param {HTMLElement} ladderLayer layer struktural (tidak menggambar apa pun sendiri)
     * @param {import('./BoardGeometry').BoardGeometry} geometry
     * @param {{start:number,end:number,themeIndex:number,cols:number,rows:number}} config
     */
    constructor(ladderLayer, geometry, config) {
        this.uid = `ld${uidCounter++}`;
        this.config = config;
        this.themeName = LADDER_THEME_NAMES[config.themeIndex % LADDER_THEME_NAMES.length];
        this.theme = LADDER_THEMES[this.themeName];
        this.path = geometry.connector(config.start, config.end);
        this._build(ladderLayer);
    }

    /** Satu garis + outline tipis di bawahnya (dua <line> ditumpuk dalam satu <g>). */
    _strokedLine(x1, y1, x2, y2, strokeColor, thickness, className) {
        const outline = svgEl('line', {
            x1, y1, x2, y2,
            stroke: this.theme.outline,
            'stroke-width': thickness + OUTLINE_EXTRA,
            'stroke-linecap': 'round',
            class: `${className}-outline`,
        });
        const main = svgEl('line', {
            x1, y1, x2, y2,
            stroke: strokeColor,
            'stroke-width': thickness,
            'stroke-linecap': 'round',
            class: className,
        });
        const g = svgEl('g');
        g.appendChild(outline);
        g.appendChild(main);
        return g;
    }

    _build(ladderLayer) {
        const { cols, rows } = this.config;
        const { from, distance } = this.path;
        const anchorX = from.x + this.path.laneOffset.x;
        const anchorY = from.y + this.path.laneOffset.y;
        const angleDeg = (Math.atan2(this.path.dy, this.path.dx) * 180) / Math.PI;

        // Wrapper: object overlay individual, sama seperti ular — lebar =
        // jarak antar rel (RAIL_GAP), tinggi = jarak asal->tujuan, anchor
        // atas-tengah PAS di cell-center kotak bawah (kaki tangga).
        this.wrap = document.createElement('div');
        this.wrap.className = 'board-object ladder-object';
        this.wrap.dataset.ladderStart = String(this.config.start);
        this.wrap.style.position = 'absolute';
        this.wrap.style.left = `${(anchorX / cols) * 100}%`;
        this.wrap.style.top = `${(anchorY / rows) * 100}%`;
        this.wrap.style.width = `${(RAIL_GAP / cols) * 100}%`;
        this.wrap.style.height = `${(distance / rows) * 100}%`;
        this.wrap.style.transform = `translate(-50%, 0) rotate(${(angleDeg - 90).toFixed(2)}deg)`;
        this.wrap.style.transformOrigin = '50% 0%';
        this.wrap.style.pointerEvents = 'none';
        this.wrap.style.zIndex = '20';

        // Tangga statis (tidak ada gerakan idle) - this.idleEl dipertahankan
        // sebagai wrapper struktural saja untuk _buildSvg().
        this.idleEl = document.createElement('div');
        this.idleEl.className = 'ladder-idle';
        this.idleEl.style.width = '100%';
        this.idleEl.style.height = '100%';
        this.wrap.appendChild(this.idleEl);

        this._buildSvg(distance);

        ladderLayer.appendChild(this.wrap);
    }

    /** Rel & anak tangga: SVG kecil, viewBox LOKAL (0,0 di anchor kaki, y bertambah ke arah ujung). */
    _buildSvg(distance) {
        const svg = svgEl('svg', {
            viewBox: `${-RAIL_GAP / 2} 0 ${RAIL_GAP} ${distance}`,
            preserveAspectRatio: 'none',
            class: 'board-object-svg ladder-svg',
        });
        svg.style.position = 'absolute';
        svg.style.left = '0';
        svg.style.top = '0';
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.overflow = 'visible';

        const defs = svgEl('defs');
        const gradId = `ladder-grad-${this.uid}`;
        const gradient = svgEl('linearGradient', { id: gradId, x1: '0%', y1: '0%', x2: '100%', y2: '0%' });
        gradient.appendChild(svgEl('stop', { offset: '0%', 'stop-color': this.theme.rail[0] }));
        gradient.appendChild(svgEl('stop', { offset: '100%', 'stop-color': this.theme.rail[1] }));
        defs.appendChild(gradient);
        svg.appendChild(defs);

        this.group = svgEl('g', { class: 'ladder-group' });

        const railX = RAIL_GAP / 2;
        const railA = this._strokedLine(railX, 0, railX, distance, `url(#${gradId})`, RAIL_THICKNESS, 'ladder-rail-line');
        const railB = this._strokedLine(-railX, 0, -railX, distance, `url(#${gradId})`, RAIL_THICKNESS, 'ladder-rail-line');

        const rungHalf = RAIL_GAP / 2 - RAIL_THICKNESS * 0.3;
        const rungCount = Math.max(2, Math.round(distance / RUNG_SPACING));
        for (let i = 1; i < rungCount; i++) {
            const cy = (distance * i) / rungCount;
            const rung = this._strokedLine(-rungHalf, cy, rungHalf, cy, this.theme.rail[0], RUNG_THICKNESS, 'ladder-rung-line');
            this.group.appendChild(rung);
        }

        this.group.appendChild(railA);
        this.group.appendChild(railB);

        // Highlight glossy tipis di satu sisi tiap rel — kesan dowel kayu
        // bulat memantulkan cahaya, bukan pipih.
        this.group.appendChild(this._buildRailHighlight(railX, distance));
        this.group.appendChild(this._buildRailHighlight(-railX, distance));

        svg.appendChild(this.group);
        this.wrap.querySelector('.ladder-idle').appendChild(svg);
    }

    /** Highlight glossy tipis di sisi kiri tiap rel — kesan dowel bulat memantulkan cahaya. */
    _buildRailHighlight(railX, distance) {
        const offset = RAIL_THICKNESS * 0.24;
        return svgEl('line', {
            x1: fmt(railX - offset), y1: fmt(distance * 0.05),
            x2: fmt(railX - offset), y2: fmt(distance * 0.95),
            stroke: '#ffffff', 'stroke-width': fmt(RAIL_THICKNESS * 0.2),
            'stroke-linecap': 'round', opacity: 0.5,
        });
    }

    /**
     * Efek "tangga aktif" saat pion naik lewatnya: glow + sedikit scale, lalu
     * kembali normal. Best-effort, tidak pernah error.
     */
    async reactToClimb() {
        if (!this.group) return;
        await AnimationController.pulse(this.group, 'ladder-climb-active', 650);
    }
}
