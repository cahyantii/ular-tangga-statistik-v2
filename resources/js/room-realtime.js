/**
 * Waiting Room (Tahap 4/12c, keputusan final): begitu lawan bergabung, room
 * ini harus otomatis berpindah ke papan permainan TANPA pemain pertama perlu
 * menekan "Segarkan Status" manual. Kita TIDAK menebak status permainan di
 * klien — presence channel di sini hanya dipakai sebagai sinyal "ada member
 * lain baru bergabung", lalu klien menavigasi ulang ke URL room yang sama;
 * MatchmakingController::showRoom() sendiri yang memutuskan (server-authoritative)
 * apakah sudah waktunya redirect ke game.show.
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('room-realtime-data');
    if (!root || !window.Echo) {
        return;
    }

    const roomId = root.dataset.roomId;
    const roomUrl = root.dataset.roomUrl;

    window.Echo.join(`room.${roomId}`).joining(() => {
        window.location.href = roomUrl;
    });
});
