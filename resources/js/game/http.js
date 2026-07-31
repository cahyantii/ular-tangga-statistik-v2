import { state, dom, config } from './state.js';
import { animatePlayerTurn, setTileGlow } from './pawn.js';
import { logTurnResult, logAnswerResult, pushLog } from './ui.js';
import { animateDiceRoll } from './dice.js';
import { animateExternalDiff, finalizeOutcome, applySessionState } from './syncPion.js';
import { hideQuestion, showQuestion } from './modalSoal.js';
import { hideDuel, showDuel } from './modalDuel.js';
import { updateTurnIndicator } from './panel.js';
import { queueAchievementUnlocks } from './achievement.js';
import { showToast } from './utils.js';
import { colyseusRoom } from './multiplayer.js';
import { playSound } from './audio.js';

    // ---------------------------------------------------------------
    // HTTP
    // ---------------------------------------------------------------
    async function postJson(url, body = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': config.csrfToken,
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
        dom.rollButton.disabled = true;

        const me = state.latestSession?.players.find((p) => p.id === state.myGamePlayerId);
        const fromPosisi = me ? me.posisi_pion : 1;
        // Posisi robot SEBELUM giliran ini harus diambil sebelum applySessionState
        // menimpa state.knownPositions dengan posisi akhirnya (lihat playRobotTurns).
        const robotBefore = state.latestSession?.players.find((p) => p.is_robot);
        const robotFromPosisi = robotBefore ? (state.knownPositions.get(robotBefore.id) ?? robotBefore.posisi_pion) : 0;

        // Route through Colyseus if active
        if (colyseusRoom) {
            colyseusRoom.send("roll_dice", { forced_roll: document.getElementById('forced-roll-input')?.value });
            dom.rollButton.disabled = false; // Reset manually since there's no await
            return;
        }

        try {
            const forcedRollEl = document.getElementById('forced-roll-input');
            const forcedRoll = forcedRollEl ? forcedRollEl.value : null;
            const body = forcedRoll ? { forced_roll: parseInt(forcedRoll, 10) } : {};
            const result = await postJson(config.rollUrl, body);

            await animateDiceRoll(result.raw_nilai_dadu ?? result.nilai_dadu ?? 1, result.double_dice_active ?? false);

            if (result.toast) {
                showToast(result.toast);
            }

            const actingPlayer = result.session.players.find((p) => p.id === state.myGamePlayerId) ?? me;
            if (actingPlayer) {
                logTurnResult(actingPlayer, result);
                await animatePlayerTurn(actingPlayer, fromPosisi, result);
                state.knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
                
                if (result.type === 'mystery' && result.item_id === 'whirlwind') {
                    await animateWhirlwind(actingPlayer.id);
                }
            }

            if (result.type === 'answered') {
                logAnswerResult(actingPlayer, result.benar);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const finalRobotFromPosisi = robot ? (state.knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
            const skipIds = new Set([state.myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, finalRobotFromPosisi);
            finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
        } catch (error) {
            showToast(error.message);
        } finally {
            dom.rollButton.disabled = false;
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

        let fromPosisi = startFromPosisi ?? (state.knownPositions.get(robot.id) ?? robot.posisi_pion);

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
                fromPosisi = state.knownPositions.get(robot.id) ?? robot.posisi_pion;
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
                    dom.boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => dom.boardEl.classList.remove('board-shake'), 400);
                }
                
                fromPosisi = info.posisi_akhir;
                placePawnAt(el, fromPosisi, stackIndexAt(fromPosisi, robot.id, session?.players));
                await delay(200);
            }
        }

        state.knownPositions.set(robot.id, robot.posisi_pion);
        setTileGlow(state.latestSession?.status === 'playing' ? state.latestSession.players.find((p) => p.id === state.latestSession.current_turn_game_player_id)?.posisi_pion : null);
    }

    async function submitDuelAnswer(soalId, jawaban, timeTakenMs, selectedButtonEl = null) {
        clearInterval(state.duelCountdownInterval);

        try {
            const duelAnswerUrl = config.answerUrl.replace('/answer', '/duel-answer');
            const result = await postJson(duelAnswerUrl, { soal_id: soalId, jawaban, time_taken_ms: timeTakenMs });

            if (result.type === 'duel_answered') {
                if (result.benar) {
                    playSound('correct_answer');
                } else {
                    playSound('false_answer');
                }

                dom.duelFeedbackEl.textContent = result.benar
                    ? 'Jawaban benar!'
                    : `Jawaban salah. ${result.pembahasan ?? ''}`;
                dom.duelFeedbackEl.classList.remove('hidden');

                if (selectedButtonEl) {
                    if (result.benar) {
                        selectedButtonEl.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                        selectedButtonEl.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                    } else {
                        selectedButtonEl.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                        selectedButtonEl.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                        
                        if (result.kunci_jawaban) {
                            const correctButton = Array.from(dom.duelOptionsEl.children).find(b => b.dataset.kunci && b.dataset.kunci.toUpperCase() === result.kunci_jawaban.toUpperCase());
                            if (correctButton) {
                                correctButton.classList.remove('border-slate-200', 'hover:border-rose-400', 'hover:bg-rose-50', 'text-slate-700');
                                correctButton.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                            }
                        }
                    }
                }

                Array.from(dom.duelOptionsEl.children).forEach(b => {
                    b.disabled = true;
                    b.classList.remove('hover:-translate-y-0.5');
                });

                await delay(1800);
                
                // Refresh state to show next question or wait screen
                applySessionState(result.session);
            } else if (result.type === 'duel_finished') {
                dom.duelFeedbackEl.textContent = 'Semua soal telah dijawab. Memproses hasil duel...';
                dom.duelFeedbackEl.classList.remove('hidden');

                await delay(1500);
                hideDuel();

                const duel = result.duel;
                const loser = result.session.players.find(p => p.id === duel.loser_id);
                const winner = result.session.players.find(p => p.id === duel.winner_id);

                if (winner && loser) {
                    const winnerNameEl = document.getElementById('duel-result-winner-name');
                    const loserNameEl = document.getElementById('duel-result-loser-name');
                    const penaltyEl = document.getElementById('duel-result-penalty');
                    const messageEl = document.getElementById('duel-result-message');
                    
                    if(winnerNameEl) winnerNameEl.textContent = winner.nama || (winner.is_robot ? 'Robot' : 'Pemain');
                    if(loserNameEl) loserNameEl.textContent = loser.nama || (loser.is_robot ? 'Robot' : 'Pemain');
                    if(penaltyEl) penaltyEl.textContent = `Mundur ${duel.loser_penalty_roll} Langkah`;
                    if(messageEl) messageEl.textContent = `${winner.nama || (winner.is_robot ? 'Robot' : 'Pemain')} memenangkan duel!`;
                    
                    window.dispatchEvent(new CustomEvent('open-modal', {detail: 'duel-result-modal'}));
                    
                    await delay(4000);
                    window.dispatchEvent(new CustomEvent('close-modal', {detail: 'duel-result-modal'}));
                    await delay(300);
                }

                if (loser) {
                    showToast(`Duel selesai! ${winner?.nama || 'Pemain'} menang. ${loser.nama || 'Pemain'} terlempar mundur ${duel.loser_penalty_roll} langkah.`);
                    await animateDiceRoll(duel.loser_penalty_roll);
                    const loserFromPosisi = state.knownPositions.get(loser.id) ?? 1;
                    await animatePlayerTurn(loser, loserFromPosisi, { type: 'normal', nilai_dadu: -duel.loser_penalty_roll });
                    state.knownPositions.set(loser.id, loser.posisi_pion);
                }

                // Capture robotFromPosisi AFTER loser animation so if bot was loser,
                // its knownPosition is already updated to the post-penalty position.
                const robot = result.session.players.find((p) => p.is_robot);
                const robotFromPosisi = robot ? (state.knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
                const skipIds = new Set([state.myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
                
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
        const robotBefore = state.latestSession?.players.find((p) => p.is_robot);
        const robotFromPosisi = robotBefore ? (state.knownPositions.get(robotBefore.id) ?? robotBefore.posisi_pion) : 0;
        const meBefore = state.latestSession?.players.find((p) => p.id === state.myGamePlayerId);
        const myFromPosisi = meBefore ? meBefore.posisi_pion : 1;

        // Route through Colyseus if active
        if (colyseusRoom) {
            colyseusRoom.send("answer_question", { soal_id: soalId, jawaban });
            hideQuestion();
            return;
        }

        try {
            const result = await postJson(config.answerUrl, { soal_id: soalId, jawaban });

            if (result.benar) {
                playSound('correct_answer');
            } else {
                playSound('false_answer');
            }

            dom.questionFeedbackEl.textContent = result.benar
                ? 'Jawaban benar!'
                : `Jawaban salah. ${result.pembahasan ?? ''}`;
            dom.questionFeedbackEl.classList.remove('hidden');

            if (selectedButtonEl) {
                if (result.benar) {
                    selectedButtonEl.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                    selectedButtonEl.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                } else {
                    selectedButtonEl.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                    selectedButtonEl.classList.add('border-red-500', 'bg-red-50', 'text-red-700');
                    
                    if (result.kunci_jawaban) {
                        const correctButton = Array.from(dom.questionOptionsEl.children).find(b => b.dataset.kunci && b.dataset.kunci.toUpperCase() === result.kunci_jawaban.toUpperCase());
                        if (correctButton) {
                            correctButton.classList.remove('border-slate-200', 'hover:border-primary-400', 'hover:bg-primary-50', 'text-slate-700');
                            correctButton.classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                        }
                    }
                }
            }

            Array.from(dom.questionOptionsEl.children).forEach(b => {
                b.disabled = true;
                b.classList.remove('hover:-translate-y-0.5');
            });

            const actingPlayer = result.session.players.find((p) => p.id === state.myGamePlayerId);
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
                    dom.boardEl.classList.add('board-shake');
                    await animateAlongConnector(el, info.posisi_awal, info.posisi_akhir, 'ular', 900);
                    spawnParticles(el, 'dust', 8);
                    setTimeout(() => dom.boardEl.classList.remove('board-shake'), 400);
                }

                placePawnAt(el, info.posisi_akhir, stackIndexAt(info.posisi_akhir, actingPlayer.id, result.session?.players));
                await delay(200);

                // Cek apakah mendarat di mystery tile setelah konektor
                if (result.mystery_after_konektor && actingPlayer.id === state.myGamePlayerId) {
                    // Inject session ke objek mystery agar showGachaModal bisa cek inventory
                    const mysteryWithSession = { ...result.mystery_after_konektor, session: result.session };
                    await showGachaModal(mysteryWithSession);
                    if (result.mystery_after_konektor.item_id === 'whirlwind') {
                        await animateWhirlwind(actingPlayer.id);
                    }
                }
            } else if (actingPlayer && actingPlayer.posisi_pion !== myFromPosisi) {
                await animatePlayerTurn(actingPlayer, myFromPosisi, { type: 'answered', nilai_dadu: actingPlayer.posisi_pion - myFromPosisi });
            }
            if (actingPlayer) {
                state.knownPositions.set(actingPlayer.id, actingPlayer.posisi_pion);
            }

            const robot = result.session.players.find((p) => p.is_robot);
            const finalRobotFromPosisi = robot ? (state.knownPositions.get(robot.id) ?? robot.posisi_pion) : 0;
            const skipIds = new Set([state.myGamePlayerId, robot?.id].filter((id) => id !== undefined && id !== null));
            applySessionState(result.session, { pawnMode: 'skip', skipPawnIds: skipIds, deferOutcome: true });

            await playRobotTurns(result.robot_turns, result.session, finalRobotFromPosisi);
            finalizeOutcome(result.session, result.newly_unlocked_achievements ?? []);
        } catch (error) {
            showToast(error.message);
            hideQuestion();
        }
    }

    async function loadState() {
        const response = await fetch(config.stateUrl, { headers: { Accept: 'application/json' } });
        const session = await response.json();
        applySessionState(session);
    }

    async function loadInitialState() {
        initDiceSize();

        const response = await fetch(config.stateUrl, { headers: { Accept: 'application/json' } });
        const session = await response.json();

        const me = session.players.find((p) => p.user_id === config.currentUserId);
        state.myGamePlayerId = me ? me.id : null;

        applySessionState(session, { pawnMode: 'instant' });
    }
export function initEventListeners() {
    
        dom.rollButton?.addEventListener('click', rollDice);
    
        // Tambahan untuk fullscreen: buat area dadu bisa diklik langsung
        if (dom.diceGlowWrap && dom.rollButton) {
            dom.diceGlowWrap.addEventListener('click', () => {
                if (!dom.rollButton.disabled) dom.rollButton.click();
            });
            dom.diceGlowWrap.title = 'Klik untuk melempar dadu';
            
            const syncDiceGlowCursor = () => {
                dom.diceGlowWrap.style.cursor = dom.rollButton.disabled ? 'not-allowed' : 'pointer';
            };
            new MutationObserver(syncDiceGlowCursor).observe(dom.rollButton, { attributes: true, attributeFilter: ['disabled'] });
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
    
            if (diceBtn && dom.rollButton) {
                const syncDice = () => { diceBtn.disabled = dom.rollButton.disabled; };
                new MutationObserver(syncDice).observe(dom.rollButton, { attributes: true, attributeFilter: ['disabled'] });
                diceBtn.addEventListener('click', () => dom.rollButton.click());
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
            const isMultiplayer = state.latestSession?.mode === 'multiplayer';
            // Pesan digeneralisasi untuk game 2-6 pemain: kalau cuma tersisa 1
            // pemain aktif lain, dia otomatis menang (WO) - kalau masih ada 2+,
            // permainan lanjut tanpamu (lihat GameSessionService::leave()).
            const confirmMessage = isMultiplayer
                ? 'Yakin ingin keluar? Anda akan dinyatakan kalah WO. Kalau masih ada pemain lain, permainan lanjut tanpa Anda; kalau tersisa satu, dia otomatis menang.'
                : 'Yakin ingin keluar dari permainan ini? Permainan akan dihentikan.';
    
            if (!confirm(confirmMessage)) {
                return;
            }
    
            state.leaveConfirmed = true;
    
            try {
                await postJson(config.leaveUrl);
            } finally {
                window.location.href = '/dashboard';
            }
        });
    
        window.addEventListener('beforeunload', (event) => {
            if (!state.leaveConfirmed && document.getElementById('game-in-progress')) {
                event.preventDefault();
                event.returnValue = '';
            }
        });
    
}