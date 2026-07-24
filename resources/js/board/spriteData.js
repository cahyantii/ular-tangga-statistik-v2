/**
 * Data mentah untuk sistem visual papan v6.
 *
 * Ular adalah SATU PATH SVG UTUH (kepala->leher->badan->ekor disampel dari
 * satu kurva dengan PROFIL LEBAR ANATOMI ular sungguhan — rahang lebih
 * lebar dari leher, leher menyempit sebelum badan — lihat SnakeRenderer)
 * BUKAN gambar yang ditempel. Wajah (mata/mulut/lidah) digambar sebagai
 * bentuk SVG, dihitung dari geometri path yang sama supaya presisi & tidak
 * pernah desync.
 *
 * TANGGA digambar procedural lewat SVG (bukan gambar yang direntangkan).
 */

/**
 * Persis 4 varian warna ular sesuai permintaan (Hijau Emerald / Merah Ruby /
 * Ungu Royal / Hitam Biru "Dark Cobra") - masing-masing:
 * - body: gradient 3-stop (terang kepala -> gelap ekor) untuk kedalaman.
 * - belly: warna PERUT, jelas lebih terang dari body - digambar sebagai
 *   pita di sisi bawah/dalam lengkungan (lihat SnakeRenderer._buildBelly()).
 * - scale: warna rantai motif sisik di punggung.
 * - outline: garis tepi tegas (hampir hitam per tema) - siluet harus terbaca
 *   jelas dari jarak papan penuh, bukan menyatu ke latar.
 * - iris/glow: warna mata (tajam tapi ramah - bukan hitam polos) & efek
 *   glow saat pion mendarat di kepalanya.
 */
export const SNAKE_THEMES = {
    emerald: {
        body: ['#5eead4', '#10b981', '#065f46'],
        belly: '#ecfdf5',
        scale: '#047857',
        outline: '#022c22',
        iris: '#facc15',
        glow: '#22c55e',
    },
    ruby: {
        body: ['#fca5a5', '#e11d48', '#7f1d1d'],
        belly: '#fff1f2',
        scale: '#9f1239',
        outline: '#450a0a',
        iris: '#fde68a',
        glow: '#f43f5e',
    },
    royal: {
        body: ['#d8b4fe', '#9333ea', '#4c1d95'],
        belly: '#f5f3ff',
        scale: '#6b21a8',
        outline: '#2e1065',
        iris: '#fde68a',
        glow: '#a855f7',
    },
    cobra: {
        body: ['#64748b', '#1e293b', '#0f172a'],
        belly: '#e2e8f0',
        scale: '#0b1220',
        outline: '#000000',
        iris: '#38bdf8',
        glow: '#38bdf8',
    },
};

/** Warna lidah (merah tua) — satu warna universal, bukan per-tema. */
export const TONGUE_COLOR = '#7f1d1d';

/**
 * Tangga kayu premium - 3 varian nada kayu (tetap satu gaya yang sama:
 * kayu natural, bukan logam/metalik seperti versi sebelumnya). `grain`
 * dipakai untuk guratan serat kayu tipis di sepanjang rel.
 */
export const LADDER_THEMES = {
    oak: { rail: ['#d8a15c', '#9a5f2b'], grain: '#7a4620', outline: '#4a2a12', glow: '#f2c078' },
    walnut: { rail: ['#b98452', '#7a4a20'], grain: '#5c3616', outline: '#3a220e', glow: '#e0a86a' },
    honey: { rail: ['#e6b86b', '#b3792f'], grain: '#8a5a21', outline: '#54350f', glow: '#f7d18f' },
};

export const SNAKE_THEME_NAMES = Object.keys(SNAKE_THEMES);
export const LADDER_THEME_NAMES = Object.keys(LADDER_THEMES);
