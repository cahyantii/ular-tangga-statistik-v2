import Alpine from 'alpinejs';

/**
 * Theme stores — mode gelap/terang terpisah untuk area Player dan Admin.
 *
 * Dua store independen sengaja dibuat supaya preferensi admin dan player
 * tidak saling menimpa: admin bisa pakai mode gelap sementara player pakai
 * mode terang di browser yang sama (mis. dua tab berbeda).
 *
 * Penyimpanan:
 *   - Player : localStorage key  "player-theme"  → nilai "dark" | "light"
 *   - Admin  : localStorage key  "admin-theme"   → nilai "dark" | "light"
 *
 * Class yang diterapkan ke <html>:
 *   - Player dark  → tambahkan class  "dark"
 *   - Admin dark   → tambahkan class  "dark-admin"
 *
 * Masing-masing store saling tidak tahu satu sama lain; keduanya hanya
 * membaca/menulis key localStorage-nya sendiri dan class HTML-nya sendiri.
 */

/* ------------------------------------------------------------------ */
/* Helper: baca preferensi awal sebelum Alpine mount (mencegah flash)  */
/* ------------------------------------------------------------------ */
function applyStoredTheme(storageKey, htmlClass) {
    const stored = localStorage.getItem(storageKey);
    if (stored === 'dark') {
        document.documentElement.classList.add(htmlClass);
    } else {
        document.documentElement.classList.remove(htmlClass);
    }
}

// Terapkan SECEPATNYA (sebelum render pertama) untuk menghindari FOUC
// (Flash of Unstyled Content) — dipanggil langsung saat modul di-import.
applyStoredTheme('player-theme', 'dark');
applyStoredTheme('admin-theme', 'dark-admin');

/* ------------------------------------------------------------------ */
/* Alpine store: playerTheme                                           */
/* ------------------------------------------------------------------ */
Alpine.store('playerTheme', {
    /** true = mode gelap aktif untuk area player */
    isDark: localStorage.getItem('player-theme') === 'dark',

    /** Aktifkan mode gelap player */
    enableDark() {
        this.isDark = true;
        localStorage.setItem('player-theme', 'dark');
        document.documentElement.classList.add('dark');
    },

    /** Aktifkan mode terang player */
    enableLight() {
        this.isDark = false;
        localStorage.setItem('player-theme', 'light');
        document.documentElement.classList.remove('dark');
    },

    /** Balik mode saat ini (untuk tombol toggle) */
    toggle() {
        this.isDark ? this.enableLight() : this.enableDark();
    },
});

/* ------------------------------------------------------------------ */
/* Alpine store: adminTheme                                            */
/* ------------------------------------------------------------------ */
Alpine.store('adminTheme', {
    /** true = mode gelap aktif untuk area admin */
    isDark: localStorage.getItem('admin-theme') === 'dark',

    /** Aktifkan mode gelap admin */
    enableDark() {
        this.isDark = true;
        localStorage.setItem('admin-theme', 'dark');
        document.documentElement.classList.add('dark-admin');
    },

    /** Aktifkan mode terang admin */
    enableLight() {
        this.isDark = false;
        localStorage.setItem('admin-theme', 'light');
        document.documentElement.classList.remove('dark-admin');
    },

    /** Balik mode saat ini (untuk tombol toggle) */
    toggle() {
        this.isDark ? this.enableLight() : this.enableDark();
    },
});
