import Alpine from 'alpinejs';

/**
 * Bell Notification (Tahap 19): satu Alpine.store dipakai bersama oleh bell
 * admin (layouts/admin.blade.php) dan bell player (components/player/topbar.blade.php)
 * - keduanya hanya membaca $store.notifications, tidak ada logic terpisah.
 * Realtime lewat window.Echo (private channel bawaan Notifiable,
 * App.Models.User.{id}, sudah diinisialisasi di resources/js/echo.js dan
 * diotorisasi di routes/channels.php) - fallback tanpa Echo tetap berfungsi
 * lewat fetchRecent() saat halaman dimuat, hanya tanpa update realtime.
 */
function playChime() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1320, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.35);
    } catch (e) {
        // Web Audio tidak tersedia (mis. browser lama) - abaikan, notifikasi
        // visual (badge + shake) tetap berfungsi tanpa suara.
    }
}

function shakeBells() {
    document.querySelectorAll('.js-notification-bell').forEach((el) => {
        el.classList.remove('notif-bell-shake');
        // reflow supaya animasi bisa diulang walau class-nya sama seperti sebelumnya
        void el.offsetWidth;
        el.classList.add('notif-bell-shake');
    });
}

Alpine.store('notifications', {
    items: [],
    unreadCount: 0,
    loading: true,

    /**
     * Kloning SVG asli dari #notification-icon-templates (lihat
     * components/player/notification-icon-templates.blade.php) - dipanggil
     * lewat x-init per baris dropdown supaya icon identik dengan <x-player.icon>
     * yang dipakai di seluruh aplikasi, bukan re-implementasi terpisah di JS.
     */
    mountIcon(el, iconName) {
        if (!el) {
            return;
        }
        const template = document.querySelector(`#notification-icon-templates template[data-icon="${iconName}"]`)
            ?? document.querySelector('#notification-icon-templates template[data-icon="bell"]');
        el.innerHTML = '';
        if (template) {
            el.appendChild(template.content.cloneNode(true));
        }
    },

    init() {
        // Guest/auth pages (login, register) juga memuat app.js untuk Alpine
        // lain di halamannya, tapi tidak mendeklarasikan meta[name="user-id"]
        // (hanya admin/player layout yang punya) - jangan fetch/listen di sana.
        if (!document.querySelector('meta[name="user-id"]')?.content) {
            this.loading = false;
            return;
        }

        this.fetchRecent();
        this.listenRealtime();
    },

    fetchRecent() {
        this.loading = true;
        fetch('/notifications/recent', { headers: { Accept: 'application/json' } })
            .then((res) => (res.ok ? res.json() : { unread_count: 0, notifications: [] }))
            .then((data) => {
                this.items = data.notifications ?? [];
                this.unreadCount = data.unread_count ?? 0;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
    },

    listenRealtime() {
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId || typeof window.Echo === 'undefined') {
            return;
        }

        window.Echo.private(`App.Models.User.${userId}`).notification((notification) => {
            this.items.unshift({
                id: notification.id ?? String(Date.now()),
                title: notification.title ?? '',
                message: notification.message ?? '',
                icon: notification.icon ?? 'bell',
                color: notification.color ?? 'blue',
                category: notification.category ?? 'system',
                url: notification.url ?? null,
                read_at: null,
                created_at: new Date().toISOString(),
                created_at_human: 'Baru saja',
            });
            this.items = this.items.slice(0, 10);
            this.unreadCount += 1;

            shakeBells();
            playChime();
        });
    },

    /**
     * markRead/markAllRead/remove semuanya optimistic (ubah UI dulu, baru
     * kirim ke server) - SEBELUMNYA fetch() di sini benar-benar "lempar lalu
     * lupa" (tidak ada .then()/.catch() sama sekali), jadi kalau requestnya
     * gagal (koneksi putus, token CSRF kedaluwarsa/419, error 500), UI tetap
     * terlihat berhasil padahal server tidak pernah mencatatnya - baru
     * ketahuan salah setelah reload halaman. Sekarang tiap perubahan
     * optimistic itu DIBATALKAN LAGI (state balik seperti semula) kalau
     * requestnya gagal, supaya tampilan selalu jujur mencerminkan keadaan
     * sebenarnya di server.
     */
    markRead(id) {
        const item = this.items.find((n) => n.id === id);
        if (!item || item.read_at) {
            return;
        }
        item.read_at = new Date().toISOString();
        this.unreadCount = Math.max(0, this.unreadCount - 1);

        fetch(`/notifications/${id}/read`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
            })
            .catch((err) => {
                console.error('Gagal menandai notifikasi sebagai dibaca, dikembalikan ke status semula:', err);
                item.read_at = null;
                this.unreadCount += 1;
            });
    },

    markAllRead() {
        const previous = this.items.map((n) => ({ n, read_at: n.read_at }));
        const previousUnreadCount = this.unreadCount;

        this.items.forEach((n) => {
            n.read_at = n.read_at ?? new Date().toISOString();
        });
        this.unreadCount = 0;

        fetch('/notifications/read-all', {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
            })
            .catch((err) => {
                console.error('Gagal menandai semua notifikasi sebagai dibaca, dikembalikan ke status semula:', err);
                previous.forEach(({ n, read_at }) => { n.read_at = read_at; });
                this.unreadCount = previousUnreadCount;
            });
    },

    remove(id) {
        const index = this.items.findIndex((n) => n.id === id);
        if (index === -1) {
            return;
        }
        const [removed] = this.items.splice(index, 1);
        const wasUnread = !removed.read_at;
        if (wasUnread) {
            this.unreadCount = Math.max(0, this.unreadCount - 1);
        }

        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
            })
            .catch((err) => {
                console.error('Gagal menghapus notifikasi, dikembalikan ke daftar:', err);
                this.items.splice(index, 0, removed);
                if (wasUnread) {
                    this.unreadCount += 1;
                }
            });
    },
});
