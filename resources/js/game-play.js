import { CoordinateHelper } from './board/CoordinateHelper.js';
import * as Colyseus from 'colyseus.js';

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


    const questionTextEl = document.getElementById('question-text');
    const questionOptionsEl = document.getElementById('question-options');
    const questionTimerEl = document.getElementById('question-timer');
    const questionFeedbackEl = document.getElementById('question-feedback');

    const duelStatusTextEl = document.getElementById('duel-status-text');
    const duelQuestionNumberEl = document.getElementById('duel-question-number');
    const duelTextEl = document.getElementById('duel-text');
    const duelOptionsEl = document.getElementById('duel-options');
    const duelTimerEl = document.getElementById('duel-timer');
    const duelFeedbackEl = document.getElementById('duel-feedback');

    const playerCardTemplate = document.getElementById('player-card-template');
    const robotCardTemplate = document.getElementById('robot-card-template');

    let myGamePlayerId = null;
    let latestSession = null;
    let knownPositions = new Map(); // game_player_id -> posisi_pion (untuk animasi diff)
    let questionCountdownInterval = null;
    let duelCountdownInterval = null;
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
                const itemName = result.item_name || 'Power-Up';
                pushLog('mystery', `${nama} mendapat ${itemName} dari Tile Misteri`);
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
    // ---------------------------------------------------------------
    // Audio Manager (BGM & SFX)
    // ---------------------------------------------------------------
    const soundCache = {};
    let bgmAudio = null;
    let isBgmPlaying = false;
    let isMuted = false;

    function getAudio(filename) {
        if (!soundCache[filename]) {
            const audio = new Audio(`/sounds/${filename}`);
            audio.preload = 'auto';
            soundCache[filename] = audio;
        }
        return soundCache[filename];
    }

    // Preload important SFX so they play immediately without network delay
    function preloadSFX() {
        const sfx = ['correct_answer', 'false_answer', 'climb_ladder', 'snake_eat', 'pion_walk', 'roll_dice', 'win_match'];
        sfx.forEach(name => getAudio(`${name}.mp3`));
    }
    preloadSFX();

    function playSound(name) {
        if (isMuted) return;
        try {
            const audio = getAudio(`${name}.mp3`);
            audio.currentTime = 0;
            audio.play().catch(() => {});
        } catch (e) {}
    }

    function playRollSound() {
        playSound('roll_dice');
    }

    function playHitSound(vol = 0.2) {
        playSound('roll_dice');
    }

    function startBGM() {
        if (isBgmPlaying || isMuted) return;
        try {
            if (!bgmAudio) {
                bgmAudio = getAudio('bgm.mp3');
                bgmAudio.loop = true;
                bgmAudio.volume = 0.25;
            }
            bgmAudio.play().then(() => {
                isBgmPlaying = true;
            }).catch(() => {});
        } catch (e) {}
    }

    function stopBGM() {
        if (bgmAudio) {
            bgmAudio.pause();
            isBgmPlaying = false;
        }
    }

    function initAudioAutoStart() {
        const handler = () => {
            startBGM();
        };
        document.addEventListener('click', handler, { once: true });
        document.addEventListener('keydown', handler, { once: true });
        document.addEventListener('touchstart', handler, { once: true });
    }

    initAudioAutoStart();

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
        const diceCubes = document.querySelectorAll('.dice-3d');
        diceCubes.forEach((cube) => {
            if (cube && cube.parentElement) {
                const half = cube.parentElement.offsetWidth / 2;
                cube.style.setProperty('--dice-half', `${half}px`);
            }
        });
    }

    async function animateDiceRoll(finalValue, doubleDiceActive = false) {
        if (!diceCube) return;
        
        const diceWrap = document.getElementById('dice-3d-wrap');
        const board = document.getElementById('game-board');
        if (!diceWrap || !board) return;

        diceGlowWrap?.classList.add('ring-4', 'ring-accent-300', 'animate-pulse');
        
        // Sembunyikan dadu asli selama animasi (seolah terlempar)
        diceCube.style.opacity = '0';

        // Buat clone dadu untuk dianimasikan terbang
        const clone = diceWrap.cloneNode(true);
        clone.id = 'dice-clone';
        clone.classList.add('dice-clone-wrap');
        
        // Hapus disabled overlay di clone jika ada
        const overlay = clone.querySelector('#dice-3d-disabled-overlay');
        if (overlay) overlay.remove();

        const innerCube = clone.querySelector('.dice-3d');
        innerCube.style.transition = 'none';
        innerCube.style.opacity = '1'; // Pastikan clone tidak tersembunyi

        // Tambah dynamic shadow
        const shadow = document.createElement('div');
        shadow.className = 'dice-dynamic-shadow';
        clone.appendChild(shadow);
        
        document.body.appendChild(clone);
        
        const startRect = diceWrap.getBoundingClientRect();
        const boardRect = board.getBoundingClientRect();
        
        const startX = startRect.left + startRect.width / 2 - 40;
        const startY = startRect.top + startRect.height / 2 - 40;
        const endX = boardRect.left + boardRect.width / 2 - 40;
        const endY = boardRect.top + boardRect.height / 2 - 40;
        
        const dx = endX - startX;
        const dy = endY - startY;

        const duration = 1400; // 1.4 detik total sesuai permintaan
        const startTime = performance.now();
        playRollSound();

        const target = FACE_ROTATIONS[finalValue] ?? FACE_ROTATIONS[1];
        const spins = 2; // putaran ekstra penuh supaya terasa "dilempar"
        
        const targetX = diceRotation.x + spins * 360 + (target.x - (diceRotation.x % 360));
        const targetY = diceRotation.y + spins * 360 + (target.y - (diceRotation.y % 360));
        const targetZ = Math.floor(2 + Math.random() * 2) * 360;
        
        const bounces = [
            { t: 0.60, height: -180 }, // Terbang awal (puncak)
            { t: 0.80, height: -50 },  // Pantulan 1
            { t: 0.92, height: -15 },  // Pantulan 2
            { t: 1.00, height: 0 }     // Berhenti
        ];

        return new Promise((resolve) => {
            let lastBounceIdx = 0;
            
            function frame(now) {
                const elapsed = now - startTime;
                let p = Math.min(elapsed / duration, 1);
                const easeOutQuart = 1 - Math.pow(1 - p, 4);
                let currentX = startX + dx * easeOutQuart;
                let currentY = startY + dy * easeOutQuart;
                
                // Skala dasar dadu mengecil dari 100% ke 50% selama terbang
                // memberi efek 3D "menjauh" masuk ke dalam papan
                const baseScale = 1.0 - 0.5 * easeOutQuart;
                
                let yOffset = 0;
                let scaleY = baseScale;
                let scaleX = baseScale;

                let segStartT = 0;
                let segEndT = bounces[0].t;
                let segHeight = bounces[0].height;
                let bIdx = 0;
                
                for (let i = 0; i < bounces.length; i++) {
                    if (p <= bounces[i].t) {
                        segEndT = bounces[i].t;
                        segHeight = bounces[i].height;
                        bIdx = i;
                        break;
                    }
                    segStartT = bounces[i].t;
                }
                
                const segP = (p - segStartT) / (segEndT - segStartT);
                // Rumus Parabola 4 * h * t * (1 - t)
                yOffset = 4 * segHeight * segP * (1 - segP);
                
                // Sound dan particle debu saat menyentuh papan
                if (bIdx > lastBounceIdx) {
                    playHitSound(0.4 / bIdx); // Volume berkurang ditiap pantulan
                    spawnParticles(clone, 'dust', Math.max(3, 8 - bIdx * 2));
                    lastBounceIdx = bIdx;
                }
                
                // Efek Squash & Stretch (kamera feel)
                if (segP > 0.85 && bIdx > 0) {
                    const squashAmt = (segP - 0.85) * (1 / 0.15) * (1 / bIdx);
                    scaleY = baseScale * (1 - 0.25 * squashAmt);
                    scaleX = baseScale * (1 + 0.15 * squashAmt);
                } else if (segP < 0.15 && bIdx > 0) {
                    const stretchAmt = (0.15 - segP) * (1 / 0.15) * (1 / bIdx);
                    scaleY = baseScale * (1 + 0.15 * stretchAmt);
                    scaleX = baseScale * (1 - 0.1 * stretchAmt);
                }
                
                currentY += yOffset;
                
                // Dinamika Bayangan
                const shadowScale = 1 - (yOffset / -250);
                const shadowBlur = 4 + (yOffset / -15);
                shadow.style.transform = `scale(${Math.max(0.3, shadowScale)})`;
                shadow.style.filter = `blur(${Math.max(2, shadowBlur)}px)`;
                shadow.style.opacity = Math.max(0.1, shadowScale);
                
                clone.style.left = `${currentX}px`;
                clone.style.top = `${currentY}px`;
                clone.style.transform = `scale(${scaleX}, ${scaleY})`;
                
                // Rotasi melambat (ease-out cubic)
                const rotP = 1 - Math.pow(1 - p, 3);
                
                // Interpolasi mulus dari rotasi sebelumnya ke target akhir
                const rotCurrentX = diceRotation.x + (targetX - diceRotation.x) * rotP;
                const rotCurrentY = diceRotation.y + (targetY - diceRotation.y) * rotP;
                
                // Putaran Z liar di udara, tapi memudar menjadi 0 saat mendarat agar tidak merusak orientasi wajah dadu
                const currentZ = targetZ * Math.max(0, 1 - Math.pow(p, 1.5));

                innerCube.style.transform = `rotateX(${rotCurrentX}deg) rotateY(${rotCurrentY}deg) rotateZ(${currentZ}deg)`;
                
                if (p < 1) {
                    requestAnimationFrame(frame);
                } else {
                    // Beri efek glow pada wrapper clone agar tidak merusak preserve-3d innerCube
                    clone.classList.add('tile-glow-bonus');
                    
                    diceRotation.x = targetX;
                    diceRotation.y = targetY;
                    
                    document.querySelectorAll('.dice-3d').forEach((cube) => {
                        cube.style.transition = 'none';
                        cube.style.transform = `rotateX(${diceRotation.x}deg) rotateY(${diceRotation.y}deg)`;
                    });

                    // Efek ×2 untuk double dice
                    if (doubleDiceActive) {
                        const multiplierBadge = document.createElement('div');
                        multiplierBadge.textContent = '×2';
                        multiplierBadge.className = 'absolute font-black text-4xl text-primary-500 drop-shadow-md z-50 animate-bounce';
                        multiplierBadge.style.left = `${endX + 20}px`;
                        multiplierBadge.style.top = `${endY - 40}px`;
                        multiplierBadge.style.textShadow = '0 0 10px white, 0 0 20px white';
                        
                        document.body.appendChild(multiplierBadge);
                        
                        // Hapus badge setelah selesai tampil
                        setTimeout(() => multiplierBadge.remove(), 2000);
                    }
                    
                    // Tunggu 2000ms supaya hasil terlihat lebih lama sebelum memindahkan pion
                    setTimeout(() => {
                        clone.remove();
                        diceCube.style.opacity = '1';
                        diceGlowWrap?.classList.remove('ring-4', 'ring-accent-300', 'animate-pulse');
                        resolve();
                    }, 2000);
                }
            }
            requestAnimationFrame(frame);
        });
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

        const { bg } = pawnColorStyle(player);
        el = document.createElement('div');
        el.dataset.pawnId = String(player.id);
        el.className = 'game-pawn';
        // Pion 3D lebih tinggi dan ramping
        el.style.width = `${(100 / jumlahKolom) * 0.45}%`;
        el.style.height = `${(100 / totalRows) * 0.8}%`;
        
        el.innerHTML = `
            <div class="active-indicator hidden absolute -top-8 left-1/2 -translate-x-1/2 animate-bounce z-10">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#fbbf24" stroke="#b45309" stroke-width="2" class="drop-shadow-md" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22L2 6h20L12 22z" stroke-linejoin="round" />
                </svg>
            </div>
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

    /**
     * Index pion di antara pion LAIN yang kebetulan berdiri di kotak yang
     * sama (`posisi`) — dipakai `placePawnAt(..., stackIndex)` supaya pion
     * yang menumpuk digeser sedikit (nudge), bukan tumpang tindih sempurna.
     * Makin sering terjadi dengan game 3-6 pemain dibanding 2 pemain, jadi
     * digeneralisasi di sini alih-alih dihitung manual tiap call site.
     * Urutan diambil dari `turn_order` (stabil, sama di semua klien) supaya
     * offset-nya konsisten walau urutan array `players` di payload beda-beda.
     */
    function stackIndexAt(posisi, playerId, players = latestSession?.players ?? []) {
        const sameTile = players
            .filter((p) => p.posisi_pion === posisi)
            .sort((a, b) => (a.turn_order ?? 0) - (b.turn_order ?? 0));
        const idx = sameTile.findIndex((p) => p.id === playerId);
        return idx === -1 ? 0 : idx;
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

        const landingPosisi = Math.min(fromPosisi + (nilaiDadu > 0 ? nilaiDadu : 0), jumlahPetak);

        if (nilaiDadu < 0) {
            const finalPosisiTarget = Math.max(fromPosisi + nilaiDadu, 1);
            for (let step = fromPosisi - 1; step >= finalPosisiTarget; step--) {
                const stackIndex = step === finalPosisiTarget ? stackIndexAt(step, player.id, result.session?.players) : 0;
                
                el.classList.remove('pawn-hop');
                void el.offsetWidth;
                el.classList.add('pawn-hop');
                
                placePawnAt(el, step, stackIndex);
                playSound('pion_walk');
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
            playSound('pion_walk');
            // eslint-disable-next-line no-await-in-loop
            await delay(400);
        }
        el.classList.remove('pawn-hop');

        // Tampilkan Modal Gacha JIKA INI MYSTERY TILE
        if (result.type === 'mystery' && result.item_id && player.id === myGamePlayerId) {
            await showGachaModal(result);
        }

        if (finalPosisi !== landingPosisi) {
            const isBounce = landingPosisi === jumlahPetak && !konektorByStart.get(landingPosisi) && finalPosisi < landingPosisi;

            if (isBounce) {
                await delay(120);
                for (let step = landingPosisi - 1; step >= finalPosisi; step--) {
                    const stackIndex = step === finalPosisi ? stackIndexAt(step, player.id, result.session?.players) : 0;
                    el.classList.remove('pawn-hop');
                    void el.offsetWidth;
                    el.classList.add('pawn-hop');
                    
                    placePawnAt(el, step, stackIndex);
                    playSound('pion_walk');
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
                const konektor = konektorByStart.get(landingPosisi);
                const jenis = konektor?.jenis ?? (finalPosisi > landingPosisi ? 'tangga' : 'ular');
                const isNaik = jenis === 'tangga';

                await delay(120);

                if (isNaik) {
                    playSound('climb_ladder');
                    // Efek visual tangga (glow+sparkle) — best-effort, lihat catatan di atas file.
                    await window.BoardVisuals?.reactLadder?.(landingPosisi);
                    await animateAlongConnector(el, landingPosisi, finalPosisi, 'tangga', 900);
                    spawnParticles(el, 'sparkle', 10);
                    el.classList.add('pawn-climb');
                    setTimeout(() => el.classList.remove('pawn-climb'), 650);
                } else {
                    playSound('snake_eat');
                    // Efek "ular menggigit" (glow+lidah+kepala bergerak) SEBELUM pion
                    // meluncur turun — best-effort, lihat catatan di atas file.
                    await window.BoardVisuals?.reactSnake?.(landingPosisi);
                    boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, landingPosisi, finalPosisi, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => boardEl.classList.remove('board-shake'), 400);
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

    function renderInventoryUI(session) {
        const inventoryContainerEl = document.getElementById('player-inventory-container');
        const inventoryListEl = document.getElementById('player-inventory-list');
        const emptyTextEl = document.getElementById('empty-inventory-text');

        if (!inventoryContainerEl || !inventoryListEl) return;

        const me = session.players.find((p) => p.id === myGamePlayerId);
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
            if (session.current_turn_game_player_id !== myGamePlayerId || session.status !== 'playing') {
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
            const powerupUrl = rollUrl.replace('/roll', '/powerup');
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
        const isMyTurn = currentPlayer && !currentPlayer.is_robot && myGamePlayerId === currentPlayer.id;

        const turnActiveDotEl = document.getElementById('turn-active-dot');
        const turnAvatarRingEl = document.getElementById('turn-avatar-ring');
        const diceGlowWrapEl = document.getElementById('dice-glow-wrap');

        if (session.status !== 'playing') {
            turnIndicatorEl.textContent = '';
            turnSubtextEl.textContent = '';
            turnAvatarEl.textContent = '';
            rollButton.classList.add('hidden');
            if (turnActiveDotEl) turnActiveDotEl.classList.add('hidden');
            if (turnAvatarRingEl) {
                turnAvatarRingEl.classList.remove('animate-ping-slow', 'opacity-100');
                turnAvatarRingEl.classList.add('opacity-0');
            }
            if (diceGlowWrapEl) diceGlowWrapEl.classList.remove('animate-float', 'drop-shadow-lg');
            setTileGlow(null);
            return;
        }

        turnAvatarEl.textContent = currentPlayer ? (currentPlayer.is_robot ? '\u{1F916}' : initials(currentPlayer.nama)) : '?';
        turnAvatarEl.className = `relative z-10 flex h-12 w-12 items-center justify-center rounded-full text-sm font-black text-white shadow-md ${currentPlayer?.is_robot ? 'bg-gradient-to-br from-slate-600 to-slate-800' : 'bg-gradient-to-br from-primary-400 to-primary-600'}`;

        if (isMyTurn) {
            turnIndicatorEl.innerHTML = `Giliran Anda`; // The dot is now handled separately below
            if (turnActiveDotEl) {
                turnActiveDotEl.classList.remove('hidden');
                turnActiveDotEl.classList.add('flex');
            }
            if (turnAvatarRingEl) {
                turnAvatarRingEl.classList.remove('opacity-0');
                turnAvatarRingEl.classList.add('animate-ping-slow', 'opacity-100');
            }
        } else {
            turnIndicatorEl.textContent = `Menunggu giliran ${currentPlayer?.is_robot ? 'Robot' : (currentPlayer?.nama ?? '...')}`;
            if (turnActiveDotEl) turnActiveDotEl.classList.add('hidden');
            if (turnAvatarRingEl) {
                turnAvatarRingEl.classList.remove('animate-ping-slow', 'opacity-100');
                turnAvatarRingEl.classList.add('opacity-0');
            }
        }

        turnSubtextEl.textContent = currentPlayer
            ? `Posisi: ${currentPlayer.posisi_pion} • Skor: ${currentPlayer.skor}`
            : '';

        setTileGlow(currentPlayer?.posisi_pion);

        // Update Active Indicator on all pawns
        session.players.forEach(p => {
            const pawnEl = document.querySelector(`.game-pawn[data-pawn-id="${p.id}"]`);
            if (pawnEl) {
                const indicator = pawnEl.querySelector('.active-indicator');
                if (indicator) {
                    if (p.id === session.current_turn_game_player_id && session.status === 'playing') {
                        indicator.classList.remove('hidden');
                    } else {
                        indicator.classList.add('hidden');
                    }
                }
            }
        });

        const questionPending = !!session.active_question;
        const canRoll = isMyTurn && !questionPending;
        rollButton.classList.toggle('hidden', !canRoll);
        rollButton.classList.toggle('flex', canRoll);

        const disabledOverlay = document.getElementById('dice-3d-disabled-overlay');
        if (disabledOverlay) {
            disabledOverlay.classList.toggle('hidden', canRoll);
            disabledOverlay.classList.toggle('flex', !canRoll);
        }
    }

    let isWinSoundPlayed = false;

    function renderFinished(session) {
        if (session.status === 'finished') {
            if (!isWinSoundPlayed) {
                playSound('win_match');
                isWinSoundPlayed = true;
            }
            const winner = session.players.find((p) => p.id === session.winner_game_player_id);
            const isMeWinner = winner && myGamePlayerId === winner.id;

            document.getElementById('finished-title').textContent = isMeWinner
                ? 'Selamat, Anda menang!'
                : 'Permainan selesai';
            document.getElementById('finished-subtitle').textContent = isMeWinner
                ? 'Anda berhasil mencapai garis Finish lebih dulu.'
                : `${winner?.is_robot ? 'Robot' : (winner?.nama ?? 'Pemain lain')} mencapai Finish lebih dulu.`;

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
    function startQuestionCountdown(expiresAtIso, soalId, isMyTurn) {
        clearInterval(questionCountdownInterval);

        const update = () => {
            const remaining = Math.max(0, Math.floor((new Date(expiresAtIso).getTime() - Date.now()) / 1000));
            questionTimerEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(questionCountdownInterval);
                if (isMyTurn) {
                    submitAnswer(soalId, null);
                }
            }
        };

        update();
        questionCountdownInterval = setInterval(update, 1000);
    }

    function showQuestion(soal, expiresAtIso, isMyTurn = true, activePlayerName = 'Pemain') {
        questionFeedbackEl.classList.add('hidden');
        questionTextEl.textContent = soal.pertanyaan;
        questionOptionsEl.innerHTML = '';

        const titleEl = document.getElementById('question-title-text');
        if (titleEl) {
            titleEl.textContent = isMyTurn ? 'Soal' : `${activePlayerName} sedang menjawab soal...`;
        }

        Object.entries(soal.opsi_jawaban).forEach(([kunci, teks]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition-all duration-150';
            button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
            button.dataset.kunci = kunci;
            
            if (isMyTurn) {
                button.classList.add('hover:-translate-y-0.5', 'hover:border-primary-400', 'hover:bg-primary-50');
                button.addEventListener('click', () => submitAnswer(soal.id, kunci, button));
            } else {
                button.disabled = true;
                button.classList.add('opacity-60', 'cursor-not-allowed');
            }
            
            questionOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'question-modal' }));
        startQuestionCountdown(expiresAtIso, soal.id, isMyTurn);
    }

    function hideQuestion() {
        clearInterval(questionCountdownInterval);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'question-modal' }));
    }

    // ---------------------------------------------------------------
    // Modal Duel
    // ---------------------------------------------------------------
    function startDuelCountdown(duration, soalId, startedAt) {
        clearInterval(duelCountdownInterval);

        const update = () => {
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, Math.floor((duration - elapsed) / 1000));
            duelTimerEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(duelCountdownInterval);
                submitDuelAnswer(soalId, null, duration);
            }
        };

        update();
        duelCountdownInterval = setInterval(update, 1000);
    }

    let duelStartTime = null;

    let duelOpponentSimTimeout = null;
    let duelBotCurrentDuelId = null;
    let duelBotSimulatedIndex = 0;
    let duelBotIsThinking = false;

    function updateDuelOpponentView(duel) {
        const opponentId = duel.challenger_id === myGamePlayerId ? duel.opponent_id : duel.challenger_id;
        const opponent = latestSession?.players.find(p => p.id === opponentId);
        
        const nameEl = document.getElementById('duel-opponent-name');
        const statusEl = document.getElementById('duel-opponent-status');
        const textEl = document.getElementById('duel-opponent-text');
        const optionsEl = document.getElementById('duel-opponent-options');
        const feedbackEl = document.getElementById('duel-opponent-feedback');
        
        if (!opponent) return;
        
        nameEl.textContent = opponent.is_robot ? 'Robot' : opponent.nama;
        
        if (opponent.is_robot) {
            // Check if this is a new duel
            if (duelBotCurrentDuelId !== duel.id) {
                duelBotCurrentDuelId = duel.id;
                duelBotSimulatedIndex = 0;
                duelBotIsThinking = false;
                if (duelOpponentSimTimeout) clearTimeout(duelOpponentSimTimeout);
            }
            
            // If currently running an animation step, do not interrupt
            if (duelBotIsThinking) return;

            const opponentAnswers = duel.answers.filter(a => a.game_player_id === opponentId);
            
            if (duelBotSimulatedIndex >= 3 || duelBotSimulatedIndex >= opponentAnswers.length) {
                statusEl.textContent = 'SELESAI';
                textEl.textContent = 'Lawan telah menyelesaikan semua pertanyaan.';
                textEl.classList.add('text-center', 'italic');
                optionsEl.innerHTML = '';
                feedbackEl.classList.add('hidden');
                return;
            }

            // Start thinking for current simulated index
            duelBotIsThinking = true;
            statusEl.textContent = `Menjawab Soal ${duelBotSimulatedIndex + 1}`;
            
            const currentQuestion = duel.questions[duelBotSimulatedIndex];
            const botAnswer = opponentAnswers.find(a => a.soal_id === currentQuestion.soal.id);
            
            if (botAnswer) {
                // Tampilkan soal dan opsi
                textEl.textContent = currentQuestion.soal.pertanyaan;
                textEl.classList.remove('text-center', 'italic');
                optionsEl.innerHTML = '';
                feedbackEl.classList.add('hidden');

                // Render opsi
                Object.entries(currentQuestion.soal.opsi_jawaban).forEach(([kunci, teks]) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.disabled = true;
                    button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 cursor-default transition-all';
                    button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
                    button.dataset.kunci = kunci;
                    optionsEl.appendChild(button);
                });

                // Tunggu 2 detik, baru tampilkan hasil pilihan bot
                duelOpponentSimTimeout = setTimeout(() => {
                    // Cari tombol yang dipilih dan yang benar
                    // Kita tidak punya akses ke 'kunciJawaban' dari backend karena disembunyikan untuk mencegah kecurangan.
                    // Namun kita tahu jawaban bot (botAnswer.jawaban) dan apakah itu benar (botAnswer.is_correct).
                    
                    const selectedBtn = Array.from(optionsEl.children).find(b => b.dataset.kunci?.toUpperCase() === botAnswer.jawaban?.toUpperCase());
                    
                    if (selectedBtn) {
                        if (botAnswer.is_correct) {
                            selectedBtn.classList.remove('border-slate-200', 'text-slate-700');
                            selectedBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                        } else {
                            selectedBtn.classList.remove('border-slate-200', 'text-slate-700');
                            selectedBtn.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                        }
                    }

                    feedbackEl.textContent = botAnswer.is_correct ? '✅ Bot menjawab benar!' : '❌ Bot menjawab salah!';
                    feedbackEl.classList.remove('hidden');

                    duelBotSimulatedIndex++; // Advance to next question locally

                    // Jeda sebentar sebelum pindah ke soal bot berikutnya
                    duelOpponentSimTimeout = setTimeout(() => {
                        duelBotIsThinking = false;
                        updateDuelOpponentView(duel); // Recurse to render next
                    }, 1500);
                }, 2000); // Tepat 2 detik sesuai permintaan pengguna
            } else {
                duelBotIsThinking = false;
            }
        } else {
            // Real human
            if (duelOpponentSimTimeout) {
                clearTimeout(duelOpponentSimTimeout);
                duelOpponentSimTimeout = null;
            }
            const opponentAnswers = duel.answers.filter(a => a.game_player_id === opponentId);
            const answeredCount = opponentAnswers.length;
            
            if (answeredCount >= 3) {
                statusEl.textContent = 'SELESAI';
                textEl.textContent = 'Lawan telah menyelesaikan semua pertanyaan.';
                textEl.classList.add('text-center', 'italic');
            } else {
                statusEl.textContent = `Menjawab Soal ${answeredCount + 1}`;
                textEl.textContent = 'Menunggu lawan menjawab...';
                textEl.classList.add('text-center', 'italic');
            }
            optionsEl.innerHTML = '';
            feedbackEl.classList.add('hidden');
        }
    }

    function showDuel(duel) {
        if (!myGamePlayerId || (duel.challenger_id !== myGamePlayerId && duel.opponent_id !== myGamePlayerId)) {
            // Not part of duel
            return;
        }

        updateDuelOpponentView(duel);

        // Find the first question we haven't answered yet
        const myAnswers = duel.answers.filter(a => a.game_player_id === myGamePlayerId);
        const answeredSoalIds = new Set(myAnswers.map(a => a.soal_id));
        
        const nextQuestion = duel.questions.find(q => !answeredSoalIds.has(q.soal.id));

        if (!nextQuestion) {
            // Waiting for opponent
            const duelStatusTextEl = document.getElementById('duel-status-text');
            const duelTextEl = document.getElementById('duel-text');
            const duelOptionsEl = document.getElementById('duel-options');
            const duelTimerEl = document.getElementById('duel-timer');
            
            if (duelOptionsEl && duelOptionsEl.dataset.activeSoalId === 'waiting') {
                return;
            }

            if (duelStatusTextEl) duelStatusTextEl.textContent = 'Menunggu lawan selesai...';
            if (duelTextEl) duelTextEl.textContent = 'Anda telah menjawab semua soal. Harap tunggu.';
            if (duelOptionsEl) {
                duelOptionsEl.innerHTML = '';
                duelOptionsEl.dataset.activeSoalId = 'waiting';
            }
            if (duelTimerEl) duelTimerEl.textContent = '';
            clearInterval(duelCountdownInterval);
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'duel-modal' }));
            return;
        }

        const soal = nextQuestion.soal;
        const duelStatusTextEl = document.getElementById('duel-status-text');
        const duelFeedbackEl = document.getElementById('duel-feedback');
        const duelTextEl = document.getElementById('duel-text');
        const duelOptionsEl = document.getElementById('duel-options');
        
        if (duelOptionsEl && duelOptionsEl.dataset.activeSoalId == soal.id) {
            // Do not reset the current question if it's already active
            return;
        }
        
        if (duelStatusTextEl) duelStatusTextEl.textContent = `Pertanyaan ${nextQuestion.order} dari 3`;
        if (duelFeedbackEl) duelFeedbackEl.classList.add('hidden');
        if (duelTextEl) duelTextEl.textContent = soal.pertanyaan;
        if (duelOptionsEl) {
            duelOptionsEl.innerHTML = '';
            duelOptionsEl.dataset.activeSoalId = soal.id;
        }

        duelStartTime = Date.now();

        Object.entries(soal.opsi_jawaban).forEach(([kunci, teks]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition-all duration-150 hover:-translate-y-0.5 hover:border-rose-400 hover:bg-rose-50';
            button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
            button.dataset.kunci = kunci;
            button.addEventListener('click', () => {
                const timeTaken = Date.now() - duelStartTime;
                submitDuelAnswer(soal.id, kunci, timeTaken, button);
            });
            if (duelOptionsEl) duelOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'duel-modal' }));
        startDuelCountdown(20000, soal.id, duelStartTime); // 20s
    }

    function hideDuel() {
        clearInterval(duelCountdownInterval);
        if (duelOpponentSimTimeout) clearTimeout(duelOpponentSimTimeout);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'duel-modal' }));
    }

    // ---------------------------------------------------------------
    // Realtime (Multiplayer)
    // ---------------------------------------------------------------
    /**
     * Dulu berupa string statis pakai "Lawan" (masuk akal untuk 2 pemain,
     * ambigu untuk 3-6 pemain — tidak jelas siapa "lawan" yang dimaksud).
     * Sekarang fungsi yang menerima NAMA pemain sungguhan (diambil dari
     * `payload.game_player_id`, lihat resolveActorName() di bawah).
     */
    const REALTIME_EVENT_LABELS = {
        'dice-rolled': (nama) => `${nama} melempar dadu.`,
        'pawn-moved': (nama) => `${nama} menggerakkan pion.`,
        'movement-blocked': (nama) => `Langkah ${nama} terlalu jauh, giliran dilewati.`,
        'connector-applied': (nama) => `${nama} menginjak tangga/ular.`,
        'question-presented': (nama) => `${nama} sedang menjawab soal.`,
        'score-updated': (nama) => `Skor ${nama} berubah.`,
        'game-finished': () => 'Permainan telah selesai.',
        'session-paused': (nama) => `${nama} terputus koneksi. Menunggu reconnect...`,
        'session-resumed': (nama) => `${nama} telah kembali terhubung.`,
        'duel-progress': (nama) => `${nama} telah menjawab soal duel.`,
        'duel-finished': (nama) => `Duel selesai.`,
    };

    function resolveActorName(actorId) {
        const actor = latestSession?.players.find((p) => p.id === actorId);
        if (!actor) return 'Pemain lain';
        return actor.is_robot ? 'Robot' : (actor.nama ?? 'Pemain lain');
    }

    let colyseusClient = null;
    let colyseusRoom = null;
    let isJoiningColyseus = false;

    async function joinRealtimeChannel(session) {
        if (session.mode !== 'multiplayer' || colyseusRoom || isJoiningColyseus) {
            return;
        }

        isJoiningColyseus = true;

        try {
            if (!colyseusClient) {
                const defaultColyseusUrl = (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/colyseus';
                const colyseusUrl = import.meta.env.VITE_COLYSEUS_URL || defaultColyseusUrl;
                colyseusClient = new Colyseus.Client(colyseusUrl);
            }
            colyseusRoom = await colyseusClient.joinOrCreate("game_room", { 
                token: csrfToken,
                session_id: session.id,
                player_id: myGamePlayerId
            });

            console.log("Joined Colyseus room successfully", colyseusRoom.roomId);

            let diceRollPromise = Promise.resolve();

            colyseusRoom.onMessage("state_changed", (message) => {
                const eventName = message.event;
                const actorId = message.actor ?? null;
                
                if (actorId !== null && actorId === myGamePlayerId) {
                    return; // Ignore our own broadcasts
                }

                if (eventName === 'dice-rolled') {
                    // Animasi dadu dijalankan dan disimpan promisenya
                    diceRollPromise = animateDiceRoll(message.raw_nilai_dadu ?? message.nilai_dadu ?? 1, message.double_dice_active ?? false);
                    return; // Tunggu event pawn-moved untuk me-loadState
                }
                
                if (eventName === 'mystery-applied') {
                    showToast(`${resolveActorName(actorId)} mendapatkan item Misteri: ${message.item_name ?? 'Item'}!`);
                    pushLog('info', `${resolveActorName(actorId)} mendapatkan power-up!`);
                    // We let it continue to loadState() so the pawn movement syncs
                }

                if (REALTIME_EVENT_LABELS[eventName]) {
                    const label = REALTIME_EVENT_LABELS[eventName](resolveActorName(actorId));
                    showToast(label);
                    pushLog('info', label);
                }
                
                diceRollPromise.then(() => {
                    loadState();
                });
            });

            colyseusRoom.onMessage("error", (msg) => {
                showToast(msg);
                pushLog('error', msg);
            });

        } catch (e) {
            console.error("Colyseus JOIN ERROR", e);
        } finally {
            isJoiningColyseus = false;
        }
    }

    function leaveRealtimeChannel(session) {
        if (colyseusRoom) {
            colyseusRoom.leave();
            colyseusRoom = null;
        }
    }

    function startHeartbeat(session) {
        // MATIKAN SEMENTARA untuk keperluan debugging agar Debugbar tidak penuh
        return; 

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
                placePawnAt(el, p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
                continue;
            }

            if (prev === p.posisi_pion) {
                continue;
            }

            const naik = p.posisi_pion > prev;
            // Cek apakah ada konektor yang menghubungkan posisi sebelumnya (prev) dengan posisi baru (p.posisi_pion)
            // Ini untuk mendeteksi apakah pergerakan (walaupun jaraknya dekat <= 6) sebenarnya adalah karena naik tangga / turun ular
            const konektor = Array.from(konektorByStart.values())
                .find((k) => k.posisi_awal === prev && k.posisi_akhir === p.posisi_pion && (naik ? k.jenis === 'tangga' : k.jenis === 'ular'));

            if (konektor) {
                await delay(100);
                if (naik) {
                    playSound('climb_ladder');
                    await window.BoardVisuals?.reactLadder?.(konektor.posisi_awal);
                    await animateAlongConnector(el, konektor.posisi_awal, p.posisi_pion, 'tangga', 900);
                    spawnParticles(el, 'sparkle', 10);
                    el.classList.add('pawn-climb');
                    setTimeout(() => el.classList.remove('pawn-climb'), 650);
                } else {
                    playSound('snake_eat');
                    await window.BoardVisuals?.reactSnake?.(konektor.posisi_awal);
                    boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, konektor.posisi_awal, p.posisi_pion, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => boardEl.classList.remove('board-shake'), 400);
                }
            } else if (p.posisi_pion > prev && p.posisi_pion - prev <= 6) {
                // eslint-disable-next-line no-await-in-loop
                for (let step = prev + 1; step <= p.posisi_pion; step++) {
                    const stackIndex = step === p.posisi_pion ? stackIndexAt(step, p.id, session.players) : 0;
                    
                    el.classList.remove('pawn-hop');
                    void el.offsetWidth;
                    el.classList.add('pawn-hop');
                    
                    placePawnAt(el, step, stackIndex);
                    playSound('pion_walk');
                    // eslint-disable-next-line no-await-in-loop
                    await delay(400);
                }
                el.classList.remove('pawn-hop');
            } else {
                // Pergerakan jauh tapi bukan konektor dari posisi awal (misal efek teleport misteri, atau konektor tapi posisi awalnya sudah kita lewatkan karena animasi dadu)
                el.classList.add(naik ? 'pawn-climb' : 'pawn-slide');
                await delay(450);
                el.classList.remove('pawn-climb', 'pawn-slide');
            }

            placePawnAt(el, p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
        }
    }

    async function animateWhirlwind(triggerPlayerId) {
        const promises = [];
        for (const p of latestSession.players) {
            if (p.id === triggerPlayerId) continue;
            const prev = knownPositions.get(p.id);
            if (prev === undefined) continue;
            
            const newPos = Math.max(1, prev - 3);
            if (prev > newPos) {
                const el = getOrCreatePawnEl(p);
                el.classList.add('pawn-slide');
                promises.push(delay(450).then(() => {
                    el.classList.remove('pawn-slide');
                    placePawnAt(el, newPos, stackIndexAt(newPos, p.id, latestSession.players));
                    knownPositions.set(p.id, newPos);
                }));
            }
        }
        if (promises.length > 0) {
            await Promise.all(promises);
            await delay(200);
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

        const isMyTurn = session.current_turn_game_player_id === myGamePlayerId;
        const currentPlayer = session.players.find((p) => p.id === session.current_turn_game_player_id);

        if (session.active_question) {
            showQuestion(
                session.active_question, 
                session.active_question_expires_at, 
                isMyTurn, 
                currentPlayer?.nama ?? 'Pemain'
            );
        } else {
            hideQuestion();
        }

        if (session.active_duel) {
            showDuel(session.active_duel);
        } else {
            hideDuel();
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
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'achievement-unlock-modal' }));
        if (achievementQueue.length > 0) {
            setTimeout(() => showNextAchievementUnlock(), 250);
        }
    });

    function applySessionState(session, { pawnMode = 'diff', skipPawnIds = new Set(), deferOutcome = false } = {}) {
        latestSession = session;

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
            animationChain = animationChain.then(() => animateExternalDiff(session)).then(() => syncKnownPositions(session));
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
            throw new Error(data.error || data.message || 'Terjadi kesalahan.');
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
            const forcedRollEl = document.getElementById('forced-roll-input');
            const forcedRoll = forcedRollEl ? forcedRollEl.value : null;
            const body = forcedRoll ? { forced_roll: parseInt(forcedRoll, 10) } : {};
            const result = await postJson(rollUrl, body);

            if (colyseusRoom) {
                // Beri tahu Colyseus untuk mem-broadcast ke klien lain
                colyseusRoom.send("broadcast_event", { 
                    event: 'dice-rolled', 
                    actor: myGamePlayerId,
                    nilai_dadu: result.nilai_dadu ?? 1,
                    raw_nilai_dadu: result.raw_nilai_dadu,
                    double_dice_active: result.double_dice_active ?? false
                });
                colyseusRoom.send("broadcast_event", { event: 'pawn-moved', actor: myGamePlayerId });
            }

            await animateDiceRoll(result.raw_nilai_dadu ?? result.nilai_dadu ?? 1, result.double_dice_active ?? false);

            if (result.toast) {
                showToast(result.toast);
            }

            const actingPlayer = result.session.players.find((p) => p.id === myGamePlayerId) ?? me;
            if (actingPlayer) {
                logTurnResult(actingPlayer, result);
                await animatePlayerTurn(actingPlayer, fromPosisi, result);
                knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
                
                if (result.type === 'mystery' && result.item_id === 'whirlwind') {
                    await animateWhirlwind(actingPlayer.id);
                }
            }

            if (result.type === 'answered') {
                logAnswerResult(actingPlayer, result.benar);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const finalRobotFromPosisi = robot ? (knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
            const skipIds = new Set([myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, finalRobotFromPosisi);
            finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
        } catch (error) {
            showToast(error.message);
        } finally {
            rollButton.disabled = false;
        }
    }

    const botQuestionTextEl = document.getElementById('bot-question-text');
    const botQuestionOptionsEl = document.getElementById('bot-question-options');
    const botQuestionFeedbackEl = document.getElementById('bot-question-feedback');

    function showBotQuestion(soal, jawabanRobot, benar, kunciJawaban) {
        if (!botQuestionTextEl) return;
        botQuestionTextEl.textContent = soal.pertanyaan;
        botQuestionOptionsEl.innerHTML = '';
        botQuestionFeedbackEl.classList.add('hidden');

        Object.entries(soal.opsi_jawaban).forEach(([kunci, teks]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.disabled = true;
            button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 cursor-default';
            button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
            button.dataset.kunci = kunci;
            botQuestionOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'bot-question-modal' }));

        // Show answer feedback after a short delay
        setTimeout(() => {
            const selectedBtn = Array.from(botQuestionOptionsEl.children).find(b => b.dataset.kunci?.toUpperCase() === jawabanRobot?.toUpperCase());
            const correctBtn = Array.from(botQuestionOptionsEl.children).find(b => b.dataset.kunci?.toUpperCase() === kunciJawaban?.toUpperCase());

            if (selectedBtn) {
                if (benar) {
                    selectedBtn.classList.remove('border-slate-200', 'text-slate-700');
                    selectedBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                } else {
                    selectedBtn.classList.remove('border-slate-200', 'text-slate-700');
                    selectedBtn.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                    if (correctBtn && correctBtn !== selectedBtn) {
                        correctBtn.classList.remove('border-slate-200', 'text-slate-700');
                        correctBtn.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                    }
                }
            }

            playSound(benar ? 'correct_answer' : 'false_answer');
            botQuestionFeedbackEl.textContent = benar ? '✅ Bot menjawab benar!' : '❌ Bot menjawab salah!';
            botQuestionFeedbackEl.classList.remove('hidden');
        }, 800);
    }

    function hideBotQuestion() {
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'bot-question-modal' }));
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

            // Show bot question modal if this turn has a soal
            if (turn.soal) {
                showBotQuestion(turn.soal, turn.jawaban_robot, turn.benar, turn.kunci_jawaban);
                await delay(2500);
                hideBotQuestion();
                await delay(300);
            } else if (turn.type === 'answered' || turn.benar !== undefined) {
                logAnswerResult(robot, turn.benar);
            }

            // Turn terakhir membawa posisi akhir robot yang sesungguhnya (session
            // sudah final di titik ini); turn di tengah diasumsikan tidak
            // memindahkan posisi lebih lanjut (server memainkan robot secara
            // berurutan dalam satu putaran, lihat GameSessionService).
            let toPosisi = turn === robotTurns[robotTurns.length - 1] ? robot.posisi_pion : fromPosisi;
            if (turn.konektor_applied && turn.konektor_info) {
                // Posisi pion asli robot sebelum animasi connector dijalankan
                toPosisi = turn.konektor_info.posisi_awal;
            }

            // eslint-disable-next-line no-await-in-loop
            await animatePlayerTurn(robot, fromPosisi, { ...turn, posisi_pion: toPosisi });
            fromPosisi = toPosisi;
            
            if (turn.type === 'mystery' && turn.item_id === 'whirlwind') {
                await animateWhirlwind(robot.id);
                fromPosisi = knownPositions.get(robot.id) ?? robot.posisi_pion;
            }
            
            if (turn.konektor_applied && turn.konektor_info) {
                const el = getOrCreatePawnEl(robot);
                const info = turn.konektor_info;
                const isNaik = info.jenis === 'tangga';

                await delay(120);
                if (isNaik) {
                    playSound('climb_ladder');
                    await window.BoardVisuals?.reactLadder?.(info.posisi_awal);
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'tangga', 900);
                    spawnParticles(el, 'sparkle', 10);
                    el.classList.add('pawn-climb');
                    setTimeout(() => el.classList.remove('pawn-climb'), 650);
                } else {
                    playSound('snake_eat');
                    await window.BoardVisuals?.reactSnake?.(info.posisi_awal);
                    boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => boardEl.classList.remove('board-shake'), 400);
                }
                
                fromPosisi = info.posisi_akhir;
                placePawnAt(el, fromPosisi, stackIndexAt(fromPosisi, robot.id, session?.players));
                await delay(200);
            }
        }

        knownPositions.set(robot.id, robot.posisi_pion);
        setTileGlow(latestSession?.status === 'playing' ? latestSession.players.find((p) => p.id === latestSession.current_turn_game_player_id)?.posisi_pion : null);
    }

    async function submitDuelAnswer(soalId, jawaban, timeTakenMs, selectedButtonEl = null) {
        clearInterval(duelCountdownInterval);

        try {
            const duelAnswerUrl = answerUrl.replace('/answer', '/duel-answer');
            const result = await postJson(duelAnswerUrl, { soal_id: soalId, jawaban, time_taken_ms: timeTakenMs });

            if (result.type === 'duel_answered') {
                if (colyseusRoom) {
                    colyseusRoom.send("broadcast_event", { event: 'duel-progress', actor: myGamePlayerId });
                }
                
                playSound(result.benar ? 'correct_answer' : 'false_answer');

                duelFeedbackEl.textContent = result.benar
                    ? 'Jawaban benar!'
                    : `Jawaban salah. ${result.pembahasan ?? ''}`;
                duelFeedbackEl.classList.remove('hidden');

                if (selectedButtonEl) {
                    if (result.benar) {
                        selectedButtonEl.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                        selectedButtonEl.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                    } else {
                        selectedButtonEl.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                        selectedButtonEl.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                        
                        if (result.kunci_jawaban) {
                            const correctButton = Array.from(duelOptionsEl.children).find(b => b.dataset.kunci && b.dataset.kunci.toUpperCase() === result.kunci_jawaban.toUpperCase());
                            if (correctButton) {
                                correctButton.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                                correctButton.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                            }
                        }
                    }
                }

                Array.from(duelOptionsEl.children).forEach(b => {
                    b.disabled = true;
                    b.classList.remove('hover:-translate-y-0.5');
                });

                await delay(1800);
                
                // Refresh state to show next question or wait screen
                // IMPORTANT: Fetch fresh state instead of using result.session
                // to avoid overwriting a 'playing' state if the opponent finished
                // the duel during our 1.8s delay.
                await loadState();
            } else if (result.type === 'duel_finished') {
                if (colyseusRoom) {
                    colyseusRoom.send("broadcast_event", { event: 'duel-finished', actor: myGamePlayerId });
                }

                duelFeedbackEl.textContent = 'Semua soal telah dijawab. Memproses hasil duel...';
                duelFeedbackEl.classList.remove('hidden');

                await delay(1500);
                hideDuel();

                const duel = result.duel;
                const loser = result.session.players.find(p => p.id === duel.loser_id);

                if (loser) {
                    showToast(`Duel selesai! ${duel.winner?.nama || 'Pemain'} menang. ${loser.nama || 'Pemain'} terlempar mundur ${duel.loser_penalty_roll} langkah.`);
                    await animateDiceRoll(duel.loser_penalty_roll);
                    const loserFromPosisi = knownPositions.get(loser.id) ?? 1;
                    await animatePlayerTurn(loser, loserFromPosisi, { type: 'normal', nilai_dadu: -duel.loser_penalty_roll });
                    knownPositions.set(loser.id, loser.posisi_pion);
                }

                // Capture robotFromPosisi AFTER loser animation so if bot was loser,
                // its knownPosition is already updated to the post-penalty position.
                const robot = result.session.players.find((p) => p.is_robot);
                const robotFromPosisi = robot ? (knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
                const skipIds = new Set([myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
                
                applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

                await playRobotTurns(result.robot_turns, result.session, robotFromPosisi);
                finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
            }

        } catch (error) {
            showToast(error.message);
            hideDuel();
        }
    }

    async function submitAnswer(soalId, jawaban, selectedButtonEl = null) {
        const robotBefore = latestSession?.players.find((p) => p.is_robot);
        const robotFromPosisi = robotBefore ? (knownPositions.get(robotBefore.id) ?? robotBefore.posisi_pion) : 0;
        const meBefore = latestSession?.players.find((p) => p.id === myGamePlayerId);
        const myFromPosisi = meBefore ? meBefore.posisi_pion : 1;

        try {
            const result = await postJson(answerUrl, { soal_id: soalId, jawaban });

            if (colyseusRoom) {
                if (result.mystery_applied) {
                    colyseusRoom.send("broadcast_event", { 
                        event: 'mystery-applied', 
                        actor: myGamePlayerId,
                        item_id: result.mystery_effect?.item_id,
                        item_name: result.mystery_effect?.item_name
                    });
                } else {
                    colyseusRoom.send("broadcast_event", { event: 'score-updated', actor: myGamePlayerId });
                }
            }
            
            playSound(result.benar ? 'correct_answer' : 'false_answer');

            questionFeedbackEl.textContent = result.benar
                ? 'Jawaban benar!'
                : `Jawaban salah. ${result.pembahasan ?? ''}`;
            questionFeedbackEl.classList.remove('hidden');

            if (selectedButtonEl) {
                if (result.benar) {
                    selectedButtonEl.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                    selectedButtonEl.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                } else {
                    selectedButtonEl.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                    selectedButtonEl.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                    
                    if (result.kunci_jawaban) {
                        const correctButton = Array.from(questionOptionsEl.children).find(b => b.dataset.kunci && b.dataset.kunci.toUpperCase() === result.kunci_jawaban.toUpperCase());
                        if (correctButton) {
                            correctButton.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                            correctButton.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                        }
                    }
                }
            }

            Array.from(questionOptionsEl.children).forEach(b => {
                b.disabled = true;
                b.classList.remove('hover:-translate-y-0.5');
            });

            const actingPlayer = result.session.players.find((p) => p.id === myGamePlayerId);
            if (actingPlayer) {
                logAnswerResult(actingPlayer, result.benar);
            }

            await delay(1800);
            hideQuestion();

            // Jawaban bisa memicu konektor (ular/tangga)
            if (actingPlayer && result.konektor_applied && result.konektor_info) {
                const el = getOrCreatePawnEl(actingPlayer);
                const info = result.konektor_info;
                const isNaik = info.jenis === 'tangga';

                await delay(120);

                if (isNaik) {
                    playSound('climb_ladder');
                    await window.BoardVisuals?.reactLadder?.(info.posisi_awal);
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'tangga', 900);
                    spawnParticles(el, 'sparkle', 10);
                    el.classList.add('pawn-climb');
                    setTimeout(() => el.classList.remove('pawn-climb'), 650);
                } else {
                    playSound('snake_eat');
                    await window.BoardVisuals?.reactSnake?.(info.posisi_awal);
                    boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => boardEl.classList.remove('board-shake'), 400);
                }

                placePawnAt(el, info.posisi_akhir, stackIndexAt(info.posisi_akhir, actingPlayer.id, result.session?.players));
                await delay(200);
            } else if (actingPlayer && actingPlayer.posisi_pion !== myFromPosisi) {
                // Fallback jika berubah posisi tapi bukan konektor
                await animatePlayerTurn(actingPlayer, myFromPosisi, { type: 'answered', nilai_dadu: actingPlayer.posisi_pion - myFromPosisi });
            }

            // Jika respons adalah 'soal', berarti butuh menjawab soal lagi (misal menginjak misteri setelah konektor)
            if (result.type === 'soal') {
                latestSession = result.session;
                showQuestion(
                    result.soal, 
                    new Date(Date.now() + 15000).toISOString(),
                    true,
                    actingPlayer?.nama ?? 'Pemain'
                );
                return; // Hentikan alur di sini, tunggu pemain submit jawaban lagi
            }

            // Cek apakah misteri diterapkan
            if (result.mystery_applied && actingPlayer && actingPlayer.id === myGamePlayerId) {
                const mysteryWithSession = { ...result.mystery_effect, session: result.session };
                await showGachaModal(mysteryWithSession);
                if (result.mystery_effect && result.mystery_effect.item_id === 'whirlwind') {
                    await animateWhirlwind(actingPlayer.id);
                }
            }
            if (actingPlayer) {
                knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const finalRobotFromPosisi = robot ? (knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
            const skipIds = new Set([myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, finalRobotFromPosisi);
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

    // Tambahan untuk fullscreen: buat area dadu bisa diklik langsung
    if (diceGlowWrap && rollButton) {
        diceGlowWrap.addEventListener('click', () => {
            if (!rollButton.disabled) rollButton.click();
        });
        diceGlowWrap.title = 'Klik untuk melempar dadu';
        
        const syncDiceGlowCursor = () => {
            diceGlowWrap.style.cursor = rollButton.disabled ? 'not-allowed' : 'pointer';
        };
        new MutationObserver(syncDiceGlowCursor).observe(rollButton, { attributes: true, attributeFilter: ['disabled'] });
        syncDiceGlowCursor();
    }
    window.addEventListener('resize', initDiceSize);

    /**
     * FAB aksi mobile (5 tombol, lihat #mobile-action-fab di show.blade.php)
     * — "remote control" murni, tidak ada logic yang diduplikasi:
     *  - Dadu: mirror state disabled dari #roll-dice-button asli (MutationObserver,
     *    sama seperti versi FAB dadu sebelumnya) + forward klik ke sana.
     *  - Keluar: forward klik ke #leave-game-button asli (confirm() dkk tetap
     *    di situ, satu-satunya tempat).
     *  - Progress/Pemain/Log: dispatch event `open-mobile-panel` yang didengar
     *    Alpine di masing-masing panel (show.blade.php) untuk membukanya
     *    sebagai overlay — TIDAK ada state panel yang disimpan di JS ini.
     */
    (function initMobileActionFab() {
        const diceBtn = document.getElementById('mobile-fab-dice');
        const leaveBtn = document.getElementById('mobile-fab-leave');
        const leaveButtonReal = document.getElementById('leave-game-button');

        if (diceBtn && rollButton) {
            const syncDice = () => { diceBtn.disabled = rollButton.disabled; };
            new MutationObserver(syncDice).observe(rollButton, { attributes: true, attributeFilter: ['disabled'] });
            diceBtn.addEventListener('click', () => rollButton.click());
            syncDice();
        }

        leaveBtn?.addEventListener('click', () => leaveButtonReal?.click());

        document.querySelectorAll('#mobile-action-fab [data-open-panel]').forEach((btn) => {
            btn.addEventListener('click', () => {
                window.dispatchEvent(new CustomEvent('open-mobile-panel', { detail: btn.dataset.openPanel }));
            });
        });
    })();

    document.getElementById('leave-game-button')?.addEventListener('click', async () => {
        const isMultiplayer = latestSession?.mode === 'multiplayer';
        // Pesan digeneralisasi untuk game 2-6 pemain: kalau cuma tersisa 1
        // pemain aktif lain, dia otomatis menang (WO) - kalau masih ada 2+,
        // permainan lanjut tanpamu (lihat GameSessionService::leave()).
        const confirmMessage = isMultiplayer
            ? 'Yakin ingin keluar? Anda akan dinyatakan kalah WO. Kalau masih ada pemain lain, permainan lanjut tanpa Anda; kalau tersisa satu, dia otomatis menang.'
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

    const toggleAudioBtn = document.getElementById('toggle-audio-btn');
    if (toggleAudioBtn) {
        toggleAudioBtn.addEventListener('click', () => {
            isMuted = !isMuted;
            if (isMuted) {
                stopBGM();
                toggleAudioBtn.textContent = '🔇 Mute';
                toggleAudioBtn.classList.add('text-rose-500', 'border-rose-200');
            } else {
                startBGM();
                toggleAudioBtn.textContent = '🔊 Suara';
                toggleAudioBtn.classList.remove('text-rose-500', 'border-rose-200');
            }
        });
    }

    // ---------------------------------------------------------------
    // FULLSCREEN TOGGLE (CSS-only: avoids browser fullscreen API
    // which hides elements teleported outside the fullscreen element,
    // like Alpine x-teleport modals, dice animation backdrop, etc.)
    // ---------------------------------------------------------------
    const fullscreenBtn = document.getElementById('toggle-fullscreen-btn');
    const fullscreenContainer = document.getElementById('game-fullscreen-container');

    const EXPAND_ICON = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>`;
    const SHRINK_ICON = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 14h4v4m0-4l-5 5m15-1v-4h-4m4 0l-5-5M4 10h4V6m-4 4l-5-5m15-1v4h-4m4 0l-5 5" /></svg>`;

    if (fullscreenBtn && fullscreenContainer) {
        let isFullscreen = false;

        const enterFullscreen = () => {
            isFullscreen = true;
            document.body.classList.add('game-fullscreen-active');
            fullscreenContainer.classList.add('is-fullscreen');
            fullscreenBtn.innerHTML = SHRINK_ICON;
            fullscreenBtn.setAttribute('title', 'Keluar Mode Layar Penuh');
            showToast('Mode Layar Penuh Aktif — tekan tombol pojok kanan atas untuk keluar', 'info');
        };

        const exitFullscreen = () => {
            isFullscreen = false;
            document.body.classList.remove('game-fullscreen-active');
            fullscreenContainer.classList.remove('is-fullscreen');
            fullscreenBtn.innerHTML = EXPAND_ICON;
            fullscreenBtn.setAttribute('title', 'Mode Layar Penuh');
        };

        fullscreenBtn.addEventListener('click', () => {
            if (isFullscreen) exitFullscreen();
            else enterFullscreen();
        });

        // ESC key to exit fullscreen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isFullscreen) exitFullscreen();
        });
    }

    loadInitialState();
});
