import { state, dom, config } from './state.js';
import { fetchState } from './http.js';

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
    };

    function resolveActorName(actorId) {
        const actor = state.latestSession?.players.find((p) => p.id === actorId);
        if (!actor) return 'Pemain lain';
        return actor.is_robot ? 'Robot' : (actor.nama ?? 'Pemain lain');
    }

    function joinRealtimeChannel(session) {
        if (session.mode !== 'multiplayer' || !session.room_id || state.presenceChannel || !window.Echo) {
            return;
        }

        state.presenceChannel = window.Echo.join(`room.${session.room_id}`);

        Object.keys(REALTIME_EVENT_LABELS).forEach((eventName) => {
            state.presenceChannel.listen(`.${eventName}`, (payload) => {
                const actorId = payload.game_player_id ?? null;
                if (actorId !== null && actorId === state.myGamePlayerId) {
                    return;
                }

                const label = REALTIME_EVENT_LABELS[eventName](resolveActorName(actorId));
                showToast(label);
                pushLog('info', label);
                loadState();
            });
        });
    }

    function leaveRealtimeChannel(session) {
        if (state.presenceChannel && window.Echo && session.room_id) {
            window.Echo.leave(`room.${session.room_id}`);
            state.presenceChannel = null;
        }
    }

    function startHeartbeat(session) {
        if (session.mode !== 'multiplayer' || state.heartbeatIntervalId) {
            return;
        }

        state.heartbeatIntervalId = setInterval(() => {
            fetch(config.heartbeatUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrfToken, Accept: 'application/json' },
            }).catch(() => {});
        }, 8000);
    }

    function stopRealtimeIfSessionOver(session) {
        if (session.status === 'finished' || session.status === 'abandoned') {
            clearInterval(state.heartbeatIntervalId);
            state.heartbeatIntervalId = null;
            leaveRealtimeChannel(session);
        }
    }
