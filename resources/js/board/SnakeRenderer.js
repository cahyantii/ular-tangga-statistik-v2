import { SNAKE_THEMES, SNAKE_THEME_NAMES, TONGUE_COLOR } from './spriteData.js';
import { AnimationController } from './AnimationController.js';

const SVG_NS = 'http://www.w3.org/2000/svg';

/**
 * Ketebalan badan (satuan kotak) — KONSTAN dari leher sampai pangkal ekor.
 * 0.42 (percobaan sebelumnya) terlalu tebal - dikombinasikan dengan pola
 * diamond besar di _buildScaleTexture() membuat badannya terlihat
 * menggembung/berbenjol-benjol seperti balon berkerut, bukan ular yang
 * ramping. 0.32 adalah titik tengah: jelas lebih tebal dari 0.26 (terlalu
 * kurus/seperti belut) tapi tidak segemuk 0.42.
 */
const BODY_THICK = 0.32;
/** Profil lebar KEPALA (anatomi ular sungguhan, bukan tabung rata): moncong kecil -> rahang LEBAR -> leher MENGECIL (lebih sempit dari badan) -> badan. */
const NOSE_W = BODY_THICK * 0.9;
const JAW_W = BODY_THICK * 1.7;
const NECK_W = BODY_THICK * 0.72;
/** Panjang tiap fase kepala dalam satuan kotak ABSOLUT (bukan fraksi jarak) — diskalakan mengikuti BODY_THICK supaya ukuran kepala konsisten berapa pun panjang ularnya. */
const HEAD_LENS = [0, 0.06, 0.11, 0.2, 0.27];
const TAIL_T = 0.80;
const SEGMENTS = 34;

/** Amplitudo gelombang badan (satuan kotak, ~setara 2-3px) — NOL persis di t=0 & t=1 (kepala & ekor tidak pernah bergeser dari anchor). */
const WAVE_AMPLITUDE = 0.045;
const WAVE_FREQ = 1.6;
const WAVE_STEPS = 8;

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
        { t: 0, w: NOSE_W }, // ujung moncong, bulat kecil
        { t: lens[1] / distance, w: JAW_W }, // rahang melebar cepat
        { t: lens[2] / distance, w: JAW_W }, // pipi — bertahan lebar sebentar
        { t: lens[3] / distance, w: NECK_W }, // leher menyempit (LEBIH SEMPIT dari badan)
        { t: lens[4] / distance, w: BODY_THICK }, // menyatu ke ketebalan badan
        { t: Math.min(tailStart / distance, 0.96), w: BODY_THICK }, // badan konstan
        { t: 1, w: 0 }, // ekor meruncing jadi titik
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
 * Satu ular = SATU PATH SVG UTUH, langsung dikenali sebagai ULAR (bukan
 * belut/cacing/selang) lewat profil lebar ANATOMI sungguhan sepanjang satu
 * kurva cubic bezier: moncong kecil -> RAHANG melebar (lebih lebar dari
 * leher) -> LEHER menyempit (lebih sempit dari badan) -> badan tebal
 * konstan -> ekor meruncing. Semua bagian adalah titik-titik dari SATU
 * fungsi lebar kontinu di sepanjang SATU kurva — bukan head.png/body.png/
 * tail.png yang ditempel — jadi sambungannya taken otomatis mulus.
 *
 * Wajah (mata/mulut/lidah) dihitung dari titik & tangent path di area
 * rahang (bukan pas di ujung moncong) — anatomis lebih benar (mata ular
 * ada di sisi kepala, bukan di ujung hidung) dan otomatis ikut rotasi/
 * posisi kepala.
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
        // sungguhan. p1/p2 SENGAJA dibengkokkan ke arah BERLAWANAN (bukan
        // searah seperti sebelumnya) supaya terbentuk lekukan S sungguhan
        // (meliuk seperti ular berjalan), bukan cuma satu lengkungan tunggal
        // yang landai - lengkungan searah tadi nyaris tidak terlihat pada
        // konektor pendek/nyaris vertikal (banyak terjadi setelah tata-ulang
        // posisi konektor supaya tidak saling menyilang di papan), sehingga
        // ularnya tampak seperti pipa/belut lurus alih-alih meliuk.
        const bendSign = px + py >= 0 ? 1 : -1;
        const bend = Math.min(distance * 0.3, 0.7);
        this.p0 = { x: 0, y: 0 };
        this.p1 = { x: bendSign * bend, y: distance * 0.33 };
        this.p2 = { x: -bendSign * bend, y: distance * 0.67 };
        this.p3 = { x: 0, y: distance };

        // Wrapper dilebarkan mengikuti titik TERLEBAR ular (rahang, bukan
        // moncong) + sedikit margin aman (highlight/tekstur sisik sedikit
        // melebihi outline utama).
        this.wrapWidth = JAW_W * 1.25;

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
        return { point, tangent, normal, width: widthAtCurve(this.widthCurve, t) };
    }

    /** Poligon meruncing sepanjang kurva pada widthFrac tertentu (1=outline utama, <1=stripe aksen), dengan offset tegak lurus opsional & fase gelombang. */
    _buildOutline(wavePhase, widthFrac = 1, centerOffsetFrac = 0) {
        const left = [];
        const right = [];
        const samples = [];
        for (let i = 0; i <= SEGMENTS; i++) {
            const t = i / SEGMENTS;
            const s = this._sampleAt(t);
            if (wavePhase !== null) {
                const envelope = Math.sin(Math.PI * t);
                const wave = WAVE_AMPLITUDE * envelope * Math.sin(2 * Math.PI * (WAVE_FREQ * t - wavePhase));
                s.point = { x: s.point.x + s.normal.x * wave, y: s.point.y + s.normal.y * wave };
            }
            samples.push(s);
            const halfW = (s.width * widthFrac) / 2;
            const centerX = s.point.x + s.normal.x * (s.width * centerOffsetFrac);
            const centerY = s.point.y + s.normal.y * (s.width * centerOffsetFrac);
            left.push({ x: centerX + s.normal.x * halfW, y: centerY + s.normal.y * halfW });
            right.push({ x: centerX - s.normal.x * halfW, y: centerY - s.normal.y * halfW });
        }

        if (widthFrac < 1) {
            // Stripe aksen (highlight/shadow) — TIDAK butuh tutup kepala bulat,
            // cukup poligon pita tipis mengikuti kurva.
            const outline = [...left, ...right.reverse()];
            return `M ${outline.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')} Z`;
        }

        // Tutup moncong bulat: setengah lingkaran di depan t=0 (arah -tangent)
        // supaya moncong jadi ujung BULAT KECIL dari bentuk yang sama — bukan
        // lingkaran/gambar terpisah yang ditempel.
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
        const gradId = `snake-grad-${this.uid}`;
        const gradient = svgEl('linearGradient', { id: gradId, x1: '0%', y1: '0%', x2: '0%', y2: '100%' });
        gradient.appendChild(svgEl('stop', { offset: '0%', 'stop-color': this.theme.body[0] }));
        gradient.appendChild(svgEl('stop', { offset: '45%', 'stop-color': this.theme.body[1] }));
        gradient.appendChild(svgEl('stop', { offset: '100%', 'stop-color': this.theme.body[2] }));
        defs.appendChild(gradient);
        svg.appendChild(defs);

        // SATU path utuh — isi & outline ular seluruhnya (moncong+rahang+leher+badan+ekor).
        // Outline SENGAJA dibuat TEBAL (0.024 -> 0.065, ~x2.7) - inilah yang
        // membuat ilustrasi ular papan klasik terbaca sebagai "makhluk
        // bergambar dengan siluet tegas", bukan pipa gradient lembut yang
        // menyatu dengan latar (itulah kenapa versi sebelumnya masih terlihat
        // seperti belut/cacing meski badannya sudah tidak kurus).
        this.bodyPath = svgEl('path', {
            d: this._buildOutline(0),
            fill: `url(#${gradId})`,
            stroke: this.theme.outline,
            'stroke-width': 0.065,
            'stroke-linejoin': 'round',
            class: `snake-body-shape snake-wave-${this.uid}`,
        });
        svg.appendChild(this.bodyPath);

        // Bayangan halus (sisi gelap, dari body[2] - warna tergelap gradient
        // yang sama) supaya badan terasa punya volume/bulat, bukan pita
        // gepeng rata - TIDAK pakai field tema terpisah, langsung diturunkan
        // dari gradient badan sendiri supaya selalu senada.
        this.shadowPath = svgEl('path', {
            d: this._buildOutline(0, 0.4, -0.22),
            fill: this.theme.body[2],
            opacity: 0.22,
            class: `snake-shadow-shape snake-wave-${this.uid}`,
        });
        svg.appendChild(this.shadowPath);

        // Perut (belly) — pita LEBAR & jelas berwarna terang di satu sisi
        // memanjang badan, ciri khas ilustrasi ular papan (perut selalu
        // lebih terang dari punggung). Digambar SEBELUM rantai sisik supaya
        // rantai tetap tampil di atasnya.
        this.bellyPath = svgEl('path', {
            d: this._buildOutline(0, 0.46, 0.27),
            fill: this.theme.belly,
            opacity: 0.8,
            class: `snake-belly-shape snake-wave-${this.uid}`,
        });
        svg.appendChild(this.bellyPath);

        this._buildSpineChain(svg, distance);
        this._buildFace(svg);
        this._injectWaveKeyframes();

        this.wrap.appendChild(svg);
    }

    /**
     * Rantai belah-ketupat MENYAMBUNG di sepanjang TULANG PUNGGUNG (garis
     * tengah badan) - ini ciri visual utama papan ular tangga bergambar
     * klasik (lihat referensi desain) yang sebelumnya HILANG: percobaan-
     * percobaan sebelumnya menaruh diamond terpisah/tersebar di berbagai
     * titik badan, bukan satu motif menyambung dari leher sampai ekor.
     * Dibangun sebagai satu polyline zigzag (bolak-balik kiri-kanan dari
     * garis tengah) yang di-stroke tebal - jauh lebih mirip rantai diamond
     * sungguhan dibanding kumpulan bentuk diamond terpisah.
     */
    _buildSpineChain(svg, distance) {
        const startT = 0.15;
        const endT = TAIL_T - 0.02;
        const stepLen = 0.19; // satuan kotak per zig - rapat supaya menyambung jadi rantai
        const steps = Math.max(4, Math.round(((endT - startT) * distance) / stepLen));

        const pts = [];
        for (let i = 0; i <= steps; i++) {
            const t = startT + ((endT - startT) * i) / steps;
            const s = this._sampleAt(t);
            const side = i % 2 === 0 ? 1 : -1;
            const amp = s.width * 0.3;
            pts.push({ x: s.point.x + s.normal.x * amp * side, y: s.point.y + s.normal.y * amp * side });
        }

        const d = `M ${pts.map((p) => `${fmt(p.x)},${fmt(p.y)}`).join(' L ')}`;
        const chain = svgEl('path', {
            d,
            fill: 'none',
            stroke: this.theme.scale,
            'stroke-width': BODY_THICK * 0.42,
            'stroke-linejoin': 'round',
            'stroke-linecap': 'round',
            opacity: 0.85,
        });
        svg.appendChild(chain);
    }

    /**
     * Wajah (mata/mulut/lidah) dihitung dari titik/tangent/normal di area
     * RAHANG (titik terlebar kepala, bukan ujung moncong — anatomis lebih
     * benar) — koordinatnya bagian dari SVG LOKAL YANG SAMA dengan badan,
     * jadi otomatis ikut rotasi & posisi kepala.
     */
    _buildFace(svg) {
        // Cari t di titik tengah plateau rahang (antara HEAD_LENS[1] & [2]).
        const jawT = ((this.widthCurve[1].t + this.widthCurve[2].t) / 2) || 0.03;
        const jaw = this._sampleAt(Math.max(jawT, 0.001));
        const r = jaw.width / 2;
        const fwd = { x: -jaw.tangent.x, y: -jaw.tangent.y }; // arah "depan" (menuju moncong)
        const at = (fwdFrac, sideFrac) => ({
            x: jaw.point.x + fwd.x * r * fwdFrac + jaw.normal.x * r * sideFrac,
            y: jaw.point.y + fwd.y * r * fwdFrac + jaw.normal.y * r * sideFrac,
        });

        const eyeL = at(0.05, 0.52);
        const eyeR = at(0.05, -0.52);
        const eyeR_ = r * 0.34; // mata lebih besar

        // Kepala "sedikit bergerak" — wobble SANGAT kecil pada grup wajah saja
        // (mata/mulut/lidah bergerak bersama sebagai satu kesatuan, sinkron),
        // TIDAK mengubah bentuk/posisi path badan itu sendiri (anchor kepala
        // di cell-center tetap presisi).
        const seed = this.config.start + this.config.end * 0.5;
        const swayDuration = randRange(4.5, 5.5).toFixed(2);
        const swayDelay = AnimationController.seeded(seed, 0.31, 0.5, 3).toFixed(2);

        this.faceGroup = svgEl('g', { class: 'snake-face-group' });
        this.faceGroup.style.transformOrigin = `${fmt(jaw.point.x)}px ${fmt(jaw.point.y)}px`;
        this.faceGroup.style.animationDuration = `${swayDuration}s`;
        this.faceGroup.style.animationDelay = `${swayDelay}s`;

        // Ekspresi berbeda per ular (supaya tidak terlihat copy-paste walau
        // satu tema warna dipakai berkali-kali) - deterministik dari posisi
        // asal/tujuan konektornya sendiri (bukan Math.random, supaya sama
        // setiap kali halaman dimuat ulang): 0=netral, 1=ramah/penasaran
        // (alis terangkat), 2=tenang-percaya diri (alis sedikit turun).
        //
        // PENTING: AnimationController.seeded(seed, a, b, range) menghitung
        // sin(seed*a + b)*range - koefisien `a` WAJIB tidak nol, kalau tidak
        // `seed` sama sekali tidak berpengaruh ke hasil (seed*0 selalu 0
        // untuk semua ular) sehingga SEMUA ular diam-diam dapat expr yang
        // SAMA persis. Dipakai koefisien (0.47, 1.1) yang beda dari
        // pemanggilan seeded() lain di file ini (dipakai untuk swayDelay/
        // waveDelay) supaya ekspresi tidak ikut berkorelasi dengan timing
        // animasi tersebut.
        const expr = Math.min(2, Math.floor(AnimationController.seeded(seed, 0.47, 1.1, 3)));
        const browTilt = expr === 1 ? -0.16 : expr === 2 ? 0.1 : 0;

        // Mata "tajam tapi ramah": sklera putih + iris berwarna (bukan
        // hitam pekat polos) + pupil bulat (bulat = ramah, beda dari pupil
        // celah vertikal yang kesannya predator/menyeramkan) + kilau putih,
        // ditambah garis alis tipis di atas mata untuk kesan "tajam"/hidup.
        const eyeGradId = `snake-eye-grad-${this.uid}`;
        const eyeGrad = svgEl('radialGradient', { id: eyeGradId, cx: '38%', cy: '32%', r: '75%' });
        eyeGrad.appendChild(svgEl('stop', { offset: '0%', 'stop-color': this.theme.iris }));
        eyeGrad.appendChild(svgEl('stop', { offset: '100%', 'stop-color': this.theme.outline }));
        const defs = svg.querySelector('defs');
        defs.appendChild(eyeGrad);

        this.eyesOpen = svgEl('g', { class: 'snake-eyes-open' });
        [eyeL, eyeR].forEach((e, idx) => {
            const side = idx === 0 ? 1 : -1;
            // Sklera
            this.eyesOpen.appendChild(svgEl('circle', { cx: fmt(e.x), cy: fmt(e.y), r: fmt(eyeR_), fill: '#ffffff' }));
            // Iris (gradient warna tema, bukan hitam polos)
            this.eyesOpen.appendChild(svgEl('circle', { cx: fmt(e.x), cy: fmt(e.y), r: fmt(eyeR_ * 0.72), fill: `url(#${eyeGradId})` }));
            // Pupil bulat kecil (bulat = ramah)
            this.eyesOpen.appendChild(svgEl('circle', { cx: fmt(e.x), cy: fmt(e.y), r: fmt(eyeR_ * 0.34), fill: '#0b0b0f' }));
            // Kilau
            this.eyesOpen.appendChild(svgEl('circle', {
                cx: fmt(e.x + eyeR_ * 0.32), cy: fmt(e.y - eyeR_ * 0.35), r: fmt(eyeR_ * 0.32),
                fill: '#ffffff', opacity: 0.95, class: 'snake-eye-shine',
            }));
            // Alis tipis di atas mata — sumber utama "ekspresi" per ular.
            const browY = e.y - eyeR_ * 1.15;
            this.eyesOpen.appendChild(svgEl('path', {
                d: `M ${fmt(e.x - eyeR_ * 0.9)},${fmt(browY + eyeR_ * browTilt * side)} Q ${fmt(e.x)},${fmt(browY - eyeR_ * 0.35)} ${fmt(e.x + eyeR_ * 0.9)},${fmt(browY - eyeR_ * browTilt * side)}`,
                stroke: this.theme.outline, 'stroke-width': fmt(eyeR_ * 0.18), fill: 'none', 'stroke-linecap': 'round',
            }));
        });

        this.eyesClosed = svgEl('g', { class: 'snake-eyes-closed', style: 'display:none;' });
        [eyeL, eyeR].forEach((e) => {
            this.eyesClosed.appendChild(svgEl('line', {
                x1: fmt(e.x - eyeR_), y1: fmt(e.y), x2: fmt(e.x + eyeR_), y2: fmt(e.y),
                stroke: this.theme.outline, 'stroke-width': fmt(eyeR_ * 0.45), 'stroke-linecap': 'round',
            }));
        });

        this.faceGroup.appendChild(this.eyesOpen);
        this.faceGroup.appendChild(this.eyesClosed);

        // Mulut: garis tipis di sisi bawah rahang, dekat moncong - sedikit
        // lebih melengkung ke depan untuk expr=1 (ramah/penasaran, kesan
        // senyum tipis), lebih lurus untuk expr=2 (tenang), supaya ikut
        // menyumbang variasi ekspresi antar ular.
        const mouthBulge = expr === 1 ? 1.0 : expr === 2 ? 0.65 : 0.85;
        const mouthA = at(0.65, 0.5);
        const mouthB = at(0.65, -0.5);
        this.mouthEl = svgEl('path', {
            d: `M ${fmt(mouthA.x)},${fmt(mouthA.y)} Q ${fmt(jaw.point.x + fwd.x * r * mouthBulge)},${fmt(jaw.point.y + fwd.y * r * mouthBulge)} ${fmt(mouthB.x)},${fmt(mouthB.y)}`,
            stroke: this.theme.outline, 'stroke-width': fmt(r * 0.09), fill: 'none', 'stroke-linecap': 'round',
            class: 'snake-mouth-shape',
        });
        this.mouthEl.style.transformOrigin = `${fmt(jaw.point.x)}px ${fmt(jaw.point.y)}px`;
        this.faceGroup.appendChild(this.mouthEl);

        // Lidah: bercabang, merah tua, menjulur searah moncong dari ujung mulut.
        const tongueBase = at(0.85, 0);
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

        svg.appendChild(this.faceGroup);
    }

    /**
     * Gelombang badan: SELURUH ular (bukan cuma kepala) bergerak sebagai
     * SATU object lewat animasi CSS pada properti `d` — dibangun dari
     * beberapa outline pada fase berbeda (moncong/ekor selalu identik di
     * semua fase karena amplitudo nol di t=0/t=1), jadi browser
     * menginterpolasi antar-fase secara mulus. Progressive enhancement:
     * browser yang belum dukung animasi `d` cukup menampilkan bentuk
     * statis, tetap presisi & menyatu, cuma tanpa gelombang.
     */
    _injectWaveKeyframes() {
        const bodyFrames = [];
        const shadowFrames = [];
        const bellyFrames = [];
        for (let i = 0; i <= WAVE_STEPS; i++) {
            const phase = i / WAVE_STEPS;
            const pct = fmt((i / WAVE_STEPS) * 100).replace(/\.000$/, '');
            bodyFrames.push(`${pct}% { d: path("${this._buildOutline(phase)}"); }`);
            shadowFrames.push(`${pct}% { d: path("${this._buildOutline(phase, 0.4, -0.22)}"); }`);
            bellyFrames.push(`${pct}% { d: path("${this._buildOutline(phase, 0.46, 0.27)}"); }`);
        }

        const duration = randRange(4.5, 5.5).toFixed(2);
        const delay = AnimationController.seeded(this.config.start + this.config.end * 0.5, 0.23, 0.4, 3).toFixed(2);

        const style = document.createElement('style');
        style.textContent = `
            @keyframes snake-wave-${this.uid} { ${bodyFrames.join(' ')} }
            @keyframes snake-wave-shadow-${this.uid} { ${shadowFrames.join(' ')} }
            @keyframes snake-wave-belly-${this.uid} { ${bellyFrames.join(' ')} }
            .snake-body-shape.snake-wave-${this.uid} { animation: snake-wave-${this.uid} ${duration}s ease-in-out ${delay}s infinite; }
            .snake-shadow-shape.snake-wave-${this.uid} { animation: snake-wave-shadow-${this.uid} ${duration}s ease-in-out ${delay}s infinite; }
            .snake-belly-shape.snake-wave-${this.uid} { animation: snake-wave-belly-${this.uid} ${duration}s ease-in-out ${delay}s infinite; }
        `;
        this.wrap.appendChild(style);
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
     * Efek "ular aktif" saat pion mendarat di kepalanya — glow warna tema +
     * lidah menjulur. Best-effort, tidak pernah error.
     */
    async reactToLanding() {
        if (!this.bodyPath) return;
        this.bodyPath.style.setProperty('--bite-glow', this.theme.glow);
        AnimationController.pulse(this.tongueEl, 'snake-tongue-flick', 180);
        await AnimationController.pulse(this.bodyPath, 'snake-bite-glow', 750);
    }

    destroy() {
        this._timers.forEach((id) => clearTimeout(id));
        this._timers = [];
    }
}
