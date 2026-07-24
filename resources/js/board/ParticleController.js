/**
 * Particle ringan (✨⭐🍃) untuk efek naik tangga / reaksi ular — DOM span
 * biasa yang dianimasikan lewat CSS (bukan canvas, bukan library), dibuang
 * otomatis setelah animasinya selesai. Dibatasi jumlah maksimum yang hidup
 * bersamaan supaya tidak ada beban repaint berlebihan walau banyak konektor
 * terpicu berdekatan.
 */
const EMOJI = {
    sparkle: ['✨', '⭐'],
    leaf: ['🍃'],
    dust: null, // dust pakai bentuk CSS polos (bukan emoji), lihat app.css .board-particle-dust
};

const MAX_LIVE = 40;

export class ParticleController {
    constructor(layerEl) {
        this.layer = layerEl;
        this.liveCount = 0;
    }

    /**
     * @param {number} xPercent posisi pusat ledakan, % terhadap layer
     * @param {number} yPercent posisi pusat ledakan, % terhadap layer
     * @param {'sparkle'|'leaf'|'dust'} kind
     * @param {number} count
     */
    burst(xPercent, yPercent, kind = 'sparkle', count = 6) {
        if (!this.layer) return;

        const pool = EMOJI[kind];
        const budget = Math.max(0, Math.min(count, MAX_LIVE - this.liveCount));

        for (let i = 0; i < budget; i++) {
            const span = document.createElement('span');
            span.className = kind === 'dust' ? 'board-particle board-particle-dust' : 'board-particle board-particle-emoji';
            if (pool) {
                span.textContent = pool[Math.floor(Math.random() * pool.length)];
            }

            const angle = Math.random() * Math.PI * 2;
            const distance = 4 + Math.random() * 6; // % dari layer
            span.style.left = `${xPercent}%`;
            span.style.top = `${yPercent}%`;
            span.style.setProperty('--px', `${Math.cos(angle) * distance}%`);
            span.style.setProperty('--py', `${Math.sin(angle) * distance}%`);
            span.style.animationDelay = `${Math.random() * 120}ms`;

            this.layer.appendChild(span);
            this.liveCount++;

            span.addEventListener('animationend', () => {
                span.remove();
                this.liveCount--;
            }, { once: true });
        }
    }
}
