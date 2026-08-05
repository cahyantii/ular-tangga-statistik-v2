import { state, dom, config } from './state.js';
import { stackIndexAt, placePawnAt, getOrCreatePawnEl, removePawnEl } from './pawn.js';
import { delay, spawnParticles, animateAlongConnector } from './utils.js';
import { renderFinished, renderPlayerPanels, updateTurnIndicator, renderPaused, renderInventoryUI } from './panel.js';
import { showQuestion, hideQuestion } from './modalSoal.js';
import { showDuel, hideDuel } from './modalDuel.js';
import { queueAchievementUnlocks } from './achievement.js';
import { playSound } from './audio.js';
import { joinRealtimeChannel, startHeartbeat, stopRealtimeIfSessionOver } from './multiplayer.js';

    // ---------------------------------------------------------------
    // Sinkronisasi pion untuk pemain LAIN (mis. lawan multiplayer) yang
    // posisinya berubah lewat sinyal realtime, bukan aksi kita sendiri —
    // kita tidak punya rincian nilai dadu lawan (payload sengaja minim),
    // jadi cukup animasikan selisih posisi lama -> baru secara wajar.
    // ---------------------------------------------------------------
    export async function animateExternalDiff(session) {
        for (const p of session.players) {
            if (p.status === 'forfeited' || p.status === 'left') continue;

            const el = getOrCreatePawnEl(p);
            const prev = state.knownPositions.get(p.id);

            if (prev === undefined) {
                placePawnAt(el, p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
                continue;
            }

            if (prev === p.posisi_pion) {
                continue;
            }

            if (p.posisi_pion > prev && p.posisi_pion - prev <= 6) {
                // eslint-disable-next-line no-await-in-loop
                for (let step = prev + 1; step <= p.posisi_pion; step++) {
                    const stackIndex = step === p.posisi_pion ? stackIndexAt(step, p.id, session.players) : 0;
                    placePawnAt(el, step, stackIndex);
                    playSound('pion_walk');
                    // eslint-disable-next-line no-await-in-loop
                    await delay(180);
                }
            } else {
                // Lawan/robot melompat konektor lewat sinyal realtime (kita tidak
                // tahu nilai dadu mereka) — cari konektor asli yang berakhir tepat
                // di posisi baru supaya jalurnya tetap mengikuti kurva sesungguhnya,
                // bukan cuma tebakan arah naik/turun.
                const naik = p.posisi_pion > prev;
                const konektor = Array.from(config.konektorByStart.values())
                    .find((k) => k.posisi_akhir === p.posisi_pion && (naik ? k.jenis === 'tangga' : k.jenis === 'ular'));

                await delay(100);

                if (konektor) {
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
                        dom.boardEl.classList.add('board-shake');
                        await animateAlongConnector(el, konektor.posisi_awal, p.posisi_pion, 'ular', 900);
                        spawnParticles(el, 'dust', 8);
                        setTimeout(() => dom.boardEl.classList.remove('board-shake'), 400);
                    }
                } else {
                    el.classList.add(naik ? 'pawn-climb' : 'pawn-slide');
                    await delay(450);
                    el.classList.remove('pawn-climb', 'pawn-slide');
                }

                placePawnAt(el, p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
            }
        }
    }

    export async function animateWhirlwind(triggerPlayerId) {
        const promises = [];
        for (const p of state.latestSession.players) {
            if (p.id === triggerPlayerId) continue;
            const prev = state.knownPositions.get(p.id);
            if (prev === undefined) continue;
            
            const newPos = Math.max(1, prev - 3);
            if (prev > newPos) {
                const el = getOrCreatePawnEl(p);
                el.classList.add('pawn-slide');
                promises.push(delay(450).then(() => {
                    el.classList.remove('pawn-slide');
                    placePawnAt(el, newPos, stackIndexAt(newPos, p.id, state.latestSession.players));
                    state.knownPositions.set(p.id, newPos);
                }));
            }
        }
        if (promises.length > 0) {
            await Promise.all(promises);
            await delay(200);
        }
    }

    export function syncKnownPositions(session) {
        session.players.forEach((p) => state.knownPositions.set(p.id, p.posisi_pion));
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
    export function finalizeOutcome(session, newlyUnlockedAchievements = []) {
        renderFinished(session);
        if (session.status === 'finished') {
            playSound('win_match');
        }

        if (session.active_question) {
            showQuestion(session.active_question, session.active_question_remaining_seconds);
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

    export function applySessionState(session, { pawnMode = 'diff', skipPawnIds = new Set(), deferOutcome = false } = {}) {
        state.latestSession = session;

        if (pawnMode === 'instant') {
            session.players.forEach((p) => {
                if (p.status === 'forfeited' || p.status === 'left') removePawnEl(p);
                else placePawnAt(getOrCreatePawnEl(p), p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
            });
            syncKnownPositions(session);
        } else if (pawnMode === 'skip') {
            session.players.forEach((p) => {
                if (p.status === 'forfeited' || p.status === 'left') removePawnEl(p);
                else if (!skipPawnIds.has(p.id)) {
                    placePawnAt(getOrCreatePawnEl(p), p.posisi_pion, stackIndexAt(p.posisi_pion, p.id, session.players));
                }
            });
            syncKnownPositions(session);
        } else {
            state.animationChain = state.animationChain.then(() => animateExternalDiff(session)).then(() => {
                session.players.forEach((p) => {
                    if (p.status === 'forfeited' || p.status === 'left') removePawnEl(p);
                });
                syncKnownPositions(session);
            });
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
