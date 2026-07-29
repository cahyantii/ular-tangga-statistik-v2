import { state, dom, config } from './state.js';
import { CoordinateHelper } from '../board/CoordinateHelper.js';

    // ---------------------------------------------------------------
    // Util
    // ---------------------------------------------------------------
    function delay(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function initials(name) {
        return (name || '?').trim().charAt(0).toUpperCase();
    }

    const coordHelper = new CoordinateHelper(config.jumlahKolom, config.totalRows);

    function cellCenterPercent(posisi) {
        return coordHelper.cellCenterPercent(Math.max(1, Math.min(posisi, config.jumlahPetak)));
    }

    // ---------------------------------------------------------------
    // Geometri jalur ular/tangga (dipakai animasi pion mengikuti jalur asli,
    // bukan garis lurus/teleport) — rumus di bawah SENGAJA sama persis dengan
    // resources/views/components/game/snake.blade.php & ladder.blade.php,
    // hanya bekerja dalam satuan persen (%) alih-alih unit grid SVG, supaya
    // titik yang dihasilkan pas menempel di kurva yang benar-benar dirender.
    // ---------------------------------------------------------------
    function snakeCubicPoints(fromPosisi, toPosisi) {
        const from = cellCenterPercent(fromPosisi);
        const to = cellCenterPercent(toPosisi);
        const dx = to.left - from.left;
        const dy = to.top - from.top;
        const len = Math.max(Math.sqrt(dx * dx + dy * dy), 0.001);
        const ux = dx / len;
        const uy = dy / len;
        const px = -uy;
        const py = ux;
        const wave = Math.min(len * 0.32, 11);

        return {
            x0: from.left, y0: from.top,
            x1: from.left + ux * len * 0.25 + px * wave, y1: from.top + uy * len * 0.25 + py * wave,
            x2: from.left + ux * len * 0.75 - px * wave, y2: from.top + uy * len * 0.75 - py * wave,
            x3: to.left, y3: to.top,
        };
    }

    function cubicPointAt(curve, t) {
        const mt = 1 - t;
        return {
            left: mt * mt * mt * curve.x0 + 3 * mt * mt * t * curve.x1 + 3 * mt * t * t * curve.x2 + t * t * t * curve.x3,
            top: mt * mt * mt * curve.y0 + 3 * mt * mt * t * curve.y1 + 3 * mt * t * t * curve.y2 + t * t * t * curve.y3,
        };
    }

    function linearPointAt(fromPosisi, toPosisi, t) {
        const from = cellCenterPercent(fromPosisi);
        const to = cellCenterPercent(toPosisi);

        return {
            left: from.left + (to.left - from.left) * t,
            top: from.top + (to.top - from.top) * t,
        };
    }

    /**
     * Menganimasikan pion mengikuti jalur konektor sesungguhnya (bezier untuk
     * ular, garis lurus untuk tangga — sama seperti SVG-nya) lewat
     * requestAnimationFrame, bukan CSS transition left/top biasa (yang akan
     * memotong lurus antar dua titik, mengabaikan lekukan ular).
     */
    function animateAlongConnector(el, fromPosisi, toPosisi, jenis, durationMs) {
        return new Promise((resolve) => {
            const curve = jenis === 'ular' ? snakeCubicPoints(fromPosisi, toPosisi) : null;
            const start = performance.now();
            el.classList.add('pawn-path-follow');

            function frame(now) {
                const t = Math.min((now - start) / durationMs, 1);
                const eased = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; // ease-in-out
                const point = curve ? cubicPointAt(curve, eased) : linearPointAt(fromPosisi, toPosisi, eased);
                el.style.left = `${point.left}%`;
                el.style.top = `${point.top}%`;

                if (t < 1) {
                    requestAnimationFrame(frame);
                } else {
                    el.classList.remove('pawn-path-follow');
                    resolve();
                }
            }

            requestAnimationFrame(frame);
        });
    }

    function spawnParticles(el, kind, count) {
        const layer = document.createElement('div');
        layer.className = 'pawn-sparkle-layer';
        el.appendChild(layer);

        for (let i = 0; i < count; i++) {
            const p = document.createElement('span');
            p.className = kind === 'dust' ? 'pawn-dust' : 'pawn-sparkle';
            const angle = Math.random() * Math.PI * 2;
            const distance = 14 + Math.random() * 16;
            p.style.setProperty('--sx', `${Math.cos(angle) * distance}px`);
            p.style.setProperty('--sy', `${Math.sin(angle) * distance}px`);
            p.style.animationDelay = `${Math.random() * 120}ms`;
            layer.appendChild(p);
        }

        setTimeout(() => layer.remove(), 900);
    }

    function showToast(message) {
        dom.toastEl.textContent = message;
        dom.toastEl.classList.remove('hidden');
        dom.toastEl.classList.add('flex');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
            dom.toastEl.classList.add('hidden');
            dom.toastEl.classList.remove('flex');
        }, 3800);
    }
