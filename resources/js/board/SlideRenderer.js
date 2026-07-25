import { SLIDE_THEMES, SLIDE_THEME_NAMES } from './spriteData.js';
import { AnimationController } from './AnimationController.js';

const SVG_NS = 'http://www.w3.org/2000/svg';

/** Lebar KONSTAN sepanjang perosotan (satuan kotak) — beda dari ular, tidak ada taper anatomi kepala/leher. */
const WIDTH = 0.4;
const SEGMENTS = 24;

function cubicPoint(p0, p1, p2, p3, t) {
    const mt = 1 - t;
    const a = mt * mt * mt;
    const b = 3 * mt * mt * t;
    const c = 3 * mt * t * t;
    const d = t * t * t;
    return { x: a * p0.x + b * p1.x + c * p2.x + d * p3.x, y: a * p0.y + b * p1.y + c * p2.y + d * p3.y };
}

function cubicTangent(p0, p1, p2, p3, t) {
    const mt = 1 - t;
    const x = 3 * mt * mt * (p1.x - p0.x) + 6 * mt * t * (p2.x - p1.x) + 3 * t * t * (p3.x - p2.x);
    const y = 3 * mt * mt * (p1.y - p0.y) + 6 * mt * t * (p2.y - p1.y) + 3 * t * t * (p3.y - p2.y);
    const len = Math.max(Math.hypot(x, y), 0.0001);
    return { x: x / len, y: y / len };
}

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
 * Tema visual alternatif untuk konektor `jenis=ular` (lihat BoardRenderer -
 * dipilih lewat toggle tersimpan localStorage, BUKAN field database baru:
 * `jenis` konektor TETAP 'ular' apa adanya untuk data/animasi gerak pion,
 * cuma renderer VISUAL-nya yang diganti). Constructor & method publik
 * (`reactToLanding()`) SENGAJA identik kontraknya dengan SnakeRenderer
 * supaya bisa drop-in menggantikan tanpa mengubah BoardRenderer selain satu
 * baris pemilihan kelas.
 *
 * Bentuknya sengaja polos: satu tabung mulus lebar KONSTAN (bukan taper
 * anatomi ular), ujung membulat kedua sisi, TANPA wajah — mengikuti gaya
 * "slide" pada referensi (solid, playful, tanpa detail anatomi).
 */
export class SlideRenderer {
    /**
     * @param {HTMLElement} snakeLayer layer struktural (dipakai bersama SnakeRenderer)
     * @param {import('./BoardGeometry').BoardGeometry} geometry
     * @param {{start:number,end:number,themeIndex:number,cols:number,rows:number}} config
     */
    constructor(snakeLayer, geometry, config) {
        this.uid = `sl${uidCounter++}`;
        this.config = config;
        this.theme = SLIDE_THEMES[SLIDE_THEME_NAMES[config.themeIndex % SLIDE_THEME_NAMES.length]];
        this.path = geometry.connector(config.start, config.end);
        this._build(snakeLayer);
    }

    _build(snakeLayer) {
        const { cols, rows } = this.config;
        const { from, distance, px, py } = this.path;
        const anchorX = from.x + this.path.laneOffset.x;
        const anchorY = from.y + this.path.laneOffset.y;
        const angleDeg = (Math.atan2(this.path.dy, this.path.dx) * 180) / Math.PI;

        // Kurva S sama seperti SnakeRenderer supaya perosotan juga terlihat meliuk, bukan lurus kaku.
        const bendSign = px + py >= 0 ? 1 : -1;
        const bend = Math.min(distance * 0.32, 0.85);
        this.p0 = { x: 0, y: 0 };
        this.p1 = { x: bendSign * bend, y: distance * 0.33 };
        this.p2 = { x: -bendSign * bend, y: distance * 0.67 };
        this.p3 = { x: 0, y: distance };

        this.wrapWidth = WIDTH * 1.3 + Math.abs(bend) * 1.6;

        this.wrap = document.createElement('div');
        this.wrap.className = 'board-object slide-object';
        this.wrap.dataset.snakeStart = String(this.config.start);
        this.wrap.style.position = 'absolute';
        this.wrap.style.left = `${(anchorX / cols) * 100}%`;
        this.wrap.style.top = `${(anchorY / rows) * 100}%`;
        this.wrap.style.width = `${(this.wrapWidth / cols) * 100}%`;
        this.wrap.style.height = `${(distance / rows) * 100}%`;
        this.wrap.style.transform = `translate(-50%, 0) rotate(${(angleDeg - 90).toFixed(2)}deg)`;
        this.wrap.style.transformOrigin = '50% 0%';
        this.wrap.style.pointerEvents = 'none';
        this.wrap.style.zIndex = '20';

        this._buildSvg(distance);
        snakeLayer.appendChild(this.wrap);
    }

    _sampleAt(t) {
        const point = cubicPoint(this.p0, this.p1, this.p2, this.p3, t);
        const tangent = cubicTangent(this.p0, this.p1, this.p2, this.p3, t);
        const normal = { x: -tangent.y, y: tangent.x };
        return { point, tangent, normal };
    }

    _tubePath(distance) {
        const left = [];
        const right = [];
        const samples = [];
        for (let i = 0; i <= SEGMENTS; i++) {
            const t = i / SEGMENTS;
            const s = this._sampleAt(t);
            samples.push(s);
            const halfW = WIDTH / 2;
            left.push({ x: s.point.x + s.normal.x * halfW, y: s.point.y + s.normal.y * halfW });
            right.push({ x: s.point.x - s.normal.x * halfW, y: s.point.y - s.normal.y * halfW });
        }

        // Tutup bulat di kedua ujung (bukan cuma di moncong seperti ular) - perosotan simetris, tidak ada "kepala".
        const capAt = (sample, sign) => {
            const r = WIDTH / 2;
            const angleNormal = Math.atan2(sample.normal.y, sample.normal.x);
            const cap = [];
            for (let k = 0; k <= 10; k++) {
                const angle = angleNormal + sign * Math.PI * (k / 10);
                cap.push({ x: sample.point.x + r * Math.cos(angle), y: sample.point.y + r * Math.sin(angle) });
            }
            return cap;
        };

        const startCap = capAt(samples[0], 1);
        const endCap = capAt(samples[samples.length - 1], -1);

        const outline = [...startCap, ...right.slice(1, -1), ...endCap, ...left.slice(1, -1).reverse()];
        return `M ${outline.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')} Z`;
    }

    _buildSvg(distance) {
        const svg = svgEl('svg', {
            viewBox: `${-this.wrapWidth / 2} 0 ${this.wrapWidth} ${distance}`,
            preserveAspectRatio: 'none',
            class: 'board-object-svg slide-svg',
        });
        svg.style.position = 'absolute';
        svg.style.left = '0';
        svg.style.top = '0';
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.overflow = 'visible';

        const defs = svgEl('defs');
        const gradId = `slide-grad-${this.uid}`;
        const gradient = svgEl('linearGradient', { id: gradId, x1: '0%', y1: '0%', x2: '100%', y2: '0%' });
        gradient.appendChild(svgEl('stop', { offset: '0%', 'stop-color': this.theme.body[0] }));
        gradient.appendChild(svgEl('stop', { offset: '100%', 'stop-color': this.theme.body[1] }));
        defs.appendChild(gradient);
        svg.appendChild(defs);

        this.bodyGroup = svgEl('g', { class: 'slide-body-group' });

        this.bodyPath = svgEl('path', {
            d: this._tubePath(distance),
            fill: `url(#${gradId})`,
            stroke: this.theme.outline,
            'stroke-width': 0.032,
            'stroke-linejoin': 'round',
        });
        this.bodyGroup.appendChild(this.bodyPath);

        // Highlight glossy tipis mengikuti kurva - kesan plastik perosotan mengilap.
        const highlight = [];
        for (let i = 1; i < SEGMENTS; i++) {
            const t = i / SEGMENTS;
            const s = this._sampleAt(t);
            const off = WIDTH * 0.22;
            highlight.push({ x: s.point.x + s.normal.x * off, y: s.point.y + s.normal.y * off });
        }
        this.bodyGroup.appendChild(svgEl('path', {
            d: `M ${highlight.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')}`,
            fill: 'none',
            stroke: '#ffffff',
            'stroke-width': WIDTH * 0.14,
            'stroke-linecap': 'round',
            opacity: 0.4,
        }));

        svg.appendChild(this.bodyGroup);
        this.wrap.appendChild(svg);
    }

    /** Efek "perosotan aktif" saat pion mendarat/lewat - glow warna tema, konsisten dengan SnakeRenderer.reactToLanding(). */
    async reactToLanding() {
        if (!this.bodyGroup) return;
        this.bodyGroup.style.setProperty('--bite-glow', this.theme.glow);
        await AnimationController.pulse(this.bodyGroup, 'slide-glow', 650);
    }

    destroy() {
        // Tidak ada timer idle (perosotan statis total) - no-op, dipertahankan supaya kontrak sama dengan SnakeRenderer.destroy().
    }
}
