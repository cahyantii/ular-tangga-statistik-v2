/**
 * Dark Mode Manager
 * ─────────────────
 * Strategi:
 *  1. Cek localStorage → pakai preferensi user yang tersimpan
 *  2. Fallback: deteksi preferensi OS (prefers-color-scheme: dark)
 *  3. Apply class `dark` ke <html> sebelum render (anti-flash)
 *  4. Ekspos window.darkMode untuk dipakai Alpine.js component
 */

(function () {
    const STORAGE_KEY = 'theme';

    /**
     * Ambil tema saat ini:
     *   - 'dark' | 'light' dari localStorage
     *   - null jika belum pernah dipilih (gunakan OS preference)
     */
    function getStoredTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch {
            return null;
        }
    }

    /**
     * Simpan pilihan tema ke localStorage
     */
    function saveTheme(theme) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch {
            // Diam-diam gagal (misal: private mode dengan storage penuh)
        }
    }

    /**
     * Cek apakah OS user prefer dark
     */
    function systemPrefersDark() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /**
     * Terapkan tema ke <html>
     */
    function applyTheme(isDark) {
        const html = document.documentElement;
        if (isDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }

    /**
     * Tentukan apakah dark mode aktif berdasarkan urutan prioritas
     */
    function resolveIsDark() {
        const stored = getStoredTheme();
        if (stored === 'dark') return true;
        if (stored === 'light') return false;
        // Belum ada pilihan manual → ikuti OS
        return systemPrefersDark();
    }

    // ─── Apply segera (sebelum DOM render) untuk menghindari flash ───
    const isDark = resolveIsDark();
    applyTheme(isDark);

    // ─── Ekspos API global untuk Alpine.js / komponen lain ───
    window.darkMode = {
        /**
         * Cek apakah dark mode sedang aktif
         */
        isDark() {
            return document.documentElement.classList.contains('dark');
        },

        /**
         * Toggle antara dark / light
         */
        toggle() {
            const currentlyDark = this.isDark();
            applyTheme(!currentlyDark);
            saveTheme(!currentlyDark ? 'dark' : 'light');
        },

        /**
         * Set tema secara eksplisit: 'dark' | 'light' | 'system'
         */
        set(theme) {
            if (theme === 'system') {
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch {}
                applyTheme(systemPrefersDark());
            } else {
                saveTheme(theme);
                applyTheme(theme === 'dark');
            }
        },
    };

    // ─── Dengarkan perubahan preferensi OS (hanya jika user belum memilih) ───
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!getStoredTheme()) {
            applyTheme(e.matches);
        }
    });
})();
