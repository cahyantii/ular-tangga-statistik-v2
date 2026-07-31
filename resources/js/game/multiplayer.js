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

import * as Colyseus from "colyseus.js";

// Global colyseus room instance
export let colyseusRoom = null;
const defaultColyseusUrl = (window.location.protocol === 'https:' ? 'wss://' : 'ws://') + window.location.host + '/colyseus';
const colyseusUrl = import.meta.env.VITE_COLYSEUS_URL || defaultColyseusUrl;
const colyseusClient = new Colyseus.Client(colyseusUrl);

    function joinRealtimeChannel(session) {
        if (session.mode !== 'multiplayer' || colyseusRoom) {
            return;
        }

        colyseusClient.joinOrCreate("game_room", { 
            session_id: session.id,
            nama: state.latestSession?.players?.find(p => p.id === state.myGamePlayerId)?.nama || "Player",
            color: state.latestSession?.players?.find(p => p.id === state.myGamePlayerId)?.pawn_color || "red"
        }).then(room => {
            colyseusRoom = room;
            console.log("Joined Colyseus room successfully", room.sessionId);

            room.onMessage("pawn_moved", (message) => {
                const label = REALTIME_EVENT_LABELS['pawn-moved'](resolveActorName(message.playerId));
                showToast(label);
                pushLog('info', label);
                loadState();
            });

            room.onMessage("movement_blocked", (message) => {
                const label = REALTIME_EVENT_LABELS['movement-blocked'](resolveActorName(message.playerId));
                showToast(label);
                pushLog('warning', label);
                loadState();
            });

            room.onMessage("connector_applied", (message) => {
                const label = REALTIME_EVENT_LABELS['connector-applied'](resolveActorName(message.playerId));
                showToast(label);
                pushLog('info', label);
                loadState();
            });

            room.onMessage("question_presented", (message) => {
                const label = REALTIME_EVENT_LABELS['question-presented'](resolveActorName(message.playerId));
                showToast(label);
                pushLog('info', label);
                // Trigger modal for the player whose turn it is
                if (message.playerId === state.myGamePlayerId) {
                    // This logic would normally open the question modal
                    // We need to fetch the question via API or directly from the message if provided
                }
                loadState();
            });

            room.onMessage("answered", (message) => {
                const label = REALTIME_EVENT_LABELS['score-updated'](resolveActorName(message.playerId));
                showToast(label);
                pushLog('info', label);
                loadState();
            });

            room.onMessage("error", (msg) => {
                showToast(msg);
                pushLog('error', msg);
            });

        }).catch(e => {
            console.error("Colyseus JOIN ERROR", e);
        });
    }

    function leaveRealtimeChannel(session) {
        if (colyseusRoom) {
            colyseusRoom.leave();
            colyseusRoom = null;
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
