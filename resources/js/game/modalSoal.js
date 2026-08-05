import { state, dom, config } from './state.js';
import { submitAnswer } from './http.js';

    // ---------------------------------------------------------------
    // Modal Soal
    // ---------------------------------------------------------------
    function startQuestionCountdown(remainingSeconds, soalId) {
        clearInterval(state.questionCountdownInterval);

        let remaining = remainingSeconds;
        const update = () => {
            dom.questionTimerEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(state.questionCountdownInterval);
                submitAnswer(soalId, null);
            }
            remaining--;
        };

        update();
        state.questionCountdownInterval = setInterval(update, 1000);
    }

    function showQuestion(soal, remainingSeconds) {
        dom.questionFeedbackEl.classList.add('hidden');
        dom.questionTextEl.textContent = soal.pertanyaan;
        dom.questionOptionsEl.innerHTML = '';

        Object.entries(soal.opsi_jawaban).forEach(([kunci, teks]) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full rounded-xl border border-slate-200 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition-all duration-150 hover:-translate-y-0.5 hover:border-primary-400 hover:bg-primary-50';
            button.innerHTML = `<span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">${kunci}</span>${teks}`;
            button.dataset.kunci = kunci;
            button.addEventListener('click', () => submitAnswer(soal.id, kunci, button));
            dom.questionOptionsEl.appendChild(button);
        });

        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'question-modal' }));
        startQuestionCountdown(remainingSeconds, soal.id);
    }

    function hideQuestion() {
        clearInterval(state.questionCountdownInterval);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: 'question-modal' }));
    }
