import { state, dom, config } from './state.js';
import { submitDuelAnswer } from './http.js';

    // ---------------------------------------------------------------
    // Modal Duel
    // ---------------------------------------------------------------
    function startDuelCountdown(duration, soalId, startedAt) {
        clearInterval(state.duelCountdownInterval);

        const update = () => {
            const elapsed = Date.now() - startedAt;
            const remaining = Math.max(0, Math.floor((duration - elapsed) / 1000));
            dom.duelTimerEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(state.duelCountdownInterval);
                submitDuelAnswer(soalId, null, duration);
            }
        };

        update();
        state.duelCountdownInterval = setInterval(update, 1000);
    }

    let duelStartTime = null;

    let duelOpponentSimTimeout = null;
    let duelBotCurrentDuelId = null;
    let duelBotSimulatedIndex = 0;
    let duelBotIsThinking = false;

    function updateDuelOpponentView(duel) {
        const opponentId = duel.challenger_id === state.myGamePlayerId ? duel.opponent_id : duel.challenger_id;
        const opponent = state.latestSession?.players.find(p => p.id === opponentId);
        
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
        if (!state.myGamePlayerId || (duel.challenger_id !== state.myGamePlayerId && duel.opponent_id !== state.myGamePlayerId)) {
            // Not part of duel
            return;
        }

        updateDuelOpponentView(duel);

        // Find the first question we haven't answered yet
        const myAnswers = duel.answers.filter(a => a.game_player_id === state.myGamePlayerId);
        const answeredSoalIds = new Set(myAnswers.map(a => a.soal_id));
        
        const nextQuestion = duel.questions.find(q => !answeredSoalIds.has(q.soal.id));

        if (!nextQuestion) {
            // Waiting for opponent
            const dom.duelStatusTextEl = document.getElementById('duel-status-text');
            const dom.duelTextEl = document.getElementById('duel-text');
            const dom.duelOptionsEl = document.getElementById('duel-options');
            const dom.duelTimerEl = document.getElementById('duel-timer');
            if (dom.duelStatusTextEl) dom.duelStatusTextEl.textContent = 'Menunggu lawan selesai...';
            if (dom.duelTextEl) dom.duelTextEl.textContent = 'Anda telah menjawab semua soal. Harap tunggu.';
            if (dom.duelOptionsEl) dom.duelOptionsEl.innerHTML = '';
            if (dom.duelTimerEl) dom.duelTimerEl.textContent = '';
            clearInterval(state.duelCountdownInterval);
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'duel-modal' }));
            return;
        }

        const soal = nextQuestion.soal;
        const dom.duelStatusTextEl = document.getElementById('duel-status-text');
        const dom.duelFeedbackEl = document.getElementById('duel-feedback');
        const dom.duelTextEl = document.getElementById('duel-text');
        const dom.duelOptionsEl = document.getElementById('duel-options');
        
        if (dom.duelStatusTextEl) dom.duelStatusTextEl.textContent = `Pertanyaan ${nextQuestion.order} dari 3`;
        if (dom.duelFeedbackEl) dom.duelFeedbackEl.classList.add('hidden');
        if (dom.duelTextEl) dom.duelTextEl.textContent = soal.pertanyaan;
        if (dom.duelOptionsEl) dom.duelOptionsEl.innerHTML = '';

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
            if (dom.duelOptionsEl) dom.duelOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'duel-modal' }));
        startDuelCountdown(20000, soal.id, duelStartTime); // 20s
    }

    function hideDuel() {
        clearInterval(state.duelCountdownInterval);
        if (duelOpponentSimTimeout) clearTimeout(duelOpponentSimTimeout);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'duel-modal' }));
    }
