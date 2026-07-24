<x-public-layout title="Tentang - {{ config('app.name') }}">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-white">
        <div class="mx-auto max-w-[1600px] px-4 pb-10 pt-6 sm:px-6 lg:px-8 lg:pb-14 lg:pt-8">
            <div class="relative isolate min-h-[560px] overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-white via-blue-50 to-blue-100 shadow-xl md:min-h-[500px] lg:min-h-[620px]">

                {{-- Decorative light --}}
                <div aria-hidden="true" class="pointer-events-none absolute -left-16 -top-16 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div aria-hidden="true" class="pointer-events-none absolute -right-10 bottom-0 h-72 w-72 rounded-full bg-blue-300/30 blur-3xl lg:h-96 lg:w-96"></div>
                <div aria-hidden="true" class="pointer-events-none absolute right-1/3 top-10 hidden h-40 w-40 rounded-full bg-white/70 blur-2xl lg:block"></div>

                <div class="relative flex h-full flex-col items-center gap-8 px-6 py-8 text-center sm:px-8 md:px-10 md:py-12 lg:items-start lg:gap-0 lg:px-0 lg:py-0 lg:text-left">

                    {{-- Image: in-flow on mobile/tablet, full-bleed blended on desktop --}}
                    <div class="relative order-2 w-full lg:absolute lg:inset-0 lg:order-none lg:flex lg:items-end lg:justify-end">
                        <img
                            src="{{ asset('images/brand/logo-landigpage.png') }}"
                            alt="Ilustrasi permainan Ular Tangga Statistik Indonesia"
                            width="1672"
                            height="941"
                            loading="lazy"
                            class="animate-fade-in mx-auto h-auto w-4/5 object-contain drop-shadow-2xl transition-transform duration-300 hover:scale-[1.03] sm:w-3/4 md:w-3/5 lg:mx-0 lg:w-[95%] lg:origin-bottom-right"
                        >
                    </div>

                    {{-- Blend overlay so the image melts into the text zone (desktop only) --}}
                    <div aria-hidden="true" class="pointer-events-none absolute inset-0 hidden lg:block lg:bg-gradient-to-r lg:from-white lg:via-white/60 lg:to-transparent"></div>

                    <div class="animate-fade-in-up relative order-1 z-10 w-full lg:max-w-[45%] lg:px-16 lg:py-16">
                        <span class="inline-flex items-center rounded-full bg-primary-100 px-4 py-1.5 text-sm font-semibold text-primary-700">
                            Tentang Kami
                        </span>

                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                            Tentang Ular Tangga
                            <span class="block text-primary-600">Statistik Indonesia</span>
                        </h1>

                        <p class="mx-auto mt-6 max-w-xl text-lg text-slate-600 lg:mx-0">
                            Ular Tangga Statistik Indonesia adalah game edukasi berbasis web yang menggabungkan permainan
                            tradisional ular tangga dengan pembelajaran statistika dan pengenalan Badan Pusat Statistik (BPS).
                            Kami percaya bahwa literasi statistik adalah keterampilan penting yang sebaiknya dikuasai sejak dini,
                            dan cara terbaik untuk belajar adalah melalui pengalaman yang menyenangkan.
                        </p>
                    </div>
                </div>
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

            <div class="flex flex-col-reverse items-center gap-6 rounded-3xl bg-white p-8 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-soft sm:gap-8 lg:flex-row">
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
        <div class="relative flex flex-col items-center gap-6 overflow-hidden rounded-3xl bg-primary-50/60 p-6 shadow-soft sm:gap-8 sm:p-8 lg:flex-row lg:p-10">
            <img
                src="{{ asset('images/brand/gedung.png') }}"
                alt="Ilustrasi gedung Badan Pusat Statistik"
                loading="lazy"
                class="mx-auto h-auto w-36 shrink-0 object-contain md:w-44 lg:w-52"
            >

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
