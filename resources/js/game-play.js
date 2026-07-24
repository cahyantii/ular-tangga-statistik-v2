import { CoordinateHelper } from './board/CoordinateHelper.js';

/**
 * Kontroler gameplay Vs Robot & Multiplayer — presentasi visual (papan modern,
 * animasi dadu 3D, animasi pion berjalan, panel pemain/robot, log, dsb).
 *
 * Arsitektur data TIDAK berubah dari versi sebelumnya (keputusan final,
 * dipertahankan): frontend tidak pernah menyimpan state otoritatif, setiap
 * aksi memanggil server, dan tampilan hanya merender ulang respons/state
 * server. Untuk Multiplayer, broadcast Reverb hanya dipakai sebagai sinyal
 * "ada yang berubah" — begitu diterima, kita re-fetch state lewat endpoint
 * yang sama dengan refresh-recovery, bukan merender langsung dari payload
 * broadcast (yang sengaja minim).
 *
 * Catatan: matematika posisi kotak (dulu computeCellPosition duplikat di
 * sini) sekarang dipakai bersama lewat CoordinateHelper (satu-satunya sumber,
 * dipakai juga oleh sistem visual papan di resources/js/board/) — TAPI modul
 * ini sengaja TIDAK mengimpor apa pun dari board-renderer.js (Editor Board
 * Admin) maupun BoardRenderer.js (sistem visual ular/tangga): efek reaktif
 * ular/tangga (lihat window.BoardVisuals?.reactSnake/reactLadder di bawah)
 * dipanggil lewat optional chaining supaya kalau modul visual itu gagal
 * muat, gameplay tetap jalan normal tanpa risiko.
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('game-play-data');
    if (!root) {
        return;
    }

    const stateUrl = root.dataset.stateUrl;
    const rollUrl = root.dataset.rollUrl;
    const answerUrl = root.dataset.answerUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const heartbeatUrl = root.dataset.heartbeatUrl;
    const currentUserId = parseInt(root.dataset.currentUserId, 10);
    const jumlahPetak = parseInt(root.dataset.jumlahPetak, 10);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const boardEl = document.getElementById('game-board');
    const pawnLayer = document.getElementById('pawn-layer');
    const jumlahKolom = parseInt(boardEl.dataset.jumlahKolom, 10);
    const totalRows = parseInt(boardEl.dataset.totalRows, 10);

    // posisi_awal -> { posisi_akhir, jenis } — dipakai supaya animasi naik
    // tangga/turun ular mengikuti jalur SVG asli (bezier untuk ular, garis
    // lurus untuk tangga), bukan garis lurus sembarang atau teleport. Sumber
    // data ini sama persis dengan yang dipakai resources/js/board/
    // BoardRenderer.js menggambar ular/tangga — 100% dari database.
    const konektorByStart = new Map(
        JSON.parse(boardEl.dataset.konektor || '[]').map((k) => [k.posisi_awal, k])
    );

    const rollButton = document.getElementById('roll-dice-button');
    const diceCube = document.getElementById('dice-3d');
    const diceGlowWrap = document.getElementById('dice-glow-wrap');
    const turnAvatarEl = document.getElementById('turn-avatar');
    const turnIndicatorEl = document.getElementById('turn-indicator');
    const turnSubtextEl = document.getElementById('turn-subtext');
    const toastEl = document.getElementById('game-toast');
    const pausedBannerEl = document.getElementById('game-paused-banner');
    const pausedTimerEl = document.getElementById('game-paused-timer');
    const playerPanelListEl = document.getElementById('player-panel-list');
    const logListEl = document.getElementById('game-log-list');
    const logEmptyEl = document.getElementById('game-log-empty');

    const progressPosisiEl = document.getElementById('progress-posisi');
    const progressTotalEl = document.getElementById('progress-total');
    const progressPercentEl = document.getElementById('progress-percent');
    const progressBarEl = document.getElementById('progress-bar');
    progressTotalEl.textContent = jumlahPetak;

    const questionTextEl = document.getElementById('question-text');
    const questionOptionsEl = document.getElementById('question-options');
    const questionTimerEl = document.getElementById('question-timer');
    const questionFeedbackEl = document.getElementById('question-feedback');

    const playerCardTemplate = document.getElementById('player-card-template');
    const robotCardTemplate = document.getElementById('robot-card-template');

    let myGamePlayerId = null;
    let latestSession = null;
    let knownPositions = new Map(); // game_player_id -> posisi_pion (untuk animasi diff)
    let questionCountdownInterval = null;
    let pausedCountdownInterval = null;
    let heartbeatIntervalId = null;
    let presenceChannel = null;
    let leaveConfirmed = false;
    let diceRotation = { x: 0, y: 0 };
    let animationChain = Promise.resolve(); // memastikan animasi pion tidak tumpang tindih

    // ---------------------------------------------------------------
    // Util
    // ---------------------------------------------------------------
    function delay(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    function initials(name) {
        return (name || '?').trim().charAt(0).toUpperCase();
    }

    const coordHelper = new CoordinateHelper(jumlahKolom, totalRows);

    function cellCenterPercent(posisi) {
        return coordHelper.cellCenterPercent(Math.max(1, Math.min(posisi, jumlahPetak)));
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
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.classList.add('flex');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
            toastEl.classList.add('hidden');
            toastEl.classList.remove('flex');
        }, 3800);
    }

    // ---------------------------------------------------------------
    // Log Permainan — dibangun dari respons aksi nyata (bukan data karangan),
    // lihat catatan di resources/views/components/game/log-card.blade.php
    // ---------------------------------------------------------------
    const LOG_ICONS = {
        dice: { icon: '\u{1F3B2}', color: 'text-primary-600' },
        correct: { icon: '✅', color: 'text-secondary-600' },
        wrong: { icon: '❌', color: 'text-rose-600' },
        ladder: { icon: '\u{1FA9C}', color: 'text-secondary-600' },
        snake: { icon: '\u{1F40D}', color: 'text-rose-600' },
        bonus: { icon: '⭐', color: 'text-accent-600' },
        penalti: { icon: '⚠️', color: 'text-rose-600' },
        mystery: { icon: '❓', color: 'text-violet-600' },
        info: { icon: '\u{1F514}', color: 'text-slate-500' },
        finish: { icon: '\u{1F3C6}', color: 'text-accent-600' },
    };

    function pushLog(kind, message) {
        const meta = LOG_ICONS[kind] ?? LOG_ICONS.info;
        logEmptyEl?.remove();

        const li = document.createElement('li');
        li.className = 'flex items-start gap-2 rounded-lg px-2 py-1.5 animate-fade-in-up';
        li.innerHTML = `
            <span class="shrink-0">${meta.icon}</span>
            <span class="font-medium ${meta.color}">${message}</span>
        `;
        logListEl.insertBefore(li, logListEl.firstChild);

        while (logListEl.children.length > 30) {
            logListEl.removeChild(logListEl.lastChild);
        }
    }

    function playerLabel(player) {
        if (!player) return '?';
        if (player.is_robot) return 'Robot';
        return (!player.is_robot && myGamePlayerId === player.id) ? 'Anda' : player.nama;
    }

    function logTurnResult(player, result) {
        const nama = playerLabel(player);

        if (result.nilai_dadu !== undefined && result.nilai_dadu !== null) {
            pushLog('dice', `${nama} melempar dadu ${result.nilai_dadu}`);
        }

        switch (result.type) {
            case 'blocked':
                pushLog('info', `${nama}: langkah terlalu jauh, giliran dilewati`);
                break;
            case 'bonus':
                pushLog('bonus', `${nama} mendapat petak Bonus`);
                break;
            case 'penalti':
                pushLog('penalti', `${nama} kena petak Penalti`);
                break;
            case 'mystery':
                pushLog('mystery', `${nama} kena petak Mystery`);
                break;
            case 'finished':
                pushLog('finish', `${nama} mencapai Finish!`);
                break;
        }
    }

    function logAnswerResult(player, benar) {
        const nama = playerLabel(player);
        pushLog(benar ? 'correct' : 'wrong', benar ? `${nama} menjawab benar` : `${nama} menjawab salah`);
    }

    // ---------------------------------------------------------------
    // Dadu 3D
    // ---------------------------------------------------------------
    const FACE_ROTATIONS = {
        1: { x: 0, y: 0 },
        2: { x: 0, y: -90 },
        3: { x: 90, y: 0 },
        4: { x: -90, y: 0 },
        5: { x: 0, y: 90 },
        6: { x: 0, y: 180 },
    };

    function initDiceSize() {
        if (!diceCube) return;
        const half = diceCube.parentElement.offsetWidth / 2;
        diceCube.style.setProperty('--dice-half', `${half}px`);
    }

    async function animateDiceRoll(finalValue) {
        if (!diceCube) return;

        diceGlowWrap?.classList.add('ring-4', 'ring-accent-300', 'animate-pulse');
        diceCube.classList.add('dice-rolling');

        await delay(650);

        diceCube.classList.remove('dice-rolling');

        const target = FACE_ROTATIONS[finalValue] ?? FACE_ROTATIONS[1];
        const spins = 2; // putaran ekstra penuh supaya terasa "dilempar"
        diceRotation.x += spins * 360 + (target.x - (diceRotation.x % 360));
        diceRotation.y += spins * 360 + (target.y - (diceRotation.y % 360));

        diceCube.style.transform = `rotateX(${diceRotation.x}deg) rotateY(${diceRotation.y}deg)`;

        await delay(800);
        diceGlowWrap?.classList.remove('ring-4', 'ring-accent-300', 'animate-pulse');
    }

    // ---------------------------------------------------------------
    // Pion & animasi pergerakan
    // ---------------------------------------------------------------
    function pawnColorStyle(player) {
        if (player.is_robot) return { bg: '#475569', ring: '#e2e8f0' };
        return { bg: player.pawn_color === 'red' ? '#e11d48' : (player.pawn_color || '#1d4ed8'), ring: '#ffffff' };
    }

    function getOrCreatePawnEl(player) {
        let el = pawnLayer.querySelector(`[data-pawn-id="${player.id}"]`);
        if (el) return el;

        const { bg, ring } = pawnColorStyle(player);
        el = document.createElement('div');
        el.dataset.pawnId = String(player.id);
        el.className = 'game-pawn';
        el.style.width = `${(100 / jumlahKolom) * 0.52}%`;
        el.style.height = `${(100 / totalRows) * 0.52}%`;
        el.innerHTML = `
            <div class="pawn-body" style="background:${bg}; border-color:${ring};">
                <span class="pawn-head" style="background:${ring};"></span>
            </div>
        `;
        el.title = player.is_robot ? 'Robot' : (player.nama ?? 'Pemain');
        pawnLayer.appendChild(el);
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

    function setTileGlow(posisi) {
        boardEl.querySelectorAll('.tile-active-ring').forEach((ring) => ring.classList.remove('opacity-100'));
        if (!posisi) return;
        const cell = boardEl.querySelector(`[data-posisi="${posisi}"] .tile-active-ring`);
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

        const landingPosisi = Math.min(fromPosisi + nilaiDadu, jumlahPetak);

        for (let step = fromPosisi + 1; step <= landingPosisi; step++) {
            placePawnAt(el, step);
            // eslint-disable-next-line no-await-in-loop
            await delay(220);
        }

        if (finalPosisi !== landingPosisi) {
            // Konektor diterapkan: tangga (naik) atau ular (turun). Ambil jenis
            // konektor sesungguhnya dari data papan (bukan ditebak dari arah
            // gerak) supaya jalur animasi (lurus/bezier) selalu tepat sesuai SVG.
            const konektor = konektorByStart.get(landingPosisi);
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
                boardEl.classList.add('board-shake');
                await animateAlongConnector(el, landingPosisi, finalPosisi, 'ular', 900);
                spawnParticles(el, 'dust', 8);
                setTimeout(() => boardEl.classList.remove('board-shake'), 400);
            }

            placePawnAt(el, finalPosisi);
            await delay(200);
        }
    }

    // ---------------------------------------------------------------
    // Panel pemain / robot
    // ---------------------------------------------------------------
    function renderPlayerPanels(session) {
        playerPanelListEl.innerHTML = '';

        session.players.forEach((p) => {
            const template = p.is_robot ? robotCardTemplate : playerCardTemplate;
            const node = template.content.firstElementChild.cloneNode(true);

            const isMe = !p.is_robot && myGamePlayerId === p.id;
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
            const pct = jumlahPetak > 0 ? Math.min(100, Math.max(0, (p.posisi_pion / jumlahPetak) * 100)) : 0;
            progressBar.style.width = `${pct}%`;

            playerPanelListEl.appendChild(node);
        });
    }

    function renderMyProgress(session) {
        const me = session.players.find((p) => p.id === myGamePlayerId);
        if (!me) return;

        const pct = jumlahPetak > 0 ? Math.min(100, Math.max(0, Math.round((me.posisi_pion / jumlahPetak) * 100))) : 0;
        progressPosisiEl.textContent = me.posisi_pion;
        progressPercentEl.textContent = `${pct}%`;
        progressBarEl.style.width = `${pct}%`;
    }

    function updateTurnIndicator(session) {
        const currentPlayer = session.players.find((p) => p.id === session.current_turn_game_player_id);
        const isMyTurn = currentPlayer && !currentPlayer.is_robot && myGamePlayerId === currentPlayer.id;

        if (session.status !== 'playing') {
            turnIndicatorEl.textContent = '';
            turnSubtextEl.textContent = '';
            turnAvatarEl.textContent = '';
            rollButton.classList.add('hidden');
            setTileGlow(null);
            return;
        }

        turnAvatarEl.textContent = currentPlayer ? (currentPlayer.is_robot ? '\u{1F916}' : initials(currentPlayer.nama)) : '?';
        turnAvatarEl.className = `flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold text-white ${currentPlayer?.is_robot ? 'bg-slate-700' : 'bg-primary-500'}`;

        turnIndicatorEl.textContent = isMyTurn
            ? 'Giliran Anda'
            : `Menunggu giliran ${currentPlayer?.is_robot ? 'Robot' : (currentPlayer?.nama ?? '...')}`;

        turnSubtextEl.textContent = currentPlayer
            ? `Posisi: ${currentPlayer.posisi_pion} • Skor: ${currentPlayer.skor}`
            : '';

        setTileGlow(currentPlayer?.posisi_pion);

        const questionPending = !!session.active_question;
        rollButton.classList.toggle('hidden', !isMyTurn || questionPending);
        rollButton.classList.toggle('flex', isMyTurn && !questionPending);
    }

    function renderFinished(session) {
        if (session.status === 'finished') {
            const winner = session.players.find((p) => p.id === session.winner_game_player_id);
            const isMeWinner = winner && myGamePlayerId === winner.id;

            document.getElementById('finished-title').textContent = isMeWinner
                ? 'Selamat, Anda menang!'
                : 'Permainan selesai';
            document.getElementById('finished-subtitle').textContent = isMeWinner
                ? 'Anda berhasil mencapai garis Finish lebih dulu.'
                : `${winner?.is_robot ? 'Robot' : (winner?.nama ?? 'Lawan')} mencapai Finish lebih dulu.`;

            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'game-finished-modal' }));
            rollButton.classList.add('hidden');
        } else if (session.status === 'abandoned') {
            document.getElementById('finished-title').textContent = 'Permainan dihentikan';
            document.getElementById('finished-subtitle').textContent = 'Permainan ini telah dihentikan sebelum selesai.';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'game-finished-modal' }));
            rollButton.classList.add('hidden');
        }
    }

    function renderPaused(session) {
        clearInterval(pausedCountdownInterval);

        if (session.status !== 'paused' || !pausedBannerEl) {
            pausedBannerEl?.classList.add('hidden');
            pausedBannerEl?.classList.remove('flex');
            return;
        }

        pausedBannerEl.classList.remove('hidden');
        pausedBannerEl.classList.add('flex');

        const update = () => {
            if (!session.reconnect_deadline_at) {
                pausedTimerEl.textContent = '';
                return;
            }
            const remaining = Math.max(0, Math.floor((new Date(session.reconnect_deadline_at).getTime() - Date.now()) / 1000));
            pausedTimerEl.textContent = `${remaining}s`;
        };

        update();
        pausedCountdownInterval = setInterval(update, 1000);
    }

    // ---------------------------------------------------------------
    // Modal Soal
    // ---------------------------------------------------------------
    function startQuestionCountdown(expiresAtIso, soalId) {
        clearInterval(questionCountdownInterval);

        const update = () => {
            const remaining = Math.max(0, Math.floor((new Date(expiresAtIso).getTime() - Date.now()) / 1000));
            questionTimerEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(questionCountdownInterval);
                submitAnswer(soalId, null);
            }
        };

        update();
        questionCountdownInterval = setInterval(update, 1000);
    }

    function showQuestion(soal, expiresAtIso) {
        questionFeedbackEl.classList.add('hidden');
        questionTextEl.textContent = soal.pertanyaan;
        questionOptionsEl.innerHTML = '';

        Object.entries(soal.opsi_jawaban).forEach(([kunci, teks]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition-all duration-150 hover:-translate-y-0.5 hover:border-primary-400 hover:bg-primary-50';
            button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
            button.addEventListener('click', () => submitAnswer(soal.id, kunci));
            questionOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'question-modal' }));
        startQuestionCountdown(expiresAtIso, soal.id);
    }

    function hideQuestion() {
        clearInterval(questionCountdownInterval);
        window.dispatchEvent(new CustomEvent('close-modal'));
    }

    // ---------------------------------------------------------------
    // Realtime (Multiplayer)
    // ---------------------------------------------------------------
    const REALTIME_EVENT_LABELS = {
        'dice-rolled': 'Lawan melempar dadu.',
        'pawn-moved': 'Lawan menggerakkan pion.',
        'movement-blocked': 'Langkah lawan terlalu jauh, giliran dilewati.',
        'connector-applied': 'Lawan menginjak tangga/ular.',
        'question-presented': 'Lawan sedang menjawab soal.',
        'score-updated': 'Skor lawan berubah.',
        'game-finished': 'Permainan telah selesai.',
        'session-paused': 'Lawan terputus koneksi. Menunggu reconnect...',
        'session-resumed': 'Lawan telah kembali terhubung.',
    };

    function joinRealtimeChannel(session) {
        if (session.mode !== 'multiplayer' || !session.room_id || presenceChannel || !window.Echo) {
            return;
        }

        presenceChannel = window.Echo.join(`room.${session.room_id}`);

        Object.keys(REALTIME_EVENT_LABELS).forEach((eventName) => {
            presenceChannel.listen(`.${eventName}`, (payload) => {
                const actorId = payload.game_player_id ?? null;
                if (actorId !== null && actorId === myGamePlayerId) {
                    return;
                }

                showToast(REALTIME_EVENT_LABELS[eventName]);
                pushLog('info', REALTIME_EVENT_LABELS[eventName]);
                loadState();
            });
        });
    }

    function leaveRealtimeChannel(session) {
        if (presenceChannel && window.Echo && session.room_id) {
            window.Echo.leave(`room.${session.room_id}`);
            presenceChannel = null;
        }
    }

    function startHeartbeat(session) {
        if (session.mode !== 'multiplayer' || heartbeatIntervalId) {
            return;
        }

        heartbeatIntervalId = setInterval(() => {
            fetch(heartbeatUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            }).catch(() => {});
        }, 8000);
    }

    function stopRealtimeIfSessionOver(session) {
        if (session.status === 'finished' || session.status === 'abandoned') {
            clearInterval(heartbeatIntervalId);
            heartbeatIntervalId = null;
            leaveRealtimeChannel(session);
        }
    }

    // ---------------------------------------------------------------
    // Sinkronisasi pion untuk pemain LAIN (mis. lawan multiplayer) yang
    // posisinya berubah lewat sinyal realtime, bukan aksi kita sendiri —
    // kita tidak punya rincian nilai dadu lawan (payload sengaja minim),
    // jadi cukup animasikan selisih posisi lama -> baru secara wajar.
    // ---------------------------------------------------------------
    async function animateExternalDiff(session) {
        for (const p of session.players) {
            const el = getOrCreatePawnEl(p);
            const prev = knownPositions.get(p.id);

            if (prev === undefined) {
                placePawnAt(el, p.posisi_pion);
                continue;
            }

            if (prev === p.posisi_pion) {
                continue;
            }

            if (p.posisi_pion > prev && p.posisi_pion - prev <= 6) {
                // eslint-disable-next-line no-await-in-loop
                for (let step = prev + 1; step <= p.posisi_pion; step++) {
                    placePawnAt(el, step);
                    // eslint-disable-next-line no-await-in-loop
                    await delay(180);
                }
            } else {
                // Lawan/robot melompat konektor lewat sinyal realtime (kita tidak
                // tahu nilai dadu mereka) — cari konektor asli yang berakhir tepat
                // di posisi baru supaya jalurnya tetap mengikuti kurva sesungguhnya,
                // bukan cuma tebakan arah naik/turun.
                const naik = p.posisi_pion > prev;
                const konektor = Array.from(konektorByStart.values())
                    .find((k) => k.posisi_akhir === p.posisi_pion && (naik ? k.jenis === 'tangga' : k.jenis === 'ular'));

                await delay(100);

                if (konektor) {
                    if (naik) {
                        await window.BoardVisuals?.reactLadder?.(konektor.posisi_awal);
                        await animateAlongConnector(el, konektor.posisi_awal, p.posisi_pion, 'tangga', 900);
                        spawnParticles(el, 'sparkle', 10);
                        el.classList.add('pawn-climb');
                        setTimeout(() => el.classList.remove('pawn-climb'), 650);
                    } else {
                        await window.BoardVisuals?.reactSnake?.(konektor.posisi_awal);
                        boardEl.classList.add('board-shake');
                        await animateAlongConnector(el, konektor.posisi_awal, p.posisi_pion, 'ular', 900);
                        spawnParticles(el, 'dust', 8);
                        setTimeout(() => boardEl.classList.remove('board-shake'), 400);
                    }
                } else {
                    el.classList.add(naik ? 'pawn-climb' : 'pawn-slide');
                    await delay(450);
                    el.classList.remove('pawn-climb', 'pawn-slide');
                }

                placePawnAt(el, p.posisi_pion);
            }
        }
    }

    function syncKnownPositions(session) {
        session.players.forEach((p) => knownPositions.set(p.id, p.posisi_pion));
    }

    /**
     * pawnMode:
     *  - 'diff' (default): animasikan selisih posisi lama->baru untuk SEMUA
     *     pemain (dipakai saat refresh-recovery / sinyal realtime — kita
     *     tidak tahu siapa yang baru saja bergerak).
     *  - 'instant': langsung tempatkan semua pion tanpa animasi (dipakai
     *     saat pemuatan awal halaman).
     *  - 'skip': langsung tempatkan semua pion KECUALI yang ada di
     *     `skipPawnIds` — dipakai setelah aksi kita sendiri (roll/answer),
     *     karena pemain yang beraksi (dan robot jika ada) sudah/akan
     *     dianimasikan manual oleh pemanggil.
     */
    /**
     * Menampilkan hasil akhir (modal menang/kalah) & modal soal — dipisah dari
     * applySessionState supaya bisa DITUNDA sampai animasi pion selesai
     * (mis. giliran robot yang baru saja menyentuh Finish tidak boleh
     * memunculkan modal "selesai" sebelum pion robot terlihat berjalan).
     */
    function finalizeOutcome(session, newlyUnlockedAchievements = []) {
        renderFinished(session);

        if (session.active_question) {
            showQuestion(session.active_question, session.active_question_expires_at);
        } else {
            hideQuestion();
        }

        if (newlyUnlockedAchievements.length > 0) {
            // Jeda singkat supaya modal Achievement muncul SETELAH modal hasil
            // akhir (game-finished-modal) sempat terlihat, bukan tabrakan.
            setTimeout(() => queueAchievementUnlocks(newlyUnlockedAchievements), session.status === 'finished' ? 700 : 0);
        }
    }

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
        window.dispatchEvent(new CustomEvent('close-modal'));
        if (achievementQueue.length > 0) {
            setTimeout(() => showNextAchievementUnlock(), 250);
        }
    });

    function applySessionState(session, { pawnMode = 'diff', skipPawnIds = new Set(), deferOutcome = false } = {}) {
        latestSession = session;

        if (pawnMode === 'instant') {
            session.players.forEach((p) => placePawnAt(getOrCreatePawnEl(p), p.posisi_pion));
            syncKnownPositions(session);
        } else if (pawnMode === 'skip') {
            session.players.forEach((p) => {
                if (!skipPawnIds.has(p.id)) {
                    placePawnAt(getOrCreatePawnEl(p), p.posisi_pion);
                }
            });
            syncKnownPositions(session);
        } else {
            animationChain = animationChain.then(() => animateExternalDiff(session)).then(() => syncKnownPositions(session));
        }

        renderPlayerPanels(session);
        renderMyProgress(session);
        updateTurnIndicator(session);
        renderPaused(session);

        if (!deferOutcome) {
            finalizeOutcome(session);
        }

        joinRealtimeChannel(session);
        startHeartbeat(session);
        stopRealtimeIfSessionOver(session);
    }

    // ---------------------------------------------------------------
    // HTTP
    // ---------------------------------------------------------------
    async function postJson(url, body = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Terjadi kesalahan.');
        }

        return data;
    }

    async function rollDice() {
        rollButton.disabled = true;

        const me = latestSession?.players.find((p) => p.id === myGamePlayerId);
        const fromPosisi = me ? me.posisi_pion : 1;
        // Posisi robot SEBELUM giliran ini harus diambil sebelum applySessionState
        // menimpa knownPositions dengan posisi akhirnya (lihat playRobotTurns).
        const robotBefore = latestSession?.players.find((p) => p.is_robot);
        const robotFromPosisi = robotBefore ? (knownPositions.get(robotBefore.id) ?? robotBefore.posisi_pion) : 0;

        try {
            const result = await postJson(rollUrl);

            await animateDiceRoll(result.nilai_dadu ?? 1);

            const actingPlayer = result.session.players.find((p) => p.id === myGamePlayerId) ?? me;
            if (actingPlayer) {
                logTurnResult(actingPlayer, result);
                await animatePlayerTurn(actingPlayer, fromPosisi, result);
                knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
            }

            if (result.type === 'answered') {
                logAnswerResult(actingPlayer, result.benar);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const skipIds = new Set([myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, robotFromPosisi);
            finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
        } catch (error) {
            showToast(error.message);
        } finally {
            rollButton.disabled = false;
        }
    }

    async function playRobotTurns(robotTurns, session, startFromPosisi) {
        if (!robotTurns || robotTurns.length === 0) return;

        const robot = session.players.find((p) => p.is_robot);
        if (!robot) return;

        let fromPosisi = startFromPosisi ?? (knownPositions.get(robot.id) ?? robot.posisi_pion);

        for (const turn of robotTurns) {
            await delay(500);
            await animateDiceRoll(turn.nilai_dadu ?? 1);
            logTurnResult({ ...robot }, turn);
            if (turn.type === 'answered' || turn.benar !== undefined) {
                logAnswerResult(robot, turn.benar);
            }

            // Turn terakhir membawa posisi akhir robot yang sesungguhnya (session
            // sudah final di titik ini); turn di tengah diasumsikan tidak
            // memindahkan posisi lebih lanjut (server memainkan robot secara
            // berurutan dalam satu putaran, lihat GameSessionService).
            const isLast = turn === robotTurns[robotTurns.length - 1];
            const toPosisi = isLast ? robot.posisi_pion : fromPosisi;

            // eslint-disable-next-line no-await-in-loop
            await animatePlayerTurn(robot, fromPosisi, { ...turn, posisi_pion: toPosisi });
            fromPosisi = toPosisi;
        }

        knownPositions.set(robot.id, robot.posisi_pion);
        setTileGlow(latestSession?.status === 'playing' ? latestSession.players.find((p) => p.id === latestSession.current_turn_game_player_id)?.posisi_pion : null);
    }

    async function submitAnswer(soalId, jawaban) {
        const robotBefore = latestSession?.players.find((p) => p.is_robot);
        const robotFromPosisi = robotBefore ? (knownPositions.get(robotBefore.id) ?? robotBefore.posisi_pion) : 0;
        const meBefore = latestSession?.players.find((p) => p.id === myGamePlayerId);
        const myFromPosisi = meBefore ? meBefore.posisi_pion : 1;

        try {
            const result = await postJson(answerUrl, { soal_id: soalId, jawaban });

            questionFeedbackEl.textContent = result.benar
                ? 'Jawaban benar!'
                : `Jawaban salah. ${result.pembahasan ?? ''}`;
            questionFeedbackEl.classList.remove('hidden');

            const actingPlayer = result.session.players.find((p) => p.id === myGamePlayerId);
            if (actingPlayer) {
                logAnswerResult(actingPlayer, result.benar);
            }

            await delay(1800);
            hideQuestion();

            // Jawaban bisa memindahkan pion penjawab (mis. efek soal khusus) —
            // animasikan selisih posisi sebelum -> sesudah menjawab, bukan
            // langsung memindahkan.
            if (actingPlayer && actingPlayer.posisi_pion !== myFromPosisi) {
                await animatePlayerTurn(actingPlayer, myFromPosisi, { type: 'answered', nilai_dadu: actingPlayer.posisi_pion - myFromPosisi });
            }
            if (actingPlayer) {
                knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const skipIds = new Set([myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, robotFromPosisi);
            finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
        } catch (error) {
            showToast(error.message);
            hideQuestion();
        }
    }

    async function loadState() {
        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const session = await response.json();
        applySessionState(session);
    }

    async function loadInitialState() {
        initDiceSize();

        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const session = await response.json();

        const me = session.players.find((p) => p.user_id === currentUserId);
        myGamePlayerId = me ? me.id : null;

        applySessionState(session, { pawnMode: 'instant' });
    }

    rollButton?.addEventListener('click', rollDice);
    window.addEventListener('resize', initDiceSize);

    document.getElementById('leave-game-button')?.addEventListener('click', async () => {
        const isMultiplayer = latestSession?.mode === 'multiplayer';
        const confirmMessage = isMultiplayer
            ? 'Yakin ingin keluar? Anda akan dinyatakan kalah WO dan lawan otomatis menang.'
            : 'Yakin ingin keluar dari permainan ini? Permainan akan dihentikan.';

        if (!confirm(confirmMessage)) {
            return;
        }

        leaveConfirmed = true;

        try {
            await postJson(leaveUrl);
        } finally {
            window.location.href = '/dashboard';
        }
    });

    window.addEventListener('beforeunload', (event) => {
        if (!leaveConfirmed && document.getElementById('game-in-progress')) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    loadInitialState();
});
