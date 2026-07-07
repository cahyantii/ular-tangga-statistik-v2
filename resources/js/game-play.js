import { renderBoardGrid, renderPawns } from './board-renderer';

/**
 * Kontroler gameplay Vs Robot & Multiplayer (Tahap 10c/12c). Frontend TIDAK
 * menyimpan state otoritatif apa pun — setiap aksi memanggil server, dan
 * tampilan hanya merender ulang respons/​state server (Tahap 2/5, keputusan
 * final). Animasi (dadu, dsb.) selalu terjadi SETELAH respons server
 * diterima. Untuk Multiplayer, broadcast Reverb (Tahap 12b) hanya dipakai
 * sebagai SINYAL "ada yang berubah" — begitu diterima, kita re-fetch state
 * lewat endpoint yang sama dengan refresh-recovery, bukan merender langsung
 * dari payload broadcast (yang sengaja minim, lihat memori Tahap 12b).
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('game-play-data');
    if (!root) {
        return;
    }

    const board = JSON.parse(root.dataset.board);
    const stateUrl = root.dataset.stateUrl;
    const rollUrl = root.dataset.rollUrl;
    const answerUrl = root.dataset.answerUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const heartbeatUrl = root.dataset.heartbeatUrl;
    const currentUserId = parseInt(root.dataset.currentUserId, 10);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const boardGrid = document.getElementById('board-grid');
    const rollButton = document.getElementById('roll-dice-button');
    const diceValueEl = document.getElementById('dice-value');
    const turnIndicatorEl = document.getElementById('turn-indicator');
    const toastEl = document.getElementById('game-toast');
    const finishedBannerEl = document.getElementById('game-finished-banner');
    const pausedBannerEl = document.getElementById('game-paused-banner');
    const pausedTimerEl = document.getElementById('game-paused-timer');
    const playerListEl = document.getElementById('player-list');

    const questionModal = document.getElementById('question-modal');
    const questionTextEl = document.getElementById('question-text');
    const questionOptionsEl = document.getElementById('question-options');
    const questionTimerEl = document.getElementById('question-timer');
    const questionFeedbackEl = document.getElementById('question-feedback');

    let myGamePlayerId = null;
    let latestSession = null;
    let questionCountdownInterval = null;
    let pausedCountdownInterval = null;
    let heartbeatIntervalId = null;
    let presenceChannel = null;
    let leaveConfirmed = false;

    renderBoardGrid(boardGrid, {
        jumlahKolom: board.jumlah_kolom,
        jumlahPetak: board.jumlah_petak,
        petak: board.petak,
        konektor: board.konektor,
    });

    function showToast(message) {
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    /**
     * Giliran robot (Tahap 11) sudah selesai dieksekusi server SEBELUM respons
     * ini diterima (sinkron, satu transaksi) — di sini kita hanya menyusun
     * ulang urutan kejadiannya sebagai toast berjeda, murni presentasi.
     */
    function describeRobotTurn(turn) {
        const dadu = `Robot: dadu ${turn.nilai_dadu ?? '-'}`;

        switch (turn.type) {
            case 'blocked':
                return `${dadu}, langkah terlalu jauh (giliran dilewati).`;
            case 'bonus':
                return `${dadu}, kena petak Bonus.`;
            case 'penalti':
                return `${dadu}, kena petak Penalti.`;
            case 'mystery':
                return `${dadu}, kena petak Mystery.`;
            case 'soal':
                return `${dadu}, menjawab soal (${turn.benar ? 'benar' : 'salah'}).`;
            case 'finished':
                return `${dadu}, robot mencapai Finish!`;
            default:
                return `${dadu}.`;
        }
    }

    function queueRobotTurnToasts(robotTurns) {
        (robotTurns ?? []).forEach((turn, index) => {
            setTimeout(() => showToast(describeRobotTurn(turn)), (index + 1) * 1800);
        });
    }

    function renderPlayers(session) {
        playerListEl.innerHTML = '';
        session.players.forEach((p) => {
            const isMe = !p.is_robot && myGamePlayerId === p.id;
            const card = document.createElement('div');
            card.className = `rounded-xl border p-4 ${session.current_turn_game_player_id === p.id ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-white'}`;
            card.innerHTML = `
                <p class="text-sm font-semibold text-slate-800">${p.nama}${isMe ? ' (Anda)' : ''}</p>
                <p class="text-xs text-slate-500 mt-1">Posisi: ${p.posisi_pion} &middot; Skor: ${p.skor}</p>
            `;
            playerListEl.appendChild(card);
        });
    }

    function updateTurnIndicator(session) {
        const currentPlayer = session.players.find((p) => p.id === session.current_turn_game_player_id);
        const isMyTurn = currentPlayer && !currentPlayer.is_robot && myGamePlayerId === currentPlayer.id;

        if (session.status !== 'playing') {
            turnIndicatorEl.textContent = '';
            rollButton.classList.add('hidden');
            return;
        }

        turnIndicatorEl.textContent = isMyTurn
            ? 'Giliran Anda'
            : `Menunggu giliran ${currentPlayer?.is_robot ? 'Robot' : (currentPlayer?.nama ?? '...')}`;

        const questionPending = !!session.active_question;
        rollButton.classList.toggle('hidden', !isMyTurn || questionPending);
    }

    function renderFinished(session) {
        if (session.status === 'finished') {
            const winner = session.players.find((p) => p.id === session.winner_game_player_id);
            const isMeWinner = winner && myGamePlayerId === winner.id;
            finishedBannerEl.textContent = isMeWinner
                ? 'Selamat, Anda menang!'
                : `Permainan selesai. ${winner?.is_robot ? 'Robot' : winner?.nama} menang.`;
            finishedBannerEl.classList.remove('hidden');
            rollButton.classList.add('hidden');
        } else if (session.status === 'abandoned') {
            finishedBannerEl.textContent = 'Permainan ini telah dihentikan.';
            finishedBannerEl.classList.remove('hidden');
            rollButton.classList.add('hidden');
        } else {
            finishedBannerEl.classList.add('hidden');
        }
    }

    /**
     * Paused = lawan multiplayer terputus koneksi (Tahap 12a). Countdown
     * dihitung dari `reconnect_deadline_at` yang dikirim server (Tahap 12c) —
     * bukan dihitung sendiri oleh klien, supaya tetap konsisten walau klien
     * baru membuka/refresh halaman di tengah masa tenggang.
     */
    function renderPaused(session) {
        clearInterval(pausedCountdownInterval);

        if (session.status !== 'paused' || !pausedBannerEl) {
            pausedBannerEl?.classList.add('hidden');
            return;
        }

        pausedBannerEl.classList.remove('hidden');

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
            button.className = 'w-full text-left rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-emerald-50 hover:border-emerald-400';
            button.textContent = `${kunci}. ${teks}`;
            button.addEventListener('click', () => submitAnswer(soal.id, kunci));
            questionOptionsEl.appendChild(button);
        });

        questionModal.classList.remove('hidden');
        startQuestionCountdown(expiresAtIso, soal.id);
    }

    function hideQuestion() {
        clearInterval(questionCountdownInterval);
        questionModal.classList.add('hidden');
    }

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

    /**
     * Vs Robot tidak pernah join channel apa pun (hanya satu manusia, hasil
     * aksinya sendiri sudah didapat lewat respons HTTP - Tahap 12b). Setiap
     * broadcast yang diterima di sini hanya memicu re-fetch state, TIDAK
     * dirender langsung dari payloadnya (payload sengaja minim).
     */
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

    /**
     * Sinyal "aku masih di sini" (Tahap 12a) — hanya relevan untuk Multiplayer,
     * jauh di bawah `heartbeat_timeout_seconds` (default 15s) supaya tidak
     * salah terdeteksi terputus akibat jeda jaringan wajar.
     */
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

    function applySessionState(session) {
        latestSession = session;

        renderPawns(boardGrid, session.players);
        renderPlayers(session);
        updateTurnIndicator(session);
        renderFinished(session);
        renderPaused(session);

        if (session.active_question) {
            showQuestion(session.active_question, session.active_question_expires_at);
        } else {
            hideQuestion();
        }

        joinRealtimeChannel(session);
        startHeartbeat(session);
        stopRealtimeIfSessionOver(session);
    }

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

        try {
            const result = await postJson(rollUrl);

            diceValueEl.textContent = result.nilai_dadu ?? '-';
            diceValueEl.classList.remove('hidden');

            if (result.type === 'blocked') {
                showToast(`Dadu ${result.nilai_dadu}: langkah terlalu jauh, giliran dilewati.`);
            } else if (result.type === 'bonus') {
                showToast('Petak Bonus! Skor bertambah.');
            } else if (result.type === 'penalti') {
                showToast('Kena petak Penalti. Skor berkurang.');
            } else if (result.type === 'mystery') {
                showToast('Petak Mystery! Efek acak diterapkan.');
            } else if (result.type === 'finished') {
                showToast('Permainan selesai!');
            }

            applySessionState(result.session);
            queueRobotTurnToasts(result.robot_turns);
        } catch (error) {
            showToast(error.message);
        } finally {
            rollButton.disabled = false;
        }
    }

    async function submitAnswer(soalId, jawaban) {
        try {
            const result = await postJson(answerUrl, { soal_id: soalId, jawaban });

            questionFeedbackEl.textContent = result.benar
                ? 'Jawaban benar!'
                : `Jawaban salah. ${result.pembahasan}`;
            questionFeedbackEl.classList.remove('hidden');

            setTimeout(() => {
                applySessionState(result.session);
                queueRobotTurnToasts(result.robot_turns);
            }, 2000);
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
        const response = await fetch(stateUrl, { headers: { Accept: 'application/json' } });
        const session = await response.json();

        const me = session.players.find((p) => p.user_id === currentUserId);
        myGamePlayerId = me ? me.id : null;

        applySessionState(session);
    }

    rollButton?.addEventListener('click', rollDice);

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
