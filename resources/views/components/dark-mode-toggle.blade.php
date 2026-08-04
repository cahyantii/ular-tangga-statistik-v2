{{--
    Dark Mode Toggle Button
    ───────────────────────
    Komponen tombol toggle dark mode yang menggunakan Alpine.js
    untuk reaktivitas dan window.darkMode (dari dark-mode.js) untuk logika.

    Tampilan:
    - Mode terang → ikon bulan 🌙
    - Mode gelap  → ikon matahari ☀️

    Cara pakai:
    <x-dark-mode-toggle />
    <x-dark-mode-toggle class="..." /> (custom class)
--}}

<div
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            window.darkMode.toggle();
            this.dark = window.darkMode.isDark();
        }
    }"
>
    <button
        type="button"
        @click="toggle()"
        :title="dark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
        :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
        :aria-pressed="dark.toString()"
        {{ $attributes->merge(['class' => 'relative flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200']) }}
    >
        {{-- Ikon Bulan (tampil saat mode TERANG) --}}
        <svg
            x-show="!dark"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-75 rotate-12"
            x-transition:enter-end="opacity-100 scale-100 rotate-0"
            x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100 scale-100 rotate-0"
            x-transition:leave-end="opacity-0 scale-75 -rotate-12"
            xmlns="http://www.w3.org/2000/svg"
            class="absolute h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>

        {{-- Ikon Matahari (tampil saat mode GELAP) --}}
        <svg
            x-show="dark"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-75 -rotate-12"
            x-transition:enter-end="opacity-100 scale-100 rotate-0"
            x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100 scale-100 rotate-0"
            x-transition:leave-end="opacity-0 scale-75 rotate-12"
            xmlns="http://www.w3.org/2000/svg"
            class="absolute h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>
</div>
