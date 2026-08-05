import { state, dom, config } from './state.js';
import { initials } from './utils.js';
import { pawnColorStyle } from './pawn.js';

    // ---------------------------------------------------------------
    // Panel pemain / robot
    // ---------------------------------------------------------------
    function renderPlayerPanels(session) {
        dom.playerPanelListEl.innerHTML = '';

        session.players.forEach((p) => {
            const template = p.is_robot ? dom.robotCardTemplate : dom.playerCardTemplate;
            const node = template.content.firstElementChild.cloneNode(true);

            const isMe = !p.is_robot && state.myGamePlayerId === p.id;
            const isTurn = session.current_turn_game_player_id === p.id && session.status === 'playing';

            if (!p.is_robot) {
                node.querySelector('[data-field="avatar"]').textContent = initials(p.nama);
                node.querySelector('[data-field="nama"]').textContent = p.nama ?? 'Pemain';
                if (isMe) node.querySelector('.me-badge')?.classList.remove('hidden');
            }

            const turnBadge = node.querySelector('.turn-badge');
            if (isTurn) {
                turnBadge?.classList.remove('hidden');
                turnBadge?.classList.add('flex');
                node.querySelector('.turn-glow')?.classList.add('opacity-100', 'ring-2', 'ring-accent-300');
                node.classList.add('border-accent-300');
            }

            node.querySelector('[data-field="skor"]').textContent = p.skor ?? 0;
            node.querySelector('[data-field="posisi"]').textContent = p.posisi_pion ?? 0;

            const akurasiEl = node.querySelector('[data-field="akurasi"]');
            const akurasi = p.accuracy !== null && p.accuracy !== undefined ? parseFloat(p.accuracy) : null;
            akurasiEl.textContent = akurasi !== null ? `${Math.round(akurasi)}%` : '—';

            const progressBar = node.querySelector('[data-field="progress-bar"]');
            const pct = config.jumlahPetak > 0 ? Math.min(100, Math.max(0, (p.posisi_pion / config.jumlahPetak) * 100)) : 0;
            progressBar.style.width = `${pct}%`;

            dom.playerPanelListEl.appendChild(node);
        });
    }

    function renderInventoryUI(session) {
        const inventoryContainerEl = document.getElementById('player-inventory-container');
        const inventoryListEl = document.getElementById('player-inventory-list');
        const emptyTextEl = document.getElementById('empty-inventory-text');

        if (!inventoryContainerEl || !inventoryListEl) return;

        const me = session.players.find((p) => p.id === state.myGamePlayerId);
        if (!me) return;

        const inventory = me.inventory || [];
        
        inventoryListEl.innerHTML = '';

        if (inventory.length === 0) {
            if (!emptyTextEl) {
                const newEmptyText = document.createElement('p');
                newEmptyText.id = 'empty-inventory-text';
                newEmptyText.className = 'text-xs text-slate-400 italic';
                newEmptyText.textContent = 'Kosong';
                inventoryListEl.appendChild(newEmptyText);
            } else {
                emptyTextEl.style.display = 'block';
                inventoryListEl.appendChild(emptyTextEl);
            }
            return;
        }

        if (emptyTextEl) {
            emptyTextEl.style.display = 'none';
        }

        const itemImages = {
            'double_dice': '/images/powerups/double_dice.jpg',
            'snake_shield': '/images/powerups/snake_shield.jpg',
            'teleport_forward': '/images/powerups/teleport_forward.jpg',
            'curse_dice': '/images/powerups/curse_dice.jpg'
        };

        const itemNames = {
            'double_dice': 'Dadu Ganda',
            'snake_shield': 'Perisai Ular',
            'teleport_forward': 'Teleport',
            'curse_dice': 'Kutukan'
        };

        inventory.forEach((itemId) => {
            const btn = document.createElement('button');
            btn.className = 'flex flex-col items-center justify-center p-2 rounded-lg bg-white border border-slate-200 hover:border-primary-500 hover:shadow-md transition-all cursor-pointer text-center group disabled:opacity-50 disabled:cursor-not-allowed';
            
            const imageSrc = itemImages[itemId] || '/images/powerups/double_dice.jpg'; // fallback
            
            if (itemId === 'hidden') {
                btn.innerHTML = `
                    <div class="w-8 h-8 bg-slate-200 rounded-full mb-1 flex items-center justify-center text-slate-400">?</div>
                    <span class="text-[10px] font-bold text-slate-400 leading-tight">Rahasia</span>
                `;
                btn.disabled = true;
            } else {
                btn.innerHTML = `
                    <img src="${imageSrc}" alt="${itemNames[itemId]}" class="w-8 h-8 object-contain mb-1 group-hover:scale-110 transition-transform">
                    <span class="text-[10px] font-bold text-slate-600 leading-tight">${itemNames[itemId] || 'Item'}</span>
                `;
            }
            
            // Nonaktifkan jika bukan giliran kita
            if (session.current_turn_game_player_id !== state.myGamePlayerId || session.status !== 'playing') {
                btn.disabled = true;
            } else {
                btn.onclick = () => showPowerupUseModal(itemId, btn);
            }

            inventoryListEl.appendChild(btn);
        });
    }

    function showPowerupUseModal(itemId, btnElement) {
        const itemImages = {
            'double_dice': '/images/powerups/double_dice.jpg',
            'snake_shield': '/images/powerups/snake_shield.jpg',
            'teleport_forward': '/images/powerups/teleport_forward.jpg',
            'curse_dice': '/images/powerups/curse_dice.jpg'
        };

        const itemNames = {
            'double_dice': 'Dadu Ganda',
            'snake_shield': 'Perisai Ular',
            'teleport_forward': 'Teleport',
            'curse_dice': 'Kutukan'
        };
        
        const itemDescs = {
            'double_dice': 'Gunakan untuk melempar 2 dadu sekaligus di giliran ini.',
            'snake_shield': 'Gunakan untuk mendapatkan perisai penangkal 1x gigitan ular.',
            'teleport_forward': 'Langsung maju 3 petak ke depan (teleportasi).',
            'curse_dice': 'Kutuk lawan! Lawan selanjutnya maksimal hanya bisa mendapat angka dadu 3.'
        };

        const imgEl = document.getElementById('powerup-use-image');
        const nameEl = document.getElementById('powerup-use-name');
        const descEl = document.getElementById('powerup-use-desc');
        const useBtn = document.getElementById('powerup-use-btn');

        if (imgEl && nameEl && descEl && useBtn) {
            imgEl.src = itemImages[itemId] || itemImages['double_dice'];
            nameEl.textContent = itemNames[itemId] || 'Power Up';
            descEl.textContent = itemDescs[itemId] || 'Pakai item ini?';
            
            useBtn.onclick = () => {
                window.dispatchEvent(new CustomEvent('close-modal', {detail: 'powerup-use-modal'}));
                executePowerUp(itemId, btnElement);
            };

            window.dispatchEvent(new CustomEvent('open-modal', {detail: 'powerup-use-modal'}));
        }
    }

    async function executePowerUp(itemId, btnElement) {
        if (btnElement) btnElement.disabled = true;
        
        try {
            const powerupUrl = config.rollUrl.replace('/roll', '/powerup');
            console.log('[PowerUp] Sending use request:', itemId, 'URL:', powerupUrl);
            const result = await postJson(powerupUrl, { item_id: itemId });
            console.log('[PowerUp] Response:', result);
            
            showToast(result.message || 'Item berhasil digunakan!');
            
            // Refresh state agar inventory dan buff terbaru muncul
            await loadState();
        } catch (err) {
            console.error('[PowerUp] Gagal menggunakan item:', err);
            showToast(err.message || 'Gagal menggunakan item.');
            if (btnElement) btnElement.disabled = false;
        }
    }


    function updateTurnIndicator(session) {
        const currentPlayer = session.players.find((p) => p.id === session.current_turn_game_player_id);
        const isMyTurn = currentPlayer && !currentPlayer.is_robot && state.myGamePlayerId === currentPlayer.id;

        const turnActiveDotEl = document.getElementById('turn-active-dot');
        const turnActiveDotMobileEl = document.getElementById('turn-active-dot-mobile');
        const turnTextEl = document.getElementById('turn-indicator-text');
        const turnTextMobileEl = document.getElementById('turn-indicator-text-mobile');
        const turnSubtextMobileEl = document.getElementById('turn-subtext-mobile');
        const turnAvatarRingEl = document.getElementById('turn-avatar-ring');
        const diceGlowWrapEl = document.getElementById('dice-glow-wrap');

        clearInterval(state.turnCountdownInterval);

        if (session.status !== 'playing') {
            if (turnTextEl) turnTextEl.textContent = '';
            if (turnTextMobileEl) turnTextMobileEl.textContent = '';
            dom.turnSubtextEl.textContent = '';
            if (turnSubtextMobileEl) turnSubtextMobileEl.textContent = '';
            dom.turnAvatarEl.textContent = '';
            dom.rollButton.classList.add('hidden');
            if (turnActiveDotEl) turnActiveDotEl.classList.add('hidden');
            if (turnActiveDotMobileEl) turnActiveDotMobileEl.classList.add('hidden');
            if (turnAvatarRingEl) {
                turnAvatarRingEl.classList.remove('animate-ping-slow', 'opacity-100');
                turnAvatarRingEl.classList.add('opacity-0');
            }
            if (diceGlowWrapEl) diceGlowWrapEl.classList.remove('animate-float', 'drop-shadow-lg');
            if (typeof setTileGlow === 'function') setTileGlow(null); // Assuming setTileGlow is globally available
            return;
        }

        const avatarInitial = currentPlayer ? (currentPlayer.is_robot ? '\u{1F916}' : initials(currentPlayer.nama)) : '?';
        dom.turnAvatarEl.textContent = avatarInitial;
        const mobileAvatar = document.getElementById('turn-avatar-mobile');
        if (mobileAvatar) mobileAvatar.textContent = avatarInitial;
        
        const avatarClass = `relative z-10 flex h-12 w-12 items-center justify-center rounded-full text-sm font-black text-white shadow-md ${currentPlayer?.is_robot ? 'bg-gradient-to-br from-slate-600 to-slate-800' : 'bg-gradient-to-br from-primary-400 to-primary-600'}`;
        dom.turnAvatarEl.className = avatarClass;
        if (mobileAvatar) mobileAvatar.className = `flex h-10 w-10 items-center justify-center rounded-full text-xs font-black text-white shadow-md ${currentPlayer?.is_robot ? 'bg-gradient-to-br from-slate-600 to-slate-800' : 'bg-gradient-to-br from-primary-400 to-primary-600'}`;

        const updateText = (countdownStr = '') => {
            if (isMyTurn) {
                const txt = `Giliran Anda ${countdownStr}`.trim();
                if (turnTextEl) turnTextEl.textContent = txt;
                if (turnTextMobileEl) turnTextMobileEl.textContent = txt;
                
                if (turnActiveDotEl) {
                    turnActiveDotEl.classList.remove('hidden');
                    turnActiveDotEl.classList.add('flex');
                }
                if (turnActiveDotMobileEl) {
                    turnActiveDotMobileEl.classList.remove('hidden');
                    turnActiveDotMobileEl.classList.add('flex');
                }
                if (turnAvatarRingEl) {
                    turnAvatarRingEl.classList.remove('opacity-0');
                    turnAvatarRingEl.classList.add('animate-ping-slow', 'opacity-100');
                }
            } else {
                const txt = `Menunggu giliran ${currentPlayer?.is_robot ? 'Robot' : (currentPlayer?.nama ?? '...')} ${countdownStr}`.trim();
                if (turnTextEl) turnTextEl.textContent = txt;
                if (turnTextMobileEl) turnTextMobileEl.textContent = txt;
                
                if (turnActiveDotEl) turnActiveDotEl.classList.add('hidden');
                if (turnActiveDotMobileEl) turnActiveDotMobileEl.classList.add('hidden');
                if (turnAvatarRingEl) {
                    turnAvatarRingEl.classList.remove('animate-ping-slow', 'opacity-100');
                    turnAvatarRingEl.classList.add('opacity-0');
                }
            }
        };

        if (session.current_turn_remaining_seconds !== null && session.current_turn_remaining_seconds !== undefined) {
            let remaining = session.current_turn_remaining_seconds;
            const tick = () => {
                if (remaining > 0) {
                    updateText(`(${remaining}s)`);
                    remaining--;
                } else {
                    updateText(`(Auto...)`);
                    clearInterval(state.turnCountdownInterval);
                }
            };
            
            tick();
            state.turnCountdownInterval = setInterval(tick, 1000);
        } else {
            updateText();
        }

        const subtext = currentPlayer ? `Posisi: ${currentPlayer.posisi_pion} • Skor: ${currentPlayer.skor}` : '';
        dom.turnSubtextEl.textContent = subtext;
        if (turnSubtextMobileEl) turnSubtextMobileEl.textContent = subtext;

        if (typeof setTileGlow === 'function') setTileGlow(currentPlayer?.posisi_pion);

        const questionPending = !!session.active_question;
        const canRoll = isMyTurn && !questionPending;
        dom.rollButton.classList.toggle('hidden', !canRoll);
        dom.rollButton.classList.toggle('flex', canRoll);

        const disabledOverlay = document.getElementById('dice-3d-disabled-overlay');
        if (disabledOverlay) {
            disabledOverlay.classList.toggle('hidden', canRoll);
            disabledOverlay.classList.toggle('flex', !canRoll);
        }
    }

    function renderFinished(session) {
        if (session.status === 'finished') {
            const winner = session.players.find((p) => p.id === session.winner_game_player_id);
            const isMeWinner = winner && state.myGamePlayerId === winner.id;

            document.getElementById('finished-title').textContent = isMeWinner
                ? 'Selamat, Anda menang!'
                : 'Permainan selesai';
            document.getElementById('finished-subtitle').textContent = isMeWinner
                ? 'Anda berhasil mencapai garis Finish lebih dulu.'
                : `${winner?.is_robot ? 'Robot' : (winner?.nama ?? 'Pemain lain')} mencapai Finish lebih dulu.`;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'game-finished-modal' }));
            dom.rollButton.classList.add('hidden');
        } else if (session.status === 'abandoned') {
            document.getElementById('finished-title').textContent = 'Permainan dihentikan';
            document.getElementById('finished-subtitle').textContent = 'Permainan ini telah dihentikan sebelum selesai.';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'game-finished-modal' }));
            dom.rollButton.classList.add('hidden');
        }
    }

    function renderPaused(session) {
        clearInterval(state.pausedCountdownInterval);

        if (session.status !== 'paused' || !dom.pausedBannerEl) {
            dom.pausedBannerEl?.classList.add('hidden');
            dom.pausedBannerEl?.classList.remove('flex');
            return;
        }

        dom.pausedBannerEl.classList.remove('hidden');
        dom.pausedBannerEl.classList.add('flex');

        const update = () => {
            if (!session.reconnect_deadline_at) {
                dom.pausedTimerEl.textContent = '';
                return;
            }
            const remaining = Math.max(0, Math.floor((new Date(session.reconnect_deadline_at).getTime() - Date.now()) / 1000));
            dom.pausedTimerEl.textContent = `${remaining}s`;
        };

        update();
        state.pausedCountdownInterval = setInterval(update, 1000);
    }
