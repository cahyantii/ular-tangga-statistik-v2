/**
 * Waiting Room Realtime & Polling
 *
 * Begitu lawan bergabung atau room penuh, room ini akan:
 * 1. Memperbarui daftar pemain dan status di UI secara real-time tanpa reload halaman.
 * 2. Otomatis mengalihkan (redirect) semua pemain ke dalam papan permainan ketika status 'playing'.
 *
 * Menggunakan gabungan Smart Polling (setiap 2s) + Echo Presence Channel (jika tersedia).
 */
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('room-realtime-data');
    if (!root) return;

    const roomId = root.dataset.roomId;
    const roomUrl = root.dataset.roomUrl;
    const statusUrl = root.dataset.statusUrl;

    const statusTextEl = document.getElementById('room-status-text');
    const playersListEl = document.getElementById('room-players-list');

    let isRedirecting = false;

    async function checkRoomStatus() {
        if (isRedirecting || !statusUrl) return;

        try {
            const response = await fetch(statusUrl, {
                headers: { Accept: 'application/json' }
            });

            if (!response.ok) return;

            const data = await response.json();

            // 1. Jika game sudah mulai (Playing) -> langsung redirect ke game
            if (data.status === 'playing' && data.game_url) {
                isRedirecting = true;
                if (statusTextEl) {
                    statusTextEl.textContent = 'Permainan dimulai, mengalihkan...';
                }
                window.location.href = data.game_url;
                return;
            }

            // 2. Jika room dibatalkan -> redirect ke dashboard
            if (data.status === 'cancelled') {
                isRedirecting = true;
                window.location.href = '/dashboard';
                return;
            }

            // 3. Update DOM jika masih di waiting room
            updateRoomUI(data);

        } catch (error) {
            console.error('[RoomRealtime] Gagal mengecek status room:', error);
        }
    }

    function updateRoomUI(data) {
        if (statusTextEl) {
            const sisa = data.sisa_slot;
            const statusMsg = sisa > 0
                ? `menunggu ${sisa} pemain lagi...`
                : 'penuh, memulai permainan...';
            statusTextEl.textContent = `${data.pemain_count}/${data.jumlah_pemain} pemain — ${statusMsg}`;
        }

        if (playersListEl && Array.isArray(data.players)) {
            let html = '';
            
            // Render pemain yang sudah bergabung
            data.players.forEach(p => {
                html += `
                    <div class="rounded-lg border border-slate-200 dark:border-dark-border bg-slate-50/50 dark:bg-dark-surface2 px-4 py-2 text-sm text-slate-700 dark:text-dark-text flex items-center justify-between transition-all duration-300">
                        <span>${escapeHtml(p.name)}</span>
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                `;
            });

            // Render sisa slot kosong
            for (let i = 0; i < data.sisa_slot; i++) {
                html += `
                    <div class="rounded-lg border border-dashed border-slate-200 dark:border-dark-border px-4 py-2 text-sm text-slate-400 dark:text-dark-muted">
                        Menunggu pemain...
                    </div>
                `;
            }

            playersListEl.innerHTML = html;
        }
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // 1. Polling setiap 2 detik
    checkRoomStatus();
    setInterval(checkRoomStatus, 2000);

    // 2. WebSocket Echo listener (jika terkonfigurasi)
    if (window.Echo) {
        window.Echo.join(`room.${roomId}`)
            .joining(() => {
                checkRoomStatus();
            })
            .leaving(() => {
                checkRoomStatus();
            });
    }
});
