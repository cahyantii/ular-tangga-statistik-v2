{{--
    Log Permainan — dibangun live oleh game-play.js dari respons nyata setiap
    aksi (lempar dadu, jawaban, giliran robot, event realtime lawan). Ini BUKAN
    riwayat GameLog tabel database (tabel itu murni audit trail server, tidak
    diekspos endpoint manapun ke frontend) — melainkan rangkuman kejadian
    sesi berjalan saat ini, disusun dari data respons endpoint game.roll /
    game.answer / broadcast Reverb yang sudah ada, jadi tetap 100% berasal
    dari aksi & data nyata (tidak ada baris log yang dikarang).
--}}
<div class="animate-fade-in-up rounded-2xl bg-white dark:bg-dark-surface p-4 shadow-sm sm:p-5">
    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-dark-text">
        <x-player.icon name="clock" class="h-4 w-4 text-primary-500" />
        Log Permainan
    </h3>

    <ul id="game-log-list" class="mt-3 max-h-64 space-y-2 overflow-y-auto scrollbar-none pr-1 text-xs">
        <li class="text-slate-400 dark:text-dark-muted" id="game-log-empty">Belum ada aktivitas.</li>
    </ul>
</div>
