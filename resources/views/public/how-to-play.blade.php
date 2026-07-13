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
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-blue-50 to-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-8 px-4 py-16 sm:px-6 lg:flex-row lg:justify-between lg:px-8 lg:py-20">
            <div class="hidden shrink-0 lg:block">
                <svg viewBox="0 0 220 200" class="h-40 w-44 select-none xl:h-48 xl:w-52" aria-hidden="true">
                    <g transform="translate(6 10) scale(0.55)">
                        <path d="M0 260c0-80 150-80 150-185 0-46-46-58-80-35" stroke="#16A34A" stroke-width="52" stroke-linecap="round" fill="none" />
                        <path d="M0 260c0-80 150-80 150-185 0-46-46-58-80-35" stroke="#22C55E" stroke-width="38" stroke-linecap="round" fill="none" />
                        <circle cx="72" cy="40" r="34" fill="#22C55E" />
                        <circle cx="62" cy="30" r="8" fill="#0A317A" />
                        <circle cx="59" cy="27" r="3" fill="#ffffff" />
                        <path d="M46 52c7 7 25 7 32 0" stroke="#0A317A" stroke-width="4.5" stroke-linecap="round" fill="none" />
                        <path d="M73 58 85 72 73 68Z" fill="#EF4444" />
                    </g>
                    <g transform="translate(120 55) scale(0.55)">
                        <rect x="0" y="0" width="12" height="200" rx="4" fill="#B45309" />
                        <rect x="50" y="0" width="12" height="200" rx="4" fill="#B45309" />
                        <rect x="0" y="30" width="62" height="11" rx="3" fill="#D97706" />
                        <rect x="0" y="80" width="62" height="11" rx="3" fill="#D97706" />
                        <rect x="0" y="130" width="62" height="11" rx="3" fill="#D97706" />
                    </g>
                    <g transform="translate(112 148) scale(0.5)">
                        <rect x="0" y="0" width="80" height="80" rx="14" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="3" />
                        <circle cx="20" cy="20" r="6.5" fill="#1E293B" />
                        <circle cx="60" cy="20" r="6.5" fill="#1E293B" />
                        <circle cx="40" cy="40" r="6.5" fill="#1E293B" />
                        <circle cx="20" cy="60" r="6.5" fill="#1E293B" />
                        <circle cx="60" cy="60" r="6.5" fill="#1E293B" />
                    </g>
                    <path d="M14 150l4 9 9 4-9 4-4 9-4-9-9-4 9-4Z" fill="#93C5FD" />
                    <circle cx="192" cy="38" r="4" fill="#FCA5A5" />
                    <rect x="178" y="118" width="8" height="8" fill="#FCD34D" transform="rotate(20 182 122)" />
                </svg>
            </div>

            <div class="text-center lg:flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">Cara Bermain</h1>
                <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">
                    Ikuti langkah-langkah berikut untuk mulai bermain dan jadi juara statistik!
                </p>
            </div>

            <div class="hidden shrink-0 lg:block">
                <svg viewBox="0 0 280 200" class="h-40 w-52 select-none xl:h-48 xl:w-60" aria-hidden="true">
                    <ellipse cx="150" cy="185" rx="120" ry="12" fill="#D1FAE5" opacity="0.6" />
                    <g transform="translate(90 6) scale(0.85)">
                        <rect x="0" y="60" width="180" height="110" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="3" />
                        <rect x="-24" y="30" width="34" height="140" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="3" />
                        <rect x="170" y="30" width="34" height="140" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="3" />
                        <path d="M-24 30 -7 6l17 24Z" fill="#3E75D1" stroke="#0F4CBA" stroke-width="2.5" />
                        <path d="M170 30l17-24 17 24Z" fill="#3E75D1" stroke="#0F4CBA" stroke-width="2.5" />
                        <rect x="70" y="100" width="40" height="70" fill="#0F4CBA" fill-opacity="0.15" stroke="#0F4CBA" stroke-width="2.5" />
                        <rect x="20" y="85" width="22" height="22" fill="#ffffff" stroke="#0F4CBA" stroke-width="2" />
                        <rect x="138" y="85" width="22" height="22" fill="#ffffff" stroke="#0F4CBA" stroke-width="2" />
                        <line x1="-7" y1="6" x2="-7" y2="-18" stroke="#0F4CBA" stroke-width="3" />
                        <path d="M-7-18h20l-20 14Z" fill="#3E75D1" />
                    </g>
                    <g transform="translate(14 88) scale(0.7)">
                        <path d="M-9 0h18v12a9 9 0 0 1-18 0V0Z" fill="#FCD34D" stroke="#F59E0B" stroke-width="2" />
                        <path d="M-9 2h-7v3a7 7 0 0 0 7 7M9 2h7v3a7 7 0 0 1-7 7" stroke="#F59E0B" stroke-width="2.2" fill="none" />
                        <rect x="-3.5" y="18" width="7" height="9" fill="#F59E0B" />
                        <path d="M-8 27h16l-2.5 8h-11Z" fill="#F59E0B" />
                    </g>
                    <path d="M254 30l5 10 10 5-10 5-5 10-5-10-10-5 10-5Z" fill="#93C5FD" />
                    <circle cx="22" cy="30" r="4" fill="#C4B5FD" />
                </svg>
            </div>
        </div>
    </section>

    {{-- Step Grid --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 lg:gap-x-10 lg:gap-y-10">
            @foreach ($steps as $step)
                <div class="group relative flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
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

                    <h3 class="mt-4 font-bold text-slate-900">{{ $step['judul'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-600">{{ $step['deskripsi'] }}</p>

                    @if ($loop->iteration % 5 !== 0 && ! $loop->last)
                        <span class="pointer-events-none absolute right-0 top-1/2 hidden -translate-y-1/2 translate-x-1/2 lg:flex" aria-hidden="true">
                            <svg viewBox="0 0 40 16" class="h-4 w-9 text-primary-300" fill="none">
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
        <div class="flex flex-col items-center gap-6 rounded-3xl bg-primary-50 p-6 text-center sm:p-8 lg:flex-row lg:justify-between lg:text-left">
            <div class="flex flex-col items-center gap-4 sm:flex-row">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary-500 text-white shadow-sm">
                    <x-player.icon name="trophy" class="h-7 w-7" />
                </span>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 sm:text-2xl">Siap Menguji Pengetahuanmu?</h2>
                    <p class="mt-1 text-slate-600">Belajar jadi lebih seru, statistik jadi lebih mudah dipahami!</p>
                </div>
            </div>

            <a
                href="{{ route('register') }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft"
            >
                Mulai Bermain Sekarang
                <x-player.icon name="arrow-right" class="h-5 w-5" />
            </a>
        </div>
    </section>
</x-public-layout>
