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
    <section class="relative overflow-hidden bg-gradient-to-b from-[#EAF3FF] via-[#F4F8FF] to-white dark:from-dark-bg dark:via-dark-bg/80 dark:to-dark-bg">
        {{-- Decorative light --}}
        <div aria-hidden="true" class="pointer-events-none absolute left-0 top-1/2 h-56 w-56 -translate-x-1/3 -translate-y-1/2 rounded-full bg-white/70 dark:bg-white/10 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute right-0 top-1/2 h-56 w-56 translate-x-1/3 -translate-y-1/2 rounded-full bg-blue-200/50 dark:bg-blue-900/30 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-40 rounded-[100%] bg-white/60 dark:bg-dark-bg/60 blur-3xl"></div>

        {{-- Soft fade into the section below --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-b from-white/0 via-white/60 to-white dark:from-transparent dark:via-dark-bg/60 dark:to-dark-bg sm:h-36 lg:h-40"></div>

        <div class="relative mx-auto flex max-w-7xl flex-col items-center gap-6 px-4 py-16 sm:px-6 md:flex-row md:justify-between md:gap-6 lg:gap-10 lg:px-8 lg:py-20">
            <div class="shrink-0">
                <img
                    src="{{ asset('images/brand/ular.png') }}"
                    alt="Ilustrasi ular tangga"
                    loading="lazy"
                    class="mx-auto h-auto w-36 object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.7)] dark:drop-shadow-none transition-transform duration-300 md:w-44 xl:w-64 xl:hover:scale-105"
                >
            </div>

            <div class="text-center md:flex-1">
                <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 dark:text-dark-text sm:text-5xl">Pertanyaan yang Sering Diajukan</h1>
                <p class="mx-auto mt-4 max-w-xl text-lg text-slate-600 dark:text-dark-muted">
                    Temukan jawaban atas pertanyaan umum seputar Ular Tangga Statistik Indonesia.
                </p>
            </div>

            <div class="shrink-0">
                <img
                    src="{{ asset('images/brand/logo-gedung.png') }}"
                    alt="Ilustrasi kastil"
                    loading="lazy"
                    class="mx-auto h-auto w-36 object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.7)] dark:drop-shadow-none transition-transform duration-300 md:w-44 xl:w-64 xl:hover:scale-105"
                >
            </div>
        </div>
    </section>

    {{-- FAQ List --}}
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <div class="overflow-hidden rounded-3xl border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft" x-data="{ open: false }">
                    <button
                        type="button"
                        class="flex w-full items-center gap-4 px-6 py-5 text-left focus:outline-none"
                        :aria-expanded="open.toString()"
                        @click="open = !open"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                            <x-player.icon :name="$faq['icon']" class="h-5 w-5" />
                        </span>

                        <span class="flex-1 font-semibold text-slate-900 dark:text-dark-text">{{ $faq['q'] }}</span>

                        <span class="relative flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                            <span class="absolute h-0.5 w-3 rounded-full bg-primary-600 dark:bg-primary-400"></span>
                            <span
                                class="absolute h-0.5 w-3 rounded-full bg-primary-600 dark:bg-primary-400 transition-transform duration-200"
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
                        <p class="px-6 pb-5 pl-[4.5rem] text-slate-600 dark:text-dark-muted">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- CTA --}}
        <div class="mt-8 flex flex-col items-center gap-6 rounded-3xl bg-gradient-to-r from-primary-50 to-blue-50 dark:from-primary-900/20 dark:to-blue-900/20 p-6 text-center shadow-sm sm:p-8 lg:flex-row lg:justify-between lg:text-left">
            <div class="flex flex-col items-center gap-4 sm:flex-row">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white dark:bg-dark-surface text-primary-600 dark:text-primary-400 shadow-sm border border-slate-100 dark:border-dark-border">
                    <x-player.icon name="headset" class="h-7 w-7" />
                </span>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-dark-text">Masih punya pertanyaan?</h2>
                    <p class="mt-1 text-slate-600 dark:text-dark-muted">Jika kamu tidak menemukan jawaban yang dicari, hubungi kami.</p>
                </div>
            </div>

            <a
                href="mailto:{{ config('mail.from.address') }}"
                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border-2 border-primary-500 dark:border-primary-600 bg-white dark:bg-dark-surface px-6 py-3 text-base font-semibold text-primary-600 dark:text-primary-400 transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-600 dark:hover:text-white sm:w-auto"
            >
                <x-player.icon name="mail" class="h-5 w-5" />
                Hubungi Kami
            </a>
        </div>
    </section>
</x-public-layout>
