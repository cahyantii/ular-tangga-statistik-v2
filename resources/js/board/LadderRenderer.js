import { LADDER_THEMES, LADDER_THEME_NAMES } from './spriteData.js';
import { AnimationController } from './AnimationController.js';

const SVG_NS = 'http://www.w3.org/2000/svg';

/**
 * Jarak antar 2 rel (satuan kotak) & jarak antar anak tangga — tetap,
 * panjang keseluruhan otomatis mengikuti jarak asal->tujuan.
 *
 * Percobaan sebelumnya (RUNG_THICKNESS 0.13, sama tebal dengan rel, dengan
 * RUNG_SPACING lebar 0.46) membuat anak tangganya sedikit & tebal seperti
 * batang, sehingga tangganya terlihat seperti bentuk "H"/kursi, bukan
 * tangga sungguhan. Rel tetap tebal (RAIL_THICKNESS) untuk kesan kokoh,
 * tapi anak tangga dibuat LEBIH TIPIS dari rel dan LEBIH RAPAT (banyak
 * anak tangga kecil) - itulah yang membuat sebuah tangga "terbaca" sebagai
 * tangga, bukan cuma dua batang dengan sedikit palang.
 */
const RAIL_GAP = 0.46;
const RUNG_SPACING = 0.26;
const RAIL_THICKNESS = 0.13;
const RUNG_THICKNESS = 0.07;
/** Outline tipis di bawah tiap rel/anak tangga — supaya tetap terbaca kontras di kotak putih/terang (bukan cuma drop-shadow). */
const OUTLINE_EXTRA = 0.05;

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

        // Tangga sengaja statis (lihat .ladder-idle di app.css - tidak ada
        // lagi animasi bob di sana) - this.idleEl dipertahankan sebagai
        // wrapper struktural saja (dibutuhkan _buildSvg() untuk menyisipkan
        // <svg>-nya), tanpa delay/animasi apa pun.
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

        // Efek 3D tipis: garis highlight sempit di satu sisi tiap rel (kayu
        // membulat, sisi yang menghadap cahaya lebih terang) + guratan serat
        // kayu horizontal renggang di sepanjang rel - keduanya dekoratif
        // murni, tidak mengubah geometri/posisi rel sama sekali.
        this.group.appendChild(this._buildRailBevel(railX, distance));
        this.group.appendChild(this._buildRailBevel(-railX, distance));
        this.group.appendChild(this._buildGrain(railX, distance));
        this.group.appendChild(this._buildGrain(-railX, distance));

        svg.appendChild(this.group);
        this.wrap.querySelector('.ladder-idle').appendChild(svg);
    }

    /** Highlight tipis di sisi kiri tiap rel — kesan dowel kayu bulat, bukan pipih. */
    _buildRailBevel(railX, distance) {
        const offset = RAIL_THICKNESS * 0.22;
        return svgEl('line', {
            x1: fmt(railX - offset), y1: fmt(distance * 0.04),
            x2: fmt(railX - offset), y2: fmt(distance * 0.96),
            stroke: this.theme.rail[0], 'stroke-width': fmt(RAIL_THICKNESS * 0.22),
            'stroke-linecap': 'round', opacity: 0.55,
        });
    }

    /** Guratan serat kayu — tanda pendek melintang rel, renggang & redup. */
    _buildGrain(railX, distance) {
        const group = svgEl('g', { opacity: 0.4 });
        const spacing = 0.24;
        const count = Math.max(3, Math.round(distance / spacing));
        for (let i = 0; i < count; i++) {
            const cy = distance * ((i + 0.5) / count);
            const w = RAIL_THICKNESS * 0.6;
            group.appendChild(svgEl('line', {
                x1: fmt(railX - w / 2), y1: fmt(cy),
                x2: fmt(railX + w / 2), y2: fmt(cy + RAIL_THICKNESS * 0.18),
                stroke: this.theme.grain, 'stroke-width': 0.012, 'stroke-linecap': 'round',
            }));
        }
        return group;
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
