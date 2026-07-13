<x-public-layout :title="config('app.name')">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-white">
        <div class="relative mx-auto flex max-w-[1600px] flex-col items-center gap-10 px-6 pb-16 pt-8 sm:px-10 sm:pb-20 lg:px-20 lg:pt-10 min-[992px]:flex-row min-[992px]:items-center min-[992px]:justify-between min-[992px]:gap-8">
            <div class="animate-fade-in-up w-full text-center min-[992px]:w-[45%] min-[992px]:text-left">
                <span class="inline-flex items-center rounded-full bg-primary-100 px-4 py-1.5 text-sm font-semibold text-primary-700">
                    Belajar Statistik Jadi Seru
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Ular Tangga
                    <span class="block text-primary-600">Statistik Indonesia</span>
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-lg text-slate-600 min-[992px]:mx-0">
                    Mainkan ular tangga digital sambil belajar Statistika, mengenal Badan Pusat Statistik (BPS),
                    dan memahami indikator penting bangsa &mdash; IPM, PDRB, kemiskinan, pengangguran, hingga inflasi.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row min-[992px]:justify-start">
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:from-primary-600 hover:to-primary-700 hover:shadow-soft sm:w-auto"
                    >
                        <x-player.icon name="dice" class="h-5 w-5" />
                        Mulai Bermain
                    </a>
                    <a
                        href="{{ route('how-to-play') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-primary-500 bg-white px-6 py-3 text-base font-semibold text-primary-600 transition-all duration-300 hover:-translate-y-0.5 hover:scale-[1.02] hover:bg-primary-50 sm:w-auto"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-5 w-5">
                            <circle cx="12" cy="12" r="9.5" />
                            <path d="M10 8.5v7l6-3.5-6-3.5Z" fill="currentColor" stroke="none" />
                        </svg>
                        Lihat Cara Bermain
                    </a>
                </div>
            </div>

            <div class="w-full min-[992px]:flex min-[992px]:w-[55%] min-[992px]:justify-end">
                <img
                    src="{{ asset('images/brand/logo-landingpage.png') }}"
                    alt="Ilustrasi permainan Ular Tangga Statistik Indonesia"
                    width="1831"
                    height="859"
                    loading="lazy"
                    class="animate-fade-in block h-auto w-full object-contain min-[992px]:max-w-[600px] min-[992px]:translate-x-[90px] min-[992px]:-translate-y-[40px] min-[1200px]:max-w-[750px] min-[1400px]:max-w-[900px]"
                >
            </div>
        </div>
    </section>

    {{-- Materi --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-slate-900">
                Apa yang <span class="text-primary-600">Akan Kamu</span> Pelajari?
            </h2>
            <p class="mt-3 text-slate-600">Materi disusun agar mudah dipahami pelajar SD hingga masyarakat umum.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="group flex items-center gap-4 rounded-2xl bg-primary-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="min-w-0 flex-1">
                    <img
                        src="{{ asset('images/brand/logo-statistik1.png') }}"
                        alt="Statistika Dasar"
                        class="h-16 w-16 shrink-0 object-contain drop-shadow-[0_6px_12px_rgba(37,99,235,0.12)]"
                    >
                    <h3 class="mt-4 font-bold text-slate-900">Statistika Dasar</h3>
                    <p class="mt-1.5 text-sm text-slate-600">Mean, median, modus, serta cara membaca tabel dan grafik.</p>
                </div>
                <img
                    src="{{ asset('images/brand/logo-statistik2.png') }}"
                    alt="Statistika Dasar"
                    class="h-[120px] w-[120px] shrink-0 object-contain drop-shadow-[0_8px_18px_rgba(37,99,235,0.15)]"
                >
            </div>

            <div class="group flex items-center gap-4 rounded-2xl bg-violet-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="min-w-0 flex-1">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500 text-white shadow-sm">
                        <x-player.icon name="building" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900">Pengenalan BPS</h3>
                    <p class="mt-1.5 text-sm text-slate-600">Tugas, fungsi, serta sensus dan survei yang diselenggarakan BPS.</p>
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

            <div class="group flex items-center gap-4 rounded-2xl bg-accent-50/60 p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft sm:col-span-2 lg:col-span-1">
                <div class="min-w-0 flex-1">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent-500 text-white shadow-sm">
                        <x-player.icon name="trend-up" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900">Indikator Statistik</h3>
                    <p class="mt-1.5 text-sm text-slate-600">IPM, PDRB, kemiskinan, pengangguran, dan inflasi.</p>
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
        <div class="flex flex-col items-center gap-8 rounded-3xl bg-white p-8 shadow-soft-lg sm:p-10 lg:flex-row lg:justify-between">
            <img
                src="{{ asset('images/brand/logo-rob.png') }}"
                alt=""
                aria-hidden="true"
                class="h-24 w-auto shrink-0 select-none object-contain sm:h-32 lg:h-40 xl:h-44"
                loading="lazy"
            >

            <div class="text-center lg:flex-1 lg:text-left">
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                    Siap Memulai Petualangan <span class="text-primary-600">Statistikmu?</span>
                </h2>
                <p class="mt-3 text-slate-600">Bergabung sekarang dan uji kemampuanmu dalam dunia statistik!</p>
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
                class="h-auto w-28 shrink-0 select-none object-contain sm:w-36 lg:w-44 xl:w-52"
                loading="lazy"
            >
        </div>
    </section>
</x-public-layout>
