<x-public-layout title="Cara Bermain - {{ config('app.name') }}">
    @php
        $toneClasses = [
            'blue' => 'bg-primary-500',
            'green' => 'bg-secondary-500',
            'amber' => 'bg-accent-500',
            'purple' => 'bg-violet-500',
            'rose' => 'bg-rose-500',
        ];

        $steps = [
            ['judul' => 'Masuk atau Daftar Akun', 'deskripsi' => 'Buat akun gratis atau masuk jika sudah terdaftar.', 'icon' => 'user-plus', 'tone' => 'green'],
            ['judul' => 'Pilih Mode Permainan', 'deskripsi' => 'Pilih bermain melawan Robot (AI) atau Multiplayer Real-Time melawan pemain lain.', 'icon' => 'gamepad', 'tone' => 'blue'],
            ['judul' => 'Masuk ke Room Permainan', 'deskripsi' => 'Untuk mode multiplayer, gunakan Quick Match untuk mencari lawan otomatis, atau buat/gabung Private Room dengan kode.', 'icon' => 'room', 'tone' => 'purple'],
            ['judul' => 'Lempar Dadu Digital', 'deskripsi' => 'Saat giliranmu tiba, klik tombol dadu. Pion akan bergerak sesuai angka yang keluar.', 'icon' => 'dice', 'tone' => 'amber'],
            ['judul' => 'Jawab Soal di Petak Soal', 'deskripsi' => 'Jika pion berhenti di petak soal, jawab pertanyaan seputar statistika/BPS sebelum waktu habis.', 'icon' => 'help', 'tone' => 'rose'],
            ['judul' => 'Kumpulkan Poin', 'deskripsi' => 'Jawaban benar +10 poin, jawaban salah -5 poin, petak bonus +20 poin, dan menang +100 poin.', 'icon' => 'coin', 'tone' => 'amber'],
            ['judul' => 'Waspadai Tangga, Ular, Penalti, dan Mystery', 'deskripsi' => 'Tangga membawamu naik lebih cepat, ular menjatuhkanmu, petak penalti mengurangi poin, dan petak mystery memberi efek kejutan.', 'icon' => 'snake-ladder', 'tone' => 'green'],
            ['judul' => 'Capai Petak Finish', 'deskripsi' => 'Permainan selesai ketika salah satu pemain mencapai petak Finish dengan angka dadu yang pas.', 'icon' => 'flag', 'tone' => 'blue'],
            ['judul' => 'Lihat Hasil dan Progres Belajarmu', 'deskripsi' => 'Setelah permainan selesai, lihat skor akhir, akurasi jawaban, achievement baru, dan progres belajarmu.', 'icon' => 'trend-up', 'tone' => 'purple'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-[#EAF3FF] via-[#F4F8FF] to-white dark:from-slate-900 dark:via-slate-900 dark:to-slate-900">
        {{-- Decorative light --}}
        <div aria-hidden="true" class="pointer-events-none absolute left-1/4 top-0 h-64 w-64 -translate-x-1/2 -translate-y-1/3 rounded-full bg-white/70 blur-3xl dark:bg-primary-900/20"></div>
        <div aria-hidden="true" class="pointer-events-none absolute right-1/4 bottom-0 h-72 w-72 translate-x-1/3 translate-y-1/3 rounded-full bg-blue-200/40 blur-3xl dark:bg-blue-900/20"></div>
        <div aria-hidden="true" class="pointer-events-none absolute left-1/2 top-1/2 h-[420px] w-[420px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[radial-gradient(circle,rgba(255,255,255,.7)_0%,rgba(255,255,255,0)_70%)] blur-2xl dark:bg-transparent"></div>

        {{-- Soft fade into the section below --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-[linear-gradient(to_bottom,rgba(255,255,255,0),rgba(255,255,255,.35),rgba(255,255,255,.75),rgba(255,255,255,1))] dark:bg-[linear-gradient(to_bottom,rgba(15,23,42,0),rgba(15,23,42,.6),rgba(15,23,42,1))] sm:h-48 lg:h-56"></div>

        <div class="relative mx-auto flex max-w-7xl flex-col items-center gap-6 px-4 py-16 sm:px-6 md:flex-row md:justify-between md:gap-6 lg:gap-10 lg:px-8 lg:py-20">
            <div class="shrink-0">
                <img
                    src="{{ asset('images/brand/ular.png') }}"
                    alt="Ilustrasi ular tangga"
                    loading="lazy"
                    class="mx-auto h-auto w-36 object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.7)] transition-transform duration-300 md:w-44 xl:w-64 xl:hover:scale-105"
                >
            </div>

            <div class="text-center md:flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl">Cara Bermain</h1>
                <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600 dark:text-slate-400">
                    Ikuti langkah-langkah berikut untuk mulai bermain dan jadi juara statistik!
                </p>
            </div>

            <div class="shrink-0">
                <img
                    src="{{ asset('images/brand/logo-gedung.png') }}"
                    alt="Ilustrasi kastil"
                    loading="lazy"
                    class="mx-auto h-auto w-36 object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.7)] transition-transform duration-300 md:w-44 xl:w-64 xl:hover:scale-105"
                >
            </div>
        </div>
    </section>

    {{-- Step Grid --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 lg:gap-x-10 lg:gap-y-10">
            @foreach ($steps as $step)
                <div class="group relative flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft dark:bg-slate-800/80 dark:shadow-slate-900/50">
                    <span class="absolute left-4 top-4 flex h-7 w-7 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                        {{ $loop->iteration }}
                    </span>

                    <span class="mt-4 flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-sm {{ $toneClasses[$step['tone']] }}">
                        @switch($step['icon'])
                            @case('room')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 4.5h3A1.5 1.5 0 0 1 19.5 6v12a1.5 1.5 0 0 1-1.5 1.5h-3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 8l-4 4 4 4M6 12h9" />
                                </svg>
                                @break

                            @case('snake-ladder')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                                    <path stroke-linecap="round" d="M7 3v9M15 3v9" />
                                    <path stroke-linecap="round" d="M7 6h8M7 9h8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 21c0-3 3-3 3-6s-3-3-3-6" />
                                    <circle cx="6" cy="7.2" r="1.3" fill="currentColor" stroke="none" />
                                </svg>
                                @break

                            @default
                                <x-player.icon :name="$step['icon']" class="h-6 w-6" />
                        @endswitch
                    </span>

                    <h3 class="mt-4 font-bold text-slate-900 dark:text-white">{{ $step['judul'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">{{ $step['deskripsi'] }}</p>

                    @if ($loop->iteration % 5 !== 0 && ! $loop->last)
                        <span class="pointer-events-none absolute right-0 top-1/2 hidden -translate-y-1/2 translate-x-1/2 lg:flex" aria-hidden="true">
                            <svg viewBox="0 0 40 16" class="h-4 w-9 text-primary-300 dark:text-primary-700" fill="none">
                                <path d="M2 8h28" stroke="currentColor" stroke-width="2" stroke-dasharray="3 4" stroke-linecap="round" />
                                <path d="M26 3l8 5-8 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                            </svg>
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative flex flex-col items-center gap-5 overflow-hidden rounded-3xl bg-gradient-to-r from-primary-50 via-blue-50 to-primary-50 p-6 text-center shadow-sm dark:from-slate-800 dark:via-slate-800/80 dark:to-slate-800 dark:border dark:border-slate-700 sm:gap-6 sm:p-8 md:flex-row md:justify-between md:gap-6 md:text-left lg:gap-10">
            {{-- Decorative light --}}
            <div aria-hidden="true" class="pointer-events-none absolute left-8 top-1/2 h-32 w-32 -translate-y-1/2 rounded-full bg-white/70 blur-3xl dark:bg-primary-900/20"></div>

            <img
                src="{{ asset('images/brand/logo-piala.png') }}"
                alt=""
                aria-hidden="true"
                loading="lazy"
                class="relative mx-auto h-auto w-16 shrink-0 object-contain drop-shadow-[0_8px_20px_rgba(255,255,255,0.8)] transition-transform duration-300 hover:scale-105 md:w-20 xl:w-28"
            >

            <div class="relative md:flex-1">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white sm:text-2xl">Siap Menguji Pengetahuanmu?</h2>
                <p class="mt-1 text-slate-600 dark:text-slate-300">Belajar jadi lebih seru, statistik jadi lebih mudah dipahami!</p>
            </div>

            <a
                href="{{ route('register') }}"
                class="relative inline-flex shrink-0 items-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft"
            >
                Mulai Bermain Sekarang
                <x-player.icon name="arrow-right" class="h-5 w-5" />
            </a>
        </div>
    </section>
</x-public-layout>
