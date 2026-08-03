import { state, dom, config } from './state.js';

    // ---------------------------------------------------------------
    // Log Permainan — dibangun dari respons aksi nyata (bukan data karangan),
    // lihat catatan di resources/views/components/game/log-card.blade.php
    // ---------------------------------------------------------------
    const LOG_ICONS = {
        dice:    { icon: '\u{1F3B2}', color: 'text-primary-600' },
        correct: { icon: '✅',        color: 'text-secondary-600' },
        wrong:   { icon: '❌',        color: 'text-rose-600' },
        ladder:  { icon: '\u{1FA9C}', color: 'text-secondary-600' },
        snake:   { icon: '\u{1F40D}', color: 'text-rose-600' },
        mystery: { icon: '❓',        color: 'text-violet-600' },
        info:    { icon: '\u{1F514}', color: 'text-slate-500' },
        finish:  { icon: '\u{1F3C6}', color: 'text-accent-600' },
    };

    function pushLog(kind, message) {
        const meta = LOG_ICONS[kind] ?? LOG_ICONS.info;
        dom.logEmptyEl?.remove();

        const li = document.createElement('li');
        li.className = 'flex items-start gap-2 rounded-lg px-2 py-1.5 animate-fade-in-up';
        li.innerHTML = `
            <span class="shrink-0">${meta.icon}</span>
            <span class="font-medium ${meta.color}">${message}</span>
        `;
        dom.logListEl.insertBefore(li, dom.logListEl.firstChild);

        while (dom.logListEl.children.length > 30) {
            dom.logListEl.removeChild(dom.logListEl.lastChild);
        }
    }

    function playerLabel(player) {
        if (!player) return '?';
        if (player.is_robot) return 'Robot';
        return (!player.is_robot && state.myGamePlayerId === player.id) ? 'Anda' : player.nama;
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
