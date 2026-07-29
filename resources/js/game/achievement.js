import { state, dom, config } from './state.js';

    // ---------------------------------------------------------------
    // Popup Achievement Terbuka — konten 100% dari respons server
    // (GameSessionService::attachNewlyUnlockedAchievements()), tidak ada
    // achievement/warna/icon yang di-hardcode di sini. Beberapa achievement
    // bisa terbuka dalam satu giliran sekaligus, jadi ditampilkan satu per
    // satu lewat antrean sederhana.
    // ---------------------------------------------------------------
    const ACHIEVEMENT_BADGE_GRADIENT = {
        green: 'linear-gradient(135deg, #4ade80, #16a34a)',
        blue: 'linear-gradient(135deg, #60a5fa, #2563eb)',
        orange: 'linear-gradient(135deg, #fb923c, #ea580c)',
        purple: 'linear-gradient(135deg, #c084fc, #9333ea)',
        pink: 'linear-gradient(135deg, #f472b6, #db2777)',
        amber: 'linear-gradient(135deg, #fbbf24, #d97706)',
        turquoise: 'linear-gradient(135deg, #2dd4bf, #0d9488)',
    };
    const ACHIEVEMENT_BADGE_CODE_COLOR = {
        green: '#16a34a', blue: '#2563eb', orange: '#ea580c', purple: '#9333ea',
        pink: '#db2777', amber: '#d97706', turquoise: '#0d9488',
    };
    const CONFETTI_COLORS = ['#22c55e', '#3b82f6', '#f97316', '#a855f7', '#ec4899', '#f59e0b', '#14b8a6'];

    let achievementQueue = [];

    function queueAchievementUnlocks(list) {
        achievementQueue.push(...list);
        if (achievementQueue.length === list.length) {
            showNextAchievementUnlock();
        }
    }

    function showNextAchievementUnlock() {
        const achievement = achievementQueue.shift();
        if (!achievement) return;

        const badgeEl = document.getElementById('achievement-unlock-badge');
        const kodeEl = document.getElementById('achievement-unlock-kode');
        const namaEl = document.getElementById('achievement-unlock-nama');
        const deskripsiEl = document.getElementById('achievement-unlock-deskripsi');
        const poinEl = document.getElementById('achievement-unlock-poin');

        const gradient = ACHIEVEMENT_BADGE_GRADIENT[achievement.warna_badge] ?? ACHIEVEMENT_BADGE_GRADIENT.blue;
        const codeColor = ACHIEVEMENT_BADGE_CODE_COLOR[achievement.warna_badge] ?? ACHIEVEMENT_BADGE_CODE_COLOR.blue;

        badgeEl.style.background = gradient;
        badgeEl.innerHTML = '';
        badgeEl.classList.remove('achievement-badge-pop');
        void badgeEl.offsetWidth; // restart animasi kalau achievement kedua muncul berturut-turut
        badgeEl.classList.add('achievement-badge-pop');

        const template = document.querySelector(`#achievement-icon-templates template[data-icon="${achievement.icon}"]`)
            ?? document.querySelector('#achievement-icon-templates template[data-icon="trophy"]');
        if (template) {
            badgeEl.appendChild(template.content.cloneNode(true));
        }

        kodeEl.textContent = achievement.kode;
        kodeEl.style.color = codeColor;
        namaEl.textContent = achievement.nama;
        deskripsiEl.textContent = achievement.deskripsi ?? '';
        deskripsiEl.classList.toggle('hidden', !achievement.deskripsi);

        if (achievement.reward_poin > 0) {
            poinEl.textContent = `+${achievement.reward_poin} Poin`;
            poinEl.classList.remove('hidden');
        } else {
            poinEl.classList.add('hidden');
        }

        spawnConfetti();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'achievement-unlock-modal' }));
    }

    function spawnConfetti() {
        const layer = document.getElementById('achievement-confetti-layer');
        if (!layer) return;
        layer.innerHTML = '';

        for (let i = 0; i < 24; i++) {
            const piece = document.createElement('span');
            piece.className = 'confetti-piece';
            piece.style.left = `${Math.random() * 100}%`;
            piece.style.background = CONFETTI_COLORS[Math.floor(Math.random() * CONFETTI_COLORS.length)];
            piece.style.setProperty('--confetti-spin', `${360 + Math.random() * 360}deg`);
            piece.style.animationDelay = `${Math.random() * 300}ms`;
            piece.style.borderRadius = Math.random() > 0.5 ? '9999px' : '2px';
            layer.appendChild(piece);
        }

        setTimeout(() => { layer.innerHTML = ''; }, 2000);
    }

    document.getElementById('achievement-unlock-next')?.addEventListener('click', () => {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'achievement-unlock-modal' }));
        if (achievementQueue.length > 0) {
            setTimeout(() => showNextAchievementUnlock(), 250);
        }
    });

    function applySessionState(session, { pawnMode = 'diff', skipPawnIds = new Set(), deferOutcome = false } = {}) {
        state.latestSession = session;

        if (pawnMode === 'instant') {
            session.players.forEach((p) => placePawnAt(getOrCreatePawnEl(p), p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players)));
            syncKnownPositions(session);
        } else if (pawnMode === 'skip') {
            session.players.forEach((p) => {
                if (!skipPawnIds.has(p.id)) {
                    placePawnAt(getOrCreatePawnEl(p), p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
                }
            });
            syncKnownPositions(session);
        } else {
            state.animationChain = state.animationChain.then(() => animateExternalDiff(session)).then(() => syncKnownPositions(session));
        }

        renderPlayerPanels(session);
        updateTurnIndicator(session);
        renderPaused(session);
        renderInventoryUI(session);

        if (!deferOutcome) {
            finalizeOutcome(session);
        }

        joinRealtimeChannel(session);
        startHeartbeat(session);
        stopRealtimeIfSessionOver(session);
    }
