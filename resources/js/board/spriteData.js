/**
 * Data mentah untuk sistem visual papan v8.
 *
 * Ular = SATU SILUET MULUS (dua potongan path yang bertemu presisi di titik
 * leher, lihat SnakeRenderer) - BUKAN rantai ellipse/ruas terpisah (versi
 * "chunky bersegmen" sebelumnya terbaca sebagai cacing/lipan). `head` &
 * `body` sengaja DUA WARNA BEDA (bukan gradient/array) supaya kepala tetap
 * terbaca tegas lewat batas warna, bukan bentuk terpisah. `pattern` = warna
 * totol statis di badan (pengganti pita berselang-seling).
 *
 * Tangga tetap procedural SVG (2 rel + rung) - lihat LadderRenderer.
 */
export const SNAKE_THEMES = {
    emerald: {
        head: '#6ee7b7',
        body: '#059669',
        belly: '#ecfdf5',
        pattern: '#047857',
        outline: '#064e3b',
        iris: '#1e293b',
        glow: '#22c55e',
    },
    ruby: {
        head: '#fda4af',
        body: '#e11d48',
        belly: '#fff1f2',
        pattern: '#9f1239',
        outline: '#7f1d1d',
        iris: '#1e293b',
        glow: '#f43f5e',
    },
    royal: {
        head: '#d8b4fe',
        body: '#9333ea',
        belly: '#f5f3ff',
        pattern: '#6b21a8',
        outline: '#4c1d95',
        iris: '#1e293b',
        glow: '#a855f7',
    },
    sunshine: {
        head: '#fde68a',
        body: '#ea580c',
        belly: '#fffbeb',
        pattern: '#c2410c',
        outline: '#7c2d12',
        iris: '#1e293b',
        glow: '#fb923c',
    },
};

/** Warna lidah (merah tua) — satu warna universal, bukan per-tema. */
export const TONGUE_COLOR = '#7f1d1d';

/**
 * Tema tangga - rel kayu, tebal & jelas. `rail` = [warna terang, warna
 * gelap] untuk gradient rel. `outline` = garis tepi tegas.
 */
export const LADDER_THEMES = {
    oak: { rail: ['#f3bd76', '#c1782f'], outline: '#5c3417', glow: '#f2c078' },
    cherry: { rail: ['#f38b8b', '#c1442f'], outline: '#4a140d', glow: '#f7a08f' },
    honey: { rail: ['#fdd577', '#e0a020'], outline: '#54350f', glow: '#f7d18f' },
};

/**
 * Tema "perosotan" (SlideRenderer, tema visual alternatif untuk
 * konektor jenis=ular, dipilih lewat toggle - lihat BoardRenderer). `body` =
 * [terang, gelap] gradient tabung plastik solid, tanpa pola/wajah.
 */
export const SLIDE_THEMES = {
    sky: { body: ['#7dd3fc', '#0284c7'], outline: '#0c4a6e', glow: '#38bdf8' },
    coral: { body: ['#fda4af', '#e11d48'], outline: '#7f1d1d', glow: '#fb7185' },
    lime: { body: ['#bef264', '#65a30d'], outline: '#365314', glow: '#a3e635' },
    grape: { body: ['#d8b4fe', '#9333ea'], outline: '#4c1d95', glow: '#c084fc' },
};

export const SNAKE_THEME_NAMES = Object.keys(SNAKE_THEMES);
export const LADDER_THEME_NAMES = Object.keys(LADDER_THEMES);
export const SLIDE_THEME_NAMES = Object.keys(SLIDE_THEMES);
