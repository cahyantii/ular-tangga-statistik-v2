/**
 * Utilitas animasi bersama untuk seluruh papan — TIDAK menyimpan state
 * gameplay apa pun, murni membantu:
 *  1) Idle loop (kedip/lidah/mulut/napas/kepala) tetap lewat CSS
 *     @keyframes infinite dengan delay/durasi acak-tapi-stabil per elemen
 *     (di-generate sekali saat elemen dibuat) — ringan, girang GPU-friendly,
 *     tidak butuh rAF sama sekali untuk gerakan idle.
 *  2) Reaksi sesaat ("pulse" satu class selama durasi tertentu lalu lepas
 *     lagi) — dipakai SnakeRenderer/LadderRenderer untuk efek "ular
 *     menggigit"/"tangga bersinar".
 *  3) IntersectionObserver: menghentikan SEMUA animasi papan saat papan
 *     tidak terlihat di viewport (mis. discroll ke bawah) supaya tidak ada
 *     kerja render sia-sia — cukup toggle satu class di root board.
 */
export class AnimationController {
    constructor(boardRootEl) {
        this.boardRootEl = boardRootEl;
        this._observer = null;
    }

    /** Angka acak-tapi-stabil (0..range) dari sebuah seed integer — dipakai supaya delay/durasi tiap instance beda tapi konsisten antar render. */
    static seeded(seed, a, b, range) {
        return Math.abs(Math.sin(seed * a + b)) * range;
    }

    /** Tambah class `className` ke `el` selama `durationMs`, lalu lepas otomatis. Aman dipanggil berulang (retrigger). */
    static pulse(el, className, durationMs) {
        if (!el) return Promise.resolve();
        el.classList.remove(className);
        // eslint-disable-next-line no-void
        void el.offsetWidth; // restart animasi CSS kalau dipicu lagi sebelum durasi sebelumnya habis
        el.classList.add(className);

        return new Promise((resolve) => {
            setTimeout(() => {
                el.classList.remove(className);
                resolve();
            }, durationMs);
        });
    }

    /** Papan berhenti "hidup" (animation-play-state: paused lewat CSS) saat di luar viewport. */
    observeVisibility() {
        if (!this.boardRootEl || typeof IntersectionObserver === 'undefined') {
            return;
        }

        this._observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    this.boardRootEl.classList.toggle('board-anims-paused', !entry.isIntersecting);
                });
            },
            { threshold: 0.05 }
        );
        this._observer.observe(this.boardRootEl);
    }

    disconnect() {
        this._observer?.disconnect();
    }
}
