import { state, dom, config } from './state.js';
import { delay, cellCenterPercent, animateAlongConnector, spawnParticles } from './utils.js';

    // ---------------------------------------------------------------
    // Pion & animasi pergerakan
    // ---------------------------------------------------------------
    function pawnColorStyle(player) {
        if (player.is_robot) return { bg: '#475569', ring: '#e2e8f0' };
        return { bg: player.pawn_color === 'red' ? '#e11d48' : (player.pawn_color || '#1d4ed8'), ring: '#ffffff' };
    }

    function getOrCreatePawnEl(player) {
        let el = dom.pawnLayer.querySelector(`[data-pawn-id="${player.id}"]`);
        if (el) return el;

        const { bg } = pawnColorStyle(player);
        el = document.createElement('div');
        el.dataset.pawnId = String(player.id);
        el.className = 'game-pawn';
        // Pion 3D lebih tinggi dan ramping
        el.style.width = `${(100 / config.jumlahKolom) * 0.45}%`;
        el.style.height = `${(100 / config.totalRows) * 0.8}%`;
        
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
        el.title = player.is_robot ? 'Robot' : (player.nama ?? 'Pemain');
        dom.pawnLayer.appendChild(el);
        return el;
    }

    function placePawnAt(el, posisi, stackIndex = 0) {
        // posisi_pion 0 = belum bergerak dari Start; tampilkan pion bertengger
        // di kotak Start (posisi 1) alih-alih menyembunyikannya.
        el.style.opacity = '1';
        const { left, top } = cellCenterPercent(Math.max(posisi, 1));
        const nudge = stackIndex * 5;
        el.style.left = `calc(${left}% + ${nudge}px)`;
        el.style.top = `calc(${top}% - ${nudge}px)`;
    }

    /**
     * Index pion di antara pion LAIN yang kebetulan berdiri di kotak yang
     * sama (`posisi`) — dipakai `placePawnAt(..., stackIndex)` supaya pion
     * yang menumpuk digeser sedikit (nudge), bukan tumpang tindih sempurna.
     * Makin sering terjadi dengan game 3-6 pemain dibanding 2 pemain, jadi
     * digeneralisasi di sini alih-alih dihitung manual tiap call site.
     * Urutan diambil dari `turn_order` (stabil, sama di semua klien) supaya
     * offset-nya konsisten walau urutan array `players` di payload beda-beda.
     */
    function stackIndexAt(posisi, playerId, players = state.latestSession?.players ?? []) {
        const sameTile = players
            .filter((p) => p.posisi_pion === posisi)
            .sort((a, b) => (a.turn_order ?? 0) - (b.turn_order ?? 0));
        const idx = sameTile.findIndex((p) => p.id === playerId);
        return idx === -1 ? 0 : idx;
    }

    function setTileGlow(posisi) {
        dom.boardEl.querySelectorAll('.tile-active-ring').forEach((ring) => ring.classList.remove('opacity-100'));
        if (!posisi) return;
        const cell = dom.boardEl.querySelector(`[data-posisi="${posisi}"] .tile-active-ring`);
        cell?.classList.add('opacity-100');
    }

    function bumpPawn(el) {
        el.classList.add('pawn-bump');
        setTimeout(() => el.classList.remove('pawn-bump'), 450);
    }

    /**
     * Menggerakkan satu pion selangkah demi selangkah dari `from` ke `to`
     * (bukan langsung berpindah), lalu jika posisi akhir sungguhan (dari
     * server) berbeda dari hasil dadu murni, lanjutkan dengan animasi
     * tangga (naik) atau ular (meluncur turun) menuju posisi akhir itu.
     */
    async function animatePlayerTurn(player, fromPosisi, result) {
        const el = getOrCreatePawnEl(player);
        const finalPosisi = player.posisi_pion;
        const nilaiDadu = result.nilai_dadu ?? null;

        if (result.type === 'blocked' || nilaiDadu === null) {
            bumpPawn(el);
            return;
        }

        const landingPosisi = Math.min(fromPosisi + (nilaiDadu > 0 ? nilaiDadu : 0), config.jumlahPetak);

        if (nilaiDadu < 0) {
            const finalPosisiTarget = Math.max(fromPosisi + nilaiDadu, 1);
            for (let step = fromPosisi - 1; step >= finalPosisiTarget; step--) {
                const stackIndex = step === finalPosisiTarget ? stackIndexAt(step, player.id, result.session?.players) : 0;
                
                el.classList.remove('pawn-hop');
                void el.offsetWidth;
                el.classList.add('pawn-hop');
                
                placePawnAt(el, step, stackIndex);
                await delay(400);
            }
            el.classList.remove('pawn-hop');
            return;
        }

        for (let step = fromPosisi + 1; step <= landingPosisi; step++) {
            // Langkah terakhir = posisi mendarat sungguhan (kalau tidak ada
            // konektor, ini JUGA posisi akhir) - beri stackIndex yang benar di
            // situ; langkah transit sebelumnya cukup stackIndex 0 (lewat saja).
            const stackIndex = step === landingPosisi ? stackIndexAt(step, player.id, result.session?.players) : 0;
            
            // Beri animasi loncat saat pindah petak
            el.classList.remove('pawn-hop');
            void el.offsetWidth; // Force reflow agar animasi me-restart
            el.classList.add('pawn-hop');
            
            placePawnAt(el, step, stackIndex);
            // eslint-disable-next-line no-await-in-loop
            await delay(400);
        }
        el.classList.remove('pawn-hop');

        // Tampilkan Modal Gacha JIKA INI MYSTERY TILE
        if (result.type === 'mystery' && result.item_id && player.id === state.myGamePlayerId) {
            await showGachaModal(result);
        }

        if (finalPosisi !== landingPosisi) {
            const isBounce = landingPosisi === config.jumlahPetak && !config.konektorByStart.get(landingPosisi) && finalPosisi < landingPosisi;

            if (isBounce) {
                await delay(120);
                for (let step = landingPosisi - 1; step >= finalPosisi; step--) {
                    const stackIndex = step === finalPosisi ? stackIndexAt(step, player.id, result.session?.players) : 0;
                    el.classList.remove('pawn-hop');
                    void el.offsetWidth;
                    el.classList.add('pawn-hop');
                    
                    placePawnAt(el, step, stackIndex);
                    // eslint-disable-next-line no-await-in-loop
                    await delay(400);
                }
                el.classList.remove('pawn-hop');
                placePawnAt(el, finalPosisi, stackIndexAt(finalPosisi, player.id, result.session?.players));
                await delay(200);
            } else {
                // Konektor diterapkan: tangga (naik) atau ular (turun). Ambil jenis
                // konektor sesungguhnya dari data papan (bukan ditebak dari arah
                // gerak) supaya jalur animasi (lurus/bezier) selalu tepat sesuai SVG.
                const konektor = config.konektorByStart.get(landingPosisi);
                const jenis = konektor?.jenis ?? (finalPosisi > landingPosisi ? 'tangga' : 'ular');
                const isNaik = jenis === 'tangga';

                await delay(120);

                if (isNaik) {
                    // Efek visual tangga (glow+sparkle) — best-effort, lihat catatan di atas file.
                    await window.BoardVisuals?.reactLadder?.(landingPosisi);
                    await animateAlongConnector(el, landingPosisi, finalPosisi, 'tangga', 900);
                    spawnParticles(el, 'sparkle', 10);
                    el.classList.add('pawn-climb');
                    setTimeout(() => el.classList.remove('pawn-climb'), 650);
                } else {
                    // Efek "ular menggigit" (glow+lidah+kepala bergerak) SEBELUM pion
                    // meluncur turun — best-effort, lihat catatan di atas file.
                    await window.BoardVisuals?.reactSnake?.(landingPosisi);
                    dom.boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, landingPosisi, finalPosisi, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => dom.boardEl.classList.remove('board-shake'), 400);
                }

                placePawnAt(el, finalPosisi, stackIndexAt(finalPosisi, player.id, result.session?.players));
                await delay(200);
            }
        }

    }

    function showGachaModal(result) {
        return new Promise((resolve) => {
            const itemImages = {
                'double_dice': '/images/powerups/double_dice.jpg',
                'snake_shield': '/images/powerups/snake_shield.jpg',
                'teleport_forward': '/images/powerups/teleport_forward.jpg',
                'curse_dice': '/images/powerups/curse_dice.jpg'
            };
            
            const gachaImg = document.getElementById('gacha-image');
            const gachaName = document.getElementById('gacha-item-name');
            const gachaDesc = document.getElementById('gacha-item-desc');
            const normalActions = document.getElementById('gacha-actions-normal');
            const klaimBtn = document.getElementById('gacha-klaim-btn');
            
            if (!gachaImg || !gachaName || !gachaDesc) {
                resolve();
                return;
            }

            gachaImg.src = itemImages[result.item_id] || itemImages['double_dice'];
            gachaName.textContent = result.item_name || 'Item Misteri';
            gachaDesc.textContent = result.item_description || '(Efek Langsung)';
            
            // Reset animasi
            gachaImg.style.transform = 'scale(0)';
            gachaName.classList.remove('opacity-100');
            gachaDesc.classList.remove('opacity-100');
            if (normalActions) normalActions.classList.remove('opacity-100');
            
            const cleanupAndClose = () => {
                if (klaimBtn) klaimBtn.onclick = null;
                window.dispatchEvent(new CustomEvent('close-modal', {detail: 'gacha-modal'}));
                resolve();
            };

            if (klaimBtn) {
                klaimBtn.onclick = () => {
                    cleanupAndClose();
                };
            }

            window.dispatchEvent(new CustomEvent('open-modal', {detail: 'gacha-modal'}));
            
            // Mulai sequence animasi
            setTimeout(() => gachaImg.style.transform = 'scale(1)', 100);
            setTimeout(() => gachaName.classList.add('opacity-100'), 400);
            setTimeout(() => gachaDesc.classList.add('opacity-100'), 600);
            setTimeout(() => {
                if (normalActions) normalActions.classList.add('opacity-100');
            }, 900);
        });
    }
