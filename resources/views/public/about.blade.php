<x-public-layout title="Tentang - {{ config('app.name') }}">
    {{-- Hero --}}
    <section class="relative flex min-h-[85vh] items-center overflow-hidden bg-white dark:bg-dark-bg">
        {{-- Background Image --}}
        <img
            src="{{ asset('images/brand/logo-landigpage.png') }}"
            alt="Ilustrasi permainan Ular Tangga Statistik Indonesia"
            loading="lazy"
            class="absolute inset-0 z-0 h-full w-full object-cover object-[70%_center] sm:object-right opacity-25 sm:opacity-90 dark:opacity-15 dark:sm:opacity-50 transition-all duration-500"
        >

        {{-- Gradient Overlay for better readability --}}
        <div class="absolute inset-0 z-0 bg-gradient-to-r from-white via-white/90 to-white/40 sm:to-transparent dark:from-dark-bg dark:via-dark-bg/90 dark:to-dark-bg/40 dark:sm:to-transparent"></div>
        {{-- Bottom gradient for mobile readability --}}
        <div class="absolute inset-0 z-0 bg-gradient-to-b from-transparent via-white/50 to-white dark:via-dark-bg/50 dark:to-dark-bg sm:hidden"></div>

        <div class="relative z-10 mx-auto w-full max-w-[1600px] px-4 py-20 sm:px-6 lg:px-8">
            <div class="flex max-w-3xl flex-col items-start text-left">
                <span class="inline-flex items-center rounded-full bg-primary-50/90 backdrop-blur-sm dark:bg-primary-900/80 px-4 py-1.5 text-sm font-semibold text-primary-800 dark:text-primary-200 shadow-sm border border-primary-100 dark:border-primary-800/50">
                    Tentang Kami
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 drop-shadow-sm dark:text-white sm:text-5xl lg:text-7xl">
                    Tentang Ular Tangga
                    <span class="block text-primary-600 dark:text-primary-400">Statistik Indonesia</span>
                </h1>

                <p class="mt-6 max-w-2xl text-lg font-medium text-slate-700 drop-shadow-sm dark:text-slate-300 sm:text-xl">
                    Ular Tangga Statistik Indonesia adalah game edukasi berbasis web yang menggabungkan permainan
                    tradisional ular tangga dengan pembelajaran statistika dan pengenalan Badan Pusat Statistik (BPS).
                    Kami percaya bahwa literasi statistik adalah keterampilan penting yang sebaiknya dikuasai sejak dini,
                    dan cara terbaik untuk belajar adalah melalui pengalaman yang menyenangkan.
                </p>
            </div>
        </div>
    </section>

    {{-- Tujuan Kami / Untuk Siapa --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="flex items-center gap-6 rounded-3xl bg-white dark:bg-dark-surface p-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-white shadow-sm">
                            <x-player.icon name="target" class="h-5 w-5" />
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-dark-text">Tujuan Kami</h2>
                    </div>
                    <ul class="mt-5 space-y-3 text-slate-600 dark:text-dark-muted">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Meningkatkan literasi statistik masyarakat Indonesia
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Memperkenalkan tugas dan fungsi Badan Pusat Statistik
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Membuat pembelajaran statistika terasa menyenangkan
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Memberikan pengalaman belajar melalui gamifikasi
                        </li>
                    </ul>
                </div>

                <svg viewBox="0 0 100 100" class="hidden h-28 w-28 shrink-0 select-none sm:block" aria-hidden="true">
                    <circle cx="50" cy="50" r="46" fill="#EAF1FC" />
                    <rect x="28" y="50" width="10" height="26" rx="2" fill="#93C5FD" />
                    <rect x="42" y="40" width="10" height="36" rx="2" fill="#0F4CBA" />
                    <rect x="56" y="30" width="10" height="46" rx="2" fill="#3E75D1" />
                    <path d="M30 46 42 34 54 42 70 22" stroke="#0F4CBA" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    <path d="M62 22h8v8" stroke="#0F4CBA" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
            </div>

            <div class="flex flex-col-reverse items-center gap-6 rounded-3xl bg-white dark:bg-dark-surface p-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft sm:gap-8 lg:flex-row">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-secondary-500 text-white shadow-sm">
                            <x-player.icon name="users" class="h-5 w-5" />
                        </span>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-dark-text">Untuk Siapa?</h2>
                    </div>
                    <ul class="mt-5 space-y-3 text-slate-600 dark:text-dark-muted">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Pelajar SD, SMP, dan SMA
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Mahasiswa
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-secondary-100 text-secondary-600">
                                <x-player.icon name="check" class="h-3 w-3" />
                            </span>
                            Masyarakat umum yang ingin belajar statistik
                        </li>
                    </ul>
                </div>

                <img
                    src="{{ asset('images/brand/book.png') }}"
                    alt="Ilustrasi tumpukan buku"
                    loading="lazy"
                    class="mx-auto h-auto w-36 shrink-0 object-contain drop-shadow-[0_12px_28px_rgba(59,130,246,0.18)] transition-transform duration-300 hover:scale-105 md:w-44 lg:w-52"
                >
            </div>
        </div>
    </section>

    {{-- Mengapa BPS --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative flex flex-col items-center gap-6 overflow-hidden rounded-3xl bg-primary-50/60 dark:bg-primary-900/20 p-6 shadow-soft sm:gap-8 sm:p-8 lg:flex-row lg:p-10">
            <img
                src="{{ asset('images/brand/gedung.png') }}"
                alt="Ilustrasi gedung Badan Pusat Statistik"
                loading="lazy"
                class="mx-auto h-auto w-36 shrink-0 object-contain md:w-44 lg:w-52"
            >

            <div class="text-center lg:flex-1 lg:text-left">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-dark-text sm:text-3xl">Mengapa Badan Pusat Statistik?</h2>
                <p class="mt-3 max-w-2xl text-slate-600 dark:text-dark-muted leading-relaxed">
                    BPS adalah lembaga yang menghasilkan data resmi negara &ndash; mulai dari angka kemiskinan, inflasi,
                    hingga Indeks Pembangunan Manusia. Data-data ini memengaruhi kebijakan yang berdampak langsung
                    pada kehidupan kita. Dengan memahami dasar-dasar statistik dan peran BPS, kita menjadi warga
                    negara yang lebih kritis dalam membaca informasi dan data di sekitar kita.
                </p>
            </div>

            <svg viewBox="0 0 200 160" class="pointer-events-none absolute -right-6 bottom-0 hidden h-40 w-48 select-none opacity-40 lg:block" aria-hidden="true">
                <rect x="20" y="80" width="24" height="60" rx="4" fill="#0F4CBA" fill-opacity="0.15" />
                <rect x="60" y="50" width="24" height="90" rx="4" fill="#0F4CBA" fill-opacity="0.2" />
                <rect x="100" y="65" width="24" height="75" rx="4" fill="#0F4CBA" fill-opacity="0.15" />
                <rect x="140" y="30" width="24" height="110" rx="4" fill="#0F4CBA" fill-opacity="0.25" />
                <path d="M20 70 60 40 100 55 140 20" stroke="#0F4CBA" stroke-opacity="0.4" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </section>
</x-public-layout>
