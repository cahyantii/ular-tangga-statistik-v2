/**
 * Markup & warna pion — SATU-SATUNYA sumber kebenaran dipakai bersama oleh
 * animasi pion gameplay (resources/js/game/pawn.js) dan pion contoh statis
 * di preview papan admin (resources/js/admin-board-preview.js), supaya
 * keduanya selalu terlihat identik (lihat catatan di board.blade.php soal
 * preview admin yang harus konsisten dengan tampilan pemain).
 */
export function pawnColorStyle(player) {
    if (player.is_robot) return { bg: '#475569', ring: '#e2e8f0' };
    return { bg: player.pawn_color === 'red' ? '#e11d48' : (player.pawn_color || '#1d4ed8'), ring: '#ffffff' };
}

/**
 * @param {{ bg: string, title: string, widthPercent: number, heightPercent: number }} options
 * @returns {HTMLElement} elemen `.game-pawn` siap diposisikan (left/top) & ditempel ke #pawn-layer.
 */
export function createPawnElement({ bg, title, widthPercent, heightPercent }) {
    const el = document.createElement('div');
    el.className = 'game-pawn';
    el.style.width = `${widthPercent}%`;
    el.style.height = `${heightPercent}%`;

    el.innerHTML = `
        <div class="pawn-body">
            <svg viewBox="0 0 100 150" class="pawn-svg">
                <!-- Base Shadow -->
                <ellipse cx="50" cy="140" rx="35" ry="10" fill="#000000" opacity="0.35" class="pawn-shadow" />
                <g class="pawn-shape" transform-origin="50px 140px">
                    <!-- Body (Base to Neck) -->
                    <path d="M 22,130 Q 50,150 78,130 L 63,45 L 37,45 Z" fill="${bg}" stroke="#111827" stroke-width="5" stroke-linejoin="round" />
                    <!-- Head -->
                    <circle cx="50" cy="35" r="30" fill="${bg}" stroke="#111827" stroke-width="5" />
                    <!-- Highlights (Glossy 3D) -->
                    <path d="M 32,20 A 18,18 0 0,0 25,40" stroke="#ffffff" stroke-width="6" fill="none" stroke-linecap="round" opacity="0.75" />
                    <path d="M 32,120 L 41,55" stroke="#ffffff" stroke-width="5" fill="none" stroke-linecap="round" opacity="0.5" />
                </g>
            </svg>
        </div>
    `;
    el.title = title;
    return el;
}
