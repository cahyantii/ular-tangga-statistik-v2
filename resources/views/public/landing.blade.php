<x-public-layout :title="config('app.name')">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-white dark:bg-slate-900">
        <div class="">
            <div class="relative isolate min-h-[max(480px,calc(100svh-144px))] overflow-hidden border border-blue-100 bg-gradient-to-br from-white via-blue-50 to-blue-100 shadow-xl dark:border-blue-900/40 dark:from-slate-800 dark:via-slate-800 dark:to-slate-900 lg:min-h-[max(560px,calc(100svh-168px))]">

                {{-- Decorative light --}}
                <div aria-hidden="true" class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 bottom-0 h-72 w-72 rounded-full bg-blue-300/30 blur-3xl lg:h-96 lg:w-96"></div>
                <div aria-hidden="true" class="pointer-events-none absolute right-1/3 top-10 hidden h-40 w-40 rounded-full bg-white/70 blur-2xl lg:block"></div>

                <div class="absolute inset-0 flex flex-col items-center gap-8 px-6 py-8 text-center sm:px-8 md:px-10 md:py-12 lg:items-start lg:gap-0 lg:px-0 lg:py-0 lg:text-left">

                    {{-- Image: full-bleed background filling the entire hero card at every breakpoint (no sizing container) --}}
                    <div class="absolute inset-0">
                        <img
                            src="{{ asset('images/brand/logo-landigpage.png') }}"
                            alt="Ilustrasi permainan Ular Tangga Statistik Indonesia"
                            width="1672"
                            height="941"
                            loading="lazy"
                            class="animate-fade-in h-full w-full object-cover"
                        >
                    </div>

                    {{-- Blend overlay so the image melts into the text zone at every breakpoint --}}
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white via-white/75 to-white/20 dark:from-slate-800 dark:via-slate-800/75 dark:to-slate-800/20 lg:bg-gradient-to-r lg:from-white lg:via-white/60 lg:to-transparent dark:lg:from-slate-800 dark:lg:via-slate-800/60 dark:lg:to-transparent"></div>

                    <div class="animate-fade-in-up relative order-1 z-10 w-full lg:max-w-[45%] lg:px-16 lg:pt-16">
                        <span class="inline-flex items-center rounded-full bg-primary-100 px-4 py-1.5 text-sm font-semibold text-primary-700 dark:bg-primary-500/20 dark:text-primary-400">
                            Belajar Statistik Jadi Seru
                        </span>

                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-slate-100">
                            Ular Tangga
                            <span class="block text-primary-600 dark:text-primary-400">Statistik Indonesia</span>
                        </h1>

                        <p class="mx-auto mt-6 mb-6 max-w-xl text-lg text-slate-600 lg:mx-0 dark:text-slate-400">
                            Mainkan ular tangga digital sambil belajar Statistika, mengenal Badan Pusat Statistik (BPS),
                            dan memahami indikator penting bangsa &mdash; IPM, PDRB, kemiskinan, pengangguran, hingga inflasi.
                        </p>
                    </div>

                    <div class="relative order-3 z-10 flex w-full flex-col items-center justify-center gap-4 sm:flex-row lg:max-w-[45%] lg:justify-start lg:px-16 lg:pb-16">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:w-auto"
                        >
                            <x-player.icon name="dice" class="h-5 w-5" />
                            Mulai Bermain
                        </a>
                        <a
                            href="{{ route('how-to-play') }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-primary-500 bg-white px-6 py-3 text-base font-semibold text-primary-600 transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:bg-primary-50 sm:w-auto dark:bg-slate-800 dark:text-primary-400 dark:hover:bg-primary-500/10"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-5 w-5">
                                <circle cx="12" cy="12" r="9.5" />
                                <path d="M10 8.5v7l6-3.5-6-3.5Z" fill="currentColor" stroke="none" />
                            </svg>
                            Lihat Cara Bermain
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Materi --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                Apa yang <span class="text-primary-600 dark:text-primary-400">Akan Kamu</span> Pelajari?
            </h2>
            <p class="mt-3 text-slate-600 dark:text-slate-400">Materi disusun agar mudah dipahami pelajar SD hingga masyarakat umum.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group flex items-center gap-4 rounded-2xl bg-primary-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft dark:bg-primary-500/10">
                <div class="min-w-0 flex-1">
                    <img
                        src="{{ asset('images/brand/logo-statistik1.png') }}"
                        alt="Statistika Dasar"
                        class="h-16 w-16 shrink-0 object-contain drop-shadow-[0_6px_12px_rgba(37,99,235,0.12)]"
                    >
                    <h3 class="mt-4 font-bold text-slate-900 dark:text-slate-100">Statistika Dasar</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">Mean, median, modus, serta cara membaca tabel dan grafik.</p>
                </div>
                <img
                    src="{{ asset('images/brand/logo-statistik2.png') }}"
                    alt="Statistika Dasar"
                    class="h-[120px] w-[120px] shrink-0 object-contain drop-shadow-[0_8px_18px_rgba(37,99,235,0.15)]"
                >
            </div>

            <div class="group flex items-center gap-4 rounded-2xl bg-violet-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft dark:bg-violet-500/10">
                <div class="min-w-0 flex-1">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500 text-white shadow-sm">
                        <x-player.icon name="building" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900 dark:text-slate-100">Pengenalan BPS</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">Tugas, fungsi, serta sensus dan survei yang diselenggarakan BPS.</p>
                </div>
                <svg viewBox="0 0 90 90" class="h-20 w-20 shrink-0 select-none" aria-hidden="true">
                    <rect x="18" y="14" width="54" height="66" rx="4" fill="#ffffff" stroke="#EDE9FE" stroke-width="2" />
                    <rect x="26" y="24" width="10" height="10" fill="#7C3AED" fill-opacity="0.75" />
                    <rect x="40" y="24" width="10" height="10" fill="#7C3AED" fill-opacity="0.55" />
                    <rect x="54" y="24" width="10" height="10" fill="#7C3AED" fill-opacity="0.75" />
                    <rect x="26" y="40" width="10" height="10" fill="#7C3AED" fill-opacity="0.55" />
                    <rect x="40" y="40" width="10" height="10" fill="#7C3AED" fill-opacity="0.75" />
                    <rect x="54" y="40" width="10" height="10" fill="#7C3AED" fill-opacity="0.55" />
                    <rect x="34" y="58" width="22" height="22" fill="#7C3AED" />
                    <path d="M18 14h54l-27-10Z" fill="#7C3AED" />
                </svg>
            </div>

            <div class="group flex items-center gap-4 rounded-2xl bg-accent-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft sm:col-span-2 lg:col-span-1 dark:bg-accent-500/10">
                <div class="min-w-0 flex-1">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500 text-white shadow-sm">
                        <x-player.icon name="trend-up" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900 dark:text-slate-100">Indikator Statistik</h3>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">IPM, PDRB, kemiskinan, pengangguran, dan inflasi.</p>
                </div>
                <svg viewBox="0 0 90 90" class="h-20 w-20 shrink-0 select-none" aria-hidden="true">
                    <rect x="16" y="10" width="50" height="66" rx="4" fill="#ffffff" stroke="#FED7AA" stroke-width="2" />
                    <rect x="27" y="4" width="28" height="10" rx="3" fill="#F68B1F" />
                    <path d="M26 52l8-10 7 6 11-16" stroke="#F68B1F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    <circle cx="52" cy="32" r="3" fill="#F68B1F" />
                    <rect x="26" y="60" width="8" height="8" fill="#FDBA74" />
                    <rect x="38" y="60" width="8" height="8" fill="#F68B1F" />
                    <rect x="50" y="60" width="8" height="8" fill="#FDBA74" />
                </svg>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative flex flex-col items-center gap-8 overflow-hidden rounded-3xl bg-white p-8 shadow-soft-lg sm:p-10 lg:flex-row lg:justify-between dark:bg-slate-800 dark:shadow-none">
            {{-- Decorative light --}}
            <div aria-hidden="true" class="pointer-events-none absolute -left-10 top-1/2 h-48 w-48 -translate-y-1/2 rounded-full bg-primary-50 blur-3xl dark:bg-primary-500/10"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -right-10 top-1/2 h-48 w-48 -translate-y-1/2 rounded-full bg-blue-50 blur-3xl dark:bg-blue-500/10"></div>

            <img
                src="{{ asset('images/brand/logo-rob.png') }}"
                alt=""
                aria-hidden="true"
                class="relative mx-auto h-auto w-28 shrink-0 select-none object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.8)] transition-transform duration-300 sm:w-36 md:w-44 lg:w-60 lg:hover:scale-105"
                loading="lazy"
            >

            <div class="relative text-center lg:flex-1 lg:text-left">
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl dark:text-slate-100">
                    Siap Memulai Petualangan <span class="text-primary-600 dark:text-primary-400">Statistikmu?</span>
                </h2>
                <p class="mt-3 text-slate-600 dark:text-slate-400">Bergabung sekarang dan uji kemampuanmu dalam dunia statistik!</p>
                <a
                    href="{{ route('register') }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft"
                >
                    Daftar Sekarang
                    <x-player.icon name="arrow-right" class="h-5 w-5" />
                </a>
            </div>

            <img
                src="{{ asset('images/brand/logo-berlian.png') }}"
                alt=""
                aria-hidden="true"
                class="relative mx-auto h-auto w-28 shrink-0 select-none object-contain drop-shadow-[0_10px_28px_rgba(255,255,255,0.8)] transition-transform duration-300 sm:w-36 md:w-44 lg:w-60 lg:hover:scale-105"
                loading="lazy"
            >
        </div>
    </section>
</x-public-layout>
