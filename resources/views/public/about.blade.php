<x-public-layout title="Tentang - {{ config('app.name') }}">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-white">
        <div class="relative mx-auto flex max-w-[1600px] flex-col items-center gap-10 px-6 pb-16 pt-8 sm:px-10 sm:pb-20 lg:px-20 lg:pt-10 min-[992px]:flex-row min-[992px]:items-center min-[992px]:justify-between min-[992px]:gap-8">
            <div class="animate-fade-in-up w-full text-center min-[992px]:w-[45%] min-[992px]:text-left">
                <span class="inline-flex items-center rounded-full bg-primary-100 px-4 py-1.5 text-sm font-semibold text-primary-700">
                    Tentang Kami
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                    Tentang Ular Tangga
                    <span class="block text-primary-600">Statistik Indonesia</span>
                </h1>

                <p class="mx-auto mt-6 max-w-xl text-lg text-slate-600 min-[992px]:mx-0">
                    Ular Tangga Statistik Indonesia adalah game edukasi berbasis web yang menggabungkan permainan
                    tradisional ular tangga dengan pembelajaran statistika dan pengenalan Badan Pusat Statistik (BPS).
                    Kami percaya bahwa literasi statistik adalah keterampilan penting yang sebaiknya dikuasai sejak dini,
                    dan cara terbaik untuk belajar adalah melalui pengalaman yang menyenangkan.
                </p>
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

    {{-- Tujuan Kami / Untuk Siapa --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="flex items-center gap-6 rounded-3xl bg-white p-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-white shadow-sm">
                            <x-player.icon name="target" class="h-5 w-5" />
                        </span>
                        <h2 class="text-xl font-bold text-slate-900">Tujuan Kami</h2>
                    </div>
                    <ul class="mt-5 space-y-3 text-slate-600">
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

            <div class="flex items-center gap-6 rounded-3xl bg-white p-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-secondary-500 text-white shadow-sm">
                            <x-player.icon name="users" class="h-5 w-5" />
                        </span>
                        <h2 class="text-xl font-bold text-slate-900">Untuk Siapa?</h2>
                    </div>
                    <ul class="mt-5 space-y-3 text-slate-600">
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

                <svg viewBox="0 0 100 100" class="hidden h-28 w-28 shrink-0 select-none sm:block" aria-hidden="true">
                    <circle cx="50" cy="50" r="46" fill="#E5F8EE" />
                    <rect x="28" y="58" width="44" height="10" rx="2" fill="#16A34A" />
                    <rect x="32" y="48" width="36" height="10" rx="2" fill="#22C55E" />
                    <path d="M50 26 78 38 50 50 22 38Z" fill="#0A317A" />
                    <path d="M50 50v10" stroke="#0A317A" stroke-width="3" stroke-linecap="round" />
                    <circle cx="50" cy="62" r="2.4" fill="#0A317A" />
                    <path d="M28 40v10c0 4 8 7 22 7s22-3 22-7V40" stroke="#0A317A" stroke-width="2.5" fill="none" stroke-linecap="round" />
                </svg>
            </div>
        </div>
    </section>

    {{-- Mengapa BPS --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative flex flex-col items-center gap-8 overflow-hidden rounded-3xl bg-primary-50/60 p-8 sm:p-10 lg:flex-row">
            <svg viewBox="0 0 160 160" class="h-32 w-32 shrink-0 select-none sm:h-36 sm:w-36" aria-hidden="true">
                <circle cx="18" cy="140" r="14" fill="#16A34A" />
                <rect x="15" y="128" width="6" height="14" fill="#7C4A26" />
                <circle cx="142" cy="140" r="12" fill="#22C55E" />
                <rect x="139" y="130" width="6" height="14" fill="#7C4A26" />

                <rect x="30" y="40" width="100" height="100" rx="4" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                <path d="M30 40 80 14 130 40Z" fill="#0F4CBA" />
                <text x="80" y="32" font-size="12" font-weight="700" fill="#ffffff" text-anchor="middle">BPS</text>

                <rect x="42" y="55" width="16" height="16" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2" />
                <rect x="72" y="55" width="16" height="16" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2" />
                <rect x="102" y="55" width="16" height="16" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2" />
                <rect x="42" y="80" width="16" height="16" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2" />
                <rect x="102" y="80" width="16" height="16" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2" />
                <rect x="70" y="110" width="20" height="30" fill="#0F4CBA" fill-opacity="0.15" stroke="#0F4CBA" stroke-width="2.5" />
            </svg>

            <div class="text-center lg:flex-1 lg:text-left">
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Mengapa Badan Pusat Statistik?</h2>
                <p class="mt-3 max-w-2xl text-slate-600 leading-relaxed">
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
