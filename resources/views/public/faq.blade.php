<x-public-layout title="FAQ - {{ config('app.name') }}">
    @php
        $faqs = [
            ['icon' => 'gift', 'q' => 'Apakah Ular Tangga Statistik Indonesia gratis?', 'a' => 'Ya. Game dapat dimainkan secara gratis oleh seluruh pengguna yang telah memiliki akun.'],
            ['icon' => 'gamepad', 'q' => 'Apa perbedaan mode Vs Robot dan Multiplayer?', 'a' => 'Mode Robot melawan AI sedangkan Multiplayer melawan pemain lain secara realtime.'],
            ['icon' => 'chart-bar', 'q' => 'Bagaimana sistem skor bekerja?', 'a' => 'Jawaban benar memperoleh poin, jawaban salah mengurangi poin, bonus tile menambah poin, dan kemenangan memberikan bonus tambahan.'],
            ['icon' => 'wifi', 'q' => 'Apa yang terjadi jika koneksi saya terputus saat multiplayer?', 'a' => 'Sistem akan mencoba reconnect otomatis. Jika gagal maka permainan dianggap selesai.'],
            ['icon' => 'certificate', 'q' => 'Bagaimana cara mendapatkan sertifikat digital?', 'a' => 'Selesaikan seluruh kategori, capai akurasi minimal 80%, dan selesaikan minimal satu permainan.'],
            ['icon' => 'dice', 'q' => 'Apakah soal bisa muncul berulang dalam satu permainan?', 'a' => 'Tidak. Sistem melakukan randomisasi sehingga soal tidak muncul dua kali pada permainan yang sama.'],
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
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">Pertanyaan yang Sering Diajukan</h1>
                <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600">
                    Temukan jawaban atas pertanyaan umum seputar Ular Tangga Statistik Indonesia.
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

    {{-- FAQ List --}}
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft" x-data="{ open: false }">
                    <button
                        type="button"
                        class="flex w-full items-center gap-4 px-6 py-5 text-left"
                        :aria-expanded="open.toString()"
                        @click="open = !open"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                            <x-player.icon :name="$faq['icon']" class="h-5 w-5" />
                        </span>

                        <span class="flex-1 font-semibold text-slate-900">{{ $faq['q'] }}</span>

                        <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                            <span class="absolute h-0.5 w-3 rounded-full bg-primary-600"></span>
                            <span
                                class="absolute h-0.5 w-3 rounded-full bg-primary-600 transition-transform duration-200"
                                :class="open ? 'rotate-0' : 'rotate-90'"
                            ></span>
                        </span>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                    >
                        <p class="px-6 pb-5 pl-[4.5rem] text-slate-600">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="mt-8 flex flex-col items-center gap-6 rounded-3xl bg-gradient-to-r from-primary-50 to-blue-50 p-6 text-center shadow-sm sm:p-8 lg:flex-row lg:justify-between lg:text-left">
            <div class="flex flex-col items-center gap-4 sm:flex-row">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-primary-600 shadow-sm">
                    <x-player.icon name="headset" class="h-7 w-7" />
                </span>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Masih punya pertanyaan?</h2>
                    <p class="mt-1 text-slate-600">Jika kamu tidak menemukan jawaban yang dicari, hubungi kami.</p>
                </div>
            </div>

            <a
                href="mailto:{{ config('mail.from.address') }}"
                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border-2 border-primary-500 bg-white px-6 py-3 text-base font-semibold text-primary-600 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600 hover:text-white sm:w-auto"
            >
                <x-player.icon name="mail" class="h-5 w-5" />
                Hubungi Kami
            </a>
        </div>
    </section>
</x-public-layout>
