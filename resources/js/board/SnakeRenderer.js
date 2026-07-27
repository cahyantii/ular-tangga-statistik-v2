import { SNAKE_THEMES, SNAKE_THEME_NAMES, TONGUE_COLOR } from './spriteData.js';
import { AnimationController } from './AnimationController.js';

const SVG_NS = 'http://www.w3.org/2000/svg';

/** Ketebalan badan (satuan kotak) — KONSTAN dari leher sampai pangkal ekor. */
const BODY_THICK = 0.25; // Dikembalikan tebal seperti referensi
/** Profil lebar KEPALA (anatomi ular, bukan tabung rata): moncong kecil -> rahang LEBAR -> leher MENGECIL (lebih sempit dari badan) -> badan. */
const NOSE_W = BODY_THICK * 1.5; // Moncong rata dan lebar seperti gaya kartun
const JAW_W = BODY_THICK * 1.7; // Rahang lebar untuk menampung mata besar yang menonjol
const NECK_W = BODY_THICK * 0.9;
/** Panjang tiap fase kepala dalam satuan kotak ABSOLUT (bukan fraksi jarak) — diskalakan mengikuti BODY_THICK supaya ukuran kepala konsisten berapa pun panjang ularnya. */
const HEAD_LENS = [0, 0.06, 0.11, 0.2, 0.27];
const TAIL_T = 0.82;
const SEGMENTS = 20;

function smoothstep(edge0, edge1, x) {
    if (edge1 <= edge0) return x >= edge1 ? 1 : 0;
    const t = Math.min(1, Math.max(0, (x - edge0) / (edge1 - edge0)));
    return t * t * (3 - 2 * t);
}

/** Kurva lebar sepanjang path (0=moncong .. 1=ujung ekor) — dibangun sekali per ular dari jarak asal->tujuan, supaya kepala tetap ukuran wajar walau ularnya pendek. */
function buildWidthCurve(distance) {
    const maxHeadLen = distance * 0.45;
    const scale = HEAD_LENS[4] > maxHeadLen ? maxHeadLen / HEAD_LENS[4] : 1;
    const lens = HEAD_LENS.map((v) => v * scale);
    const tailStart = Math.max(lens[4] + distance * 0.1, distance * TAIL_T);

    return [
        { t: 0, w: NOSE_W },
        { t: lens[1] / distance, w: JAW_W },
        { t: lens[2] / distance, w: JAW_W },
        { t: lens[3] / distance, w: NECK_W },
        { t: lens[4] / distance, w: BODY_THICK },
        { t: Math.min(tailStart / distance, 0.96), w: BODY_THICK },
        { t: 1, w: 0.015 },
    ];
}

function widthAtCurve(curve, t) {
    for (let i = 0; i < curve.length - 1; i++) {
        const a = curve[i];
        const b = curve[i + 1];
        if (t >= a.t && t <= b.t) {
            return a.w + (b.w - a.w) * smoothstep(a.t, b.t, t);
        }
    }
    return curve[curve.length - 1].w;
}

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

function randRange(min, max) {
    return min + Math.random() * (max - min);
}

let uidCounter = 0;

/**
 * Satu ular = SATU SILUET MULUS (dua path yang berbagi tepi persis sama di
 * `neckEndT`, bukan rantai ellipse/ruas terpisah — versi "chunky bersegmen"
 * sebelumnya justru terbaca sebagai cacing/lipan karena tiap ruas punya
 * pinggiran sendiri yang sedikit bergelombang). Path pertama (moncong sampai
 * leher, `neckEndT`) diisi warna KEPALA yang beda dari path kedua (leher
 * sampai ujung ekor, warna BADAN) — dua-duanya disampel dari kurva lebar
 * anatomi yang SAMA (moncong kecil -> rahang lebar -> leher menyempit ->
 * badan tebal konstan -> ekor meruncing) di titik `neckEndT` yang sama
 * persis, jadi tepinya menyatu tanpa jahitan/patahan, sekaligus kepala tetap
 * terbaca jelas lewat batas warna (bukan gradient/pinch).
 *
 * Statis (TIDAK ada animasi gelombang tubuh sama sekali) — inilah yang
 * menjamin nol biaya render idle (beda dari v6 yang menganimasikan atribut
 * `d` SVG tiap frame, mahal & jadi sumber lag). "Hidup"-nya ular cukup dari
 * kedip mata/lidah/mulut (one-shot pulse, murah) + goyang wajah halus.
 *
 * Wajah (mata/mulut/lidah) dihitung dari titik & tangent path di area
 * rahang — anatomis lebih benar dan otomatis ikut rotasi/posisi kepala.
 */
export class SnakeRenderer {
    /**
     * @param {HTMLElement} snakeLayer layer struktural (tidak menggambar apa pun sendiri)
     * @param {import('./BoardGeometry').BoardGeometry} geometry
     * @param {{start:number,end:number,themeIndex:number,cols:number,rows:number}} config
     */
    constructor(snakeLayer, geometry, config) {
        this.uid = `sn${uidCounter++}`;
        this.config = config;
        this.themeName = SNAKE_THEME_NAMES[config.themeIndex % SNAKE_THEME_NAMES.length];
        this.theme = SNAKE_THEMES[this.themeName];
        this.path = geometry.connector(config.start, config.end);
        this._timers = [];

        this._build(snakeLayer);
        this._scheduleBlink();
        this._scheduleMouth();
        this._scheduleTongue();
    }

    _build(snakeLayer) {
        const { cols, rows } = this.config;
        const { from, distance, px, py } = this.path;
        const anchorX = from.x + this.path.laneOffset.x;
        const anchorY = from.y + this.path.laneOffset.y;
        const angleDeg = (Math.atan2(this.path.dy, this.path.dx) * 180) / Math.PI;

        this.widthCurve = buildWidthCurve(distance);

        // Kurva LOKAL (0,0)=moncong -> (0,distance)=ujung ekor, cubic bezier
        // sungguhan dengan lekukan S (p1/p2 dibengkokkan berlawanan arah)
        // supaya ular terlihat meliuk, bukan lurus seperti pipa.
        const bendSign = px + py >= 0 ? 1 : -1;
        const bend = Math.min(distance * 0.35, 1.0); // Kurva S yang lembut
        this.p0 = { x: 0, y: 0 };
        this.p1 = { x: bendSign * bend, y: distance * 0.33 };
        this.p2 = { x: -bendSign * bend, y: distance * 0.67 };
        this.p3 = { x: 0, y: distance };

        // Wrapper dilebarkan mengikuti titik terlebar (rahang) + margin aman
        // untuk lekukan S & pola totol di badan.
        this.wrapWidth = JAW_W * 1.5 + Math.abs(bend) * 2.0 + 0.5;

        this.wrap = document.createElement('div');
        this.wrap.className = 'board-object snake-object';
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
        
        // Osilasi (waviness) dihilangkan agar garis lurus melengkung halus mengikuti kurva dasar saja
        return { point: point, tangent, normal, width: widthAtCurve(this.widthCurve, t) };
    }

    /**
     * Poligon mulus dari `tStart` ke `tEnd` pada `widthFrac` tertentu
     * (1=siluet utuh, <1=pita aksen seperti perut/bayangan), dengan offset
     * tegak lurus opsional. `roundStartCap`: tutup moncong bulat di tStart
     * (dipakai HANYA untuk potongan kepala, tStart=0) — ujung `tEnd` selalu
     * potongan lurus (supaya menyatu presisi dengan potongan berikutnya yang
     * disampel dari titik `tEnd` yang SAMA).
     */
    _polygonPath(tStart, tEnd, widthFrac = 1, centerOffsetFrac = 0, roundStartCap = false) {
        const left = [];
        const right = [];
        const samples = [];
        for (let i = 0; i <= SEGMENTS; i++) {
            const t = tStart + (tEnd - tStart) * (i / SEGMENTS);
            const s = this._sampleAt(t);
            samples.push(s);
            const halfW = (s.width * widthFrac) / 2;
            const centerX = s.point.x + s.normal.x * (s.width * centerOffsetFrac);
            const centerY = s.point.y + s.normal.y * (s.width * centerOffsetFrac);
            left.push({ x: centerX + s.normal.x * halfW, y: centerY + s.normal.y * halfW });
            right.push({ x: centerX - s.normal.x * halfW, y: centerY - s.normal.y * halfW });
        }

        if (!roundStartCap) {
            const outline = [...left, ...right.reverse()];
            return `M ${outline.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')} Z`;
        }

        const head = samples[0];
        const r = head.width / 2;
        const angleNormal = Math.atan2(head.normal.y, head.normal.x);
        const capSteps = 10;
        const cap = [];
        for (let k = 0; k <= capSteps; k++) {
            const angle = angleNormal + Math.PI * (k / capSteps);
            cap.push({ x: head.point.x + r * Math.cos(angle), y: head.point.y + r * Math.sin(angle) });
        }
        const outline = [...cap, ...right.slice(1), ...left.slice(1).reverse()];
        return `M ${outline.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')} Z`;
    }

    _buildSvg(distance) {
        const svg = svgEl('svg', {
            // PENTING: lebar viewBox HARUS sama persis dengan lebar CSS wrapper
            // (this.wrapWidth) supaya skala X:Y tetap 1:1 (tidak gepeng/melebar).
            viewBox: `${-this.wrapWidth / 2} 0 ${this.wrapWidth} ${distance}`,
            preserveAspectRatio: 'none',
            class: 'board-object-svg snake-body-svg',
        });
        svg.style.position = 'absolute';
        svg.style.left = '0';
        svg.style.top = '0';
        svg.style.width = '100%';
        svg.style.height = '100%';
        svg.style.overflow = 'visible';

        const defs = svgEl('defs');
        svg.appendChild(defs);

        this.bodyGroup = svgEl('g', { class: 'snake-body-group' });
        svg.appendChild(this.bodyGroup);

        // Titik rahang (plateau terlebar kepala) — dipakai oleh wajah. Titik
        // leher (widthCurve[4], lebar badan sudah stabil di BODY_THICK) =
        // batas kepala/badan, dipakai bersama oleh kedua potongan siluet
        // supaya tepinya persis menyatu (sampel dari fungsi lebar yang sama).
        const jawT = ((this.widthCurve[1].t + this.widthCurve[2].t) / 2) || 0.03;
        const jaw = this._sampleAt(Math.max(jawT, 0.001));
        const neckEndT = this.widthCurve[4].t;

        // Kepala: satu potongan siluet mulus (moncong bulat -> rahang lebar
        // -> leher menyempit), warna KEPALA berbeda dari badan supaya kepala
        // tetap terbaca tegas TANPA bentuk terpisah (tidak ada lagi "balon").
        this.headPath = svgEl('path', {
            d: this._polygonPath(0, neckEndT, 1, 0, true),
            fill: this.theme.head,
            stroke: this.theme.outline,
            'stroke-width': 0.032,
            'stroke-linejoin': 'round',
        });
        this.bodyGroup.appendChild(this.headPath);

        // Badan: potongan siluet mulus dari leher sampai ujung ekor, warna
        // BADAN (beda dari kepala) — mulai TEPAT di neckEndT (titik & lebar
        // sama persis dengan ujung potongan kepala) sehingga tidak ada celah.
        this.mainBodyPath = svgEl('path', {
            d: this._polygonPath(neckEndT, 1, 1, 0, false),
            fill: this.theme.body,
            stroke: this.theme.outline,
            'stroke-width': 0.032,
            'stroke-linejoin': 'round',
        });
        this.bodyGroup.appendChild(this.mainBodyPath);

        // Perut: pita terang di satu sisi memanjang badan (ciri ilustrasi
        // ular papan klasik) — statis, murni dekoratif.
        this.bellyPath = svgEl('path', {
            d: this._polygonPath(neckEndT + 0.02, TAIL_T, 0.44, 0.26),
            fill: this.theme.belly,
            opacity: 0.75,
        });
        this.bodyGroup.appendChild(this.bellyPath);

        // Tambahkan sisik iga melintang (scutes) di area perut
        const bellyScutesGroup = svgEl('g', { class: 'snake-belly-scutes', opacity: 0.35 });
        const scutesStart = neckEndT + 0.05;
        const scutesEnd = TAIL_T - 0.05;
        const numScutes = Math.floor((scutesEnd - scutesStart) * distance / 0.1);
        for (let i = 0; i < numScutes; i++) {
            const t = scutesStart + ((scutesEnd - scutesStart) * (i / numScutes));
            const s = this._sampleAt(t);
            const w = s.width * 0.44 * 0.7; // 70% dari lebar perut
            const cx = s.point.x + s.normal.x * s.width * 0.26;
            const cy = s.point.y + s.normal.y * s.width * 0.26;
            const p1 = { x: cx + s.normal.x * w/2, y: cy + s.normal.y * w/2 };
            const p2 = { x: cx - s.normal.x * w/2, y: cy - s.normal.y * w/2 };
            bellyScutesGroup.appendChild(svgEl('line', {
                x1: fmt(p1.x), y1: fmt(p1.y), x2: fmt(p2.x), y2: fmt(p2.y),
                stroke: this.theme.outline, 'stroke-width': 0.015
            }));
        }
        this.bodyGroup.appendChild(bellyScutesGroup);

        this._buildPatternSpots(distance, neckEndT);
        
        // Highlight 3D punggung (spine highlight) tipis transparan
        this.spineHighlight = svgEl('path', {
            d: this._polygonPath(neckEndT + 0.05, TAIL_T - 0.05, 0.04, -0.1),
            fill: '#ffffff',
            opacity: 0.25,
        });
        this.bodyGroup.appendChild(this.spineHighlight);

        this._buildFace(defs, jaw);

        this.wrap.appendChild(svg);
    }

    /**
     * Pola totol statis di sepanjang badan (bukan pita berselang-seling
     * perpendikular seperti percobaan sebelumnya — itulah yang bikin badan
     * terbaca "beruas" ala cacing/lipan). Totol kecil selang-seling kiri-
     * kanan garis tengah, ukuran & posisi deterministik dari start/end
     * konektor (stabil antar render, bukan Math.random di lokasi).
     */
    _buildPatternSpots(distance, neckEndT) {
        const startT = neckEndT + 0.05;
        const spacing = 0.34;
        const count = Math.max(3, Math.round(((TAIL_T - startT) * distance) / spacing));

        for (let i = 0; i < count; i++) {
            const t = startT + ((TAIL_T - startT) * (i + 0.5)) / count;
            const s = this._sampleAt(t);
            const side = i % 2 === 0 ? 1 : -1;
            const offset = s.width * 0.22;
            const cx = s.point.x + s.normal.x * offset * side;
            const cy = s.point.y + s.normal.y * offset * side;
            const r = s.width * 0.24;

            // Motif berlian/chevron mengikuti arah normal dan tangent
            const dW = r * 0.9;
            const dH = r * 1.6;
            const dTop = { x: cx + s.normal.x * dW, y: cy + s.normal.y * dW };
            const dBot = { x: cx - s.normal.x * dW, y: cy - s.normal.y * dW };
            const dLeft = { x: cx - s.tangent.x * dH, y: cy - s.tangent.y * dH };
            const dRight = { x: cx + s.tangent.x * dH, y: cy + s.tangent.y * dH };

            this.bodyGroup.appendChild(svgEl('polygon', {
                points: `${fmt(dTop.x)},${fmt(dTop.y)} ${fmt(dRight.x)},${fmt(dRight.y)} ${fmt(dBot.x)},${fmt(dBot.y)} ${fmt(dLeft.x)},${fmt(dLeft.y)}`,
                fill: this.theme.pattern, opacity: 0.65,
                'stroke-linejoin': 'round',
            }));
        }
    }

    /**
     * Wajah (mata/mulut/lidah) dihitung dari titik/tangent/normal di area
     * RAHANG (titik terlebar kepala) — koordinatnya di ruang SVG LOKAL yang
     * sama dengan badan, jadi otomatis ikut rotasi & posisi kepala.
     */
    _buildFace(defs, jaw) {
        const r = jaw.width / 2;
        const fwd = { x: -jaw.tangent.x, y: -jaw.tangent.y }; // arah "depan" (menuju moncong)
        const at = (fwdFrac, sideFrac) => ({
            x: jaw.point.x + fwd.x * r * fwdFrac + jaw.normal.x * r * sideFrac,
            y: jaw.point.y + fwd.y * r * fwdFrac + jaw.normal.y * r * sideFrac,
        });

        const eyeL = at(0.2, 0.7); // Mata menonjol keluar ke samping
        const eyeR = at(0.2, -0.7);
        const eyeR_ = r * 0.55; // Mata bundar besar gaya kartun

        const seed = this.config.start + this.config.end * 0.5;
        const swayDuration = randRange(4.5, 5.5).toFixed(2);
        const swayDelay = AnimationController.seeded(seed, 0.31, 0.5, 3).toFixed(2);

        this.faceGroup = svgEl('g', { class: 'snake-face-group' });
        this.faceGroup.style.transformOrigin = `${fmt(jaw.point.x)}px ${fmt(jaw.point.y)}px`;
        this.faceGroup.style.animationDuration = `${swayDuration}s`;
        this.faceGroup.style.animationDelay = `${swayDelay}s`;

        const expr = Math.min(2, Math.floor(AnimationController.seeded(seed, 0.47, 1.1, 3)));
        const browTilt = expr === 1 ? -0.2 : expr === 2 ? 0.2 : 0;

        this.eyesOpen = svgEl('g', { class: 'snake-eyes-open' });
        [eyeL, eyeR].forEach((e, idx) => {
            const side = idx === 0 ? 1 : -1;
            // Bola mata putih besar
            this.eyesOpen.appendChild(svgEl('circle', { cx: fmt(e.x), cy: fmt(e.y), r: fmt(eyeR_), fill: '#ffffff', stroke: this.theme.outline, 'stroke-width': fmt(eyeR_ * 0.15) }));
            
            // Pupil bulat besar hitam
            this.eyesOpen.appendChild(svgEl('circle', { 
                cx: fmt(e.x), cy: fmt(e.y), r: fmt(eyeR_ * 0.45), 
                fill: '#111827'
            }));

            // Pantulan cahaya (Shine) statis
            this.eyesOpen.appendChild(svgEl('circle', {
                cx: fmt(e.x + eyeR_ * 0.15), cy: fmt(e.y - eyeR_ * 0.15), r: fmt(eyeR_ * 0.15),
                fill: '#ffffff', opacity: 0.95
            }));

            // Alis melengkung
            const browY = e.y - eyeR_ * 1.35;
            this.eyesOpen.appendChild(svgEl('path', {
                d: `M ${fmt(e.x - eyeR_ * 0.8)},${fmt(browY + eyeR_ * browTilt * side)} Q ${fmt(e.x)},${fmt(browY - eyeR_ * 0.4)} ${fmt(e.x + eyeR_ * 0.8)},${fmt(browY - eyeR_ * browTilt * side)}`,
                stroke: this.theme.outline, 'stroke-width': fmt(eyeR_ * 0.15), fill: 'none', 'stroke-linecap': 'round',
            }));
        });

        this.eyesClosed = svgEl('g', { class: 'snake-eyes-closed', style: 'display:none;' });
        [eyeL, eyeR].forEach((e) => {
            this.eyesClosed.appendChild(svgEl('line', {
                x1: fmt(e.x - eyeR_ * 0.8), y1: fmt(e.y), x2: fmt(e.x + eyeR_ * 0.8), y2: fmt(e.y),
                stroke: this.theme.outline, 'stroke-width': fmt(eyeR_ * 0.35), 'stroke-linecap': 'round',
            }));
        });

        this.faceGroup.appendChild(this.eyesOpen);
        this.faceGroup.appendChild(this.eyesClosed);

        // Mulut lurus/datar khas wajah bingung/datar
        const mouthA = at(0.85, 0.4);
        const mouthB = at(0.85, -0.4);
        const mouthBulge = expr === 1 ? 0.95 : expr === 2 ? 0.8 : 0.85; // Sedikit cekung/cembung ringan
        this.mouthEl = svgEl('path', {
            d: `M ${fmt(mouthA.x)},${fmt(mouthA.y)} Q ${fmt(jaw.point.x + fwd.x * r * mouthBulge)},${fmt(jaw.point.y + fwd.y * r * mouthBulge)} ${fmt(mouthB.x)},${fmt(mouthB.y)}`,
            stroke: this.theme.outline, 'stroke-width': fmt(r * 0.08), fill: 'none', 'stroke-linecap': 'round',
            class: 'snake-mouth-shape',
        });
        this.mouthEl.style.transformOrigin = `${fmt(jaw.point.x)}px ${fmt(jaw.point.y)}px`;
        this.faceGroup.appendChild(this.mouthEl);

        const tongueBase = at(0.92, 0);
        const tongueLen = r * 1.1;
        const tongueHalf = r * 0.1;
        const tip = { x: tongueBase.x + fwd.x * tongueLen, y: tongueBase.y + fwd.y * tongueLen };
        const forkSpread = tongueHalf * 1.8;
        const forkLen = tongueLen * 0.32;
        const tipL = { x: tip.x - fwd.x * forkLen + jaw.normal.x * forkSpread, y: tip.y - fwd.y * forkLen + jaw.normal.y * forkSpread };
        const tipR = { x: tip.x - fwd.x * forkLen - jaw.normal.x * forkSpread, y: tip.y - fwd.y * forkLen - jaw.normal.y * forkSpread };
        const baseL = { x: tongueBase.x + jaw.normal.x * tongueHalf, y: tongueBase.y + jaw.normal.y * tongueHalf };
        const baseR = { x: tongueBase.x - jaw.normal.x * tongueHalf, y: tongueBase.y - jaw.normal.y * tongueHalf };

        this.tongueEl = svgEl('path', {
            d: `M ${fmt(baseL.x)},${fmt(baseL.y)} L ${fmt(tip.x)},${fmt(tip.y)} L ${fmt(tipL.x)},${fmt(tipL.y)} M ${fmt(tip.x)},${fmt(tip.y)} L ${fmt(tipR.x)},${fmt(tipR.y)} M ${fmt(baseR.x)},${fmt(baseR.y)} L ${fmt(tip.x)},${fmt(tip.y)}`,
            stroke: TONGUE_COLOR, 'stroke-width': fmt(tongueHalf * 0.55), 'stroke-linecap': 'round', 'stroke-linejoin': 'round',
            fill: 'none', class: 'snake-tongue-shape',
        });
        this.tongueEl.style.transformOrigin = `${fmt(tongueBase.x)}px ${fmt(tongueBase.y)}px`;
        this.faceGroup.appendChild(this.tongueEl);

        this.bodyGroup.appendChild(this.faceGroup);
    }

    _clearTimer(id) {
        const idx = this._timers.indexOf(id);
        if (idx !== -1) this._timers.splice(idx, 1);
    }

    /** Kedip: tukar grup mata terbuka<->tertutup (display swap, sprite) selama 120ms — interval acak 4-7 detik. */
    _scheduleBlink() {
        const delay = randRange(4000, 7000);
        const id = setTimeout(() => {
            this._clearTimer(id);
            this.eyesOpen.style.display = 'none';
            this.eyesClosed.style.display = '';
            const reopenId = setTimeout(() => {
                this._clearTimer(reopenId);
                this.eyesOpen.style.display = '';
                this.eyesClosed.style.display = 'none';
                this._scheduleBlink();
            }, 120);
            this._timers.push(reopenId);
        }, delay);
        this._timers.push(id);
    }

    /** Mulut sedikit membuka lalu menutup, ~220ms, interval acak 6-10 detik. */
    _scheduleMouth() {
        const delay = randRange(6000, 10000);
        const id = setTimeout(() => {
            this._clearTimer(id);
            AnimationController.pulse(this.mouthEl, 'snake-mouth-open', 220).then(() => this._scheduleMouth());
        }, delay);
        this._timers.push(id);
    }

    /** Lidah: keluar 50ms, diam 80ms, masuk 50ms (total 180ms) — interval acak 3-8 detik. */
    _scheduleTongue() {
        const delay = randRange(3000, 8000);
        const id = setTimeout(() => {
            this._clearTimer(id);
            AnimationController.pulse(this.tongueEl, 'snake-tongue-flick', 180).then(() => this._scheduleTongue());
        }, delay);
        this._timers.push(id);
    }

    /**
     * Efek "ular aktif" saat pion mendarat di kepalanya — glow warna tema
     * pada seluruh grup badan + lidah menjulur. Best-effort, tidak pernah error.
     */
    async reactToLanding() {
        if (!this.bodyGroup) return;
        this.bodyGroup.style.setProperty('--bite-glow', this.theme.glow);
        AnimationController.pulse(this.tongueEl, 'snake-tongue-flick', 180);
        await AnimationController.pulse(this.bodyGroup, 'snake-bite-glow', 750);
    }

    destroy() {
        this._timers.forEach((id) => clearTimeout(id));
        this._timers = [];
    }
}
