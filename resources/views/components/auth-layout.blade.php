@props([
    'navPromptText',
    'navLinkText',
    'navLinkHref',
])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-[#F7FAFF] font-sans text-slate-800 antialiased">
        {{--
            Kegagalan OAuth (mis. konfigurasi Google/GitHub belum lengkap,
            atau user membatalkan proses di provider) di-flash ke
            session('oauth_error') oleh SocialiteController & bootstrap/app.php,
            lalu ditampilkan lewat <x-auth-session-status> (dipakai di halaman
            Login & Register) memakai gaya alert merah yang sama dengan
            x-admin.flash - detail lengkapnya tetap ditulis ke
            storage/logs/laravel.log.
        --}}

        <header class="border-b border-slate-100 bg-white">
            {{--
                Nav mobile sengaja dirapatkan (gap/padding lebih kecil di
                bawah sm:) supaya logo+nama+tombol tidak overflow di layar
                sempit (<390px) — body punya overflow-x-hidden jadi overflow
                sebelumnya "hilang" diam-diam (terpotong) alih-alih scroll,
                bukan cuma soal estetika. Dari sm: ke atas nilainya kembali
                seperti semula (tidak ada perubahan tablet/desktop).
            --}}
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-3 sm:h-20 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-4">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-9 w-9 shrink-0 object-contain sm:h-11 sm:w-11 md:h-12 md:w-12 lg:h-[52px] lg:w-[52px]"
                    >
                    <span class="min-w-0 truncate leading-tight">
                        <span class="block truncate text-sm font-bold text-slate-900 sm:text-base">Ular Tangga Statistik</span>
                        <span class="block truncate text-[11px] text-slate-500 sm:text-xs">Belajar Statistik, Asyik &amp; Seru!</span>
                    </span>
                </a>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <span class="hidden text-sm text-slate-500 sm:inline">{{ $navPromptText }}</span>
                    <a
                        href="{{ $navLinkHref }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-700 px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-50 sm:gap-2 sm:px-4 sm:py-2 sm:text-sm"
                    >
                        <x-player.icon name="user-plus" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                        {{ $navLinkText }}
                    </a>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 lg:py-16">
            {{--
                HERO MINI (mobile <768px saja, lihat properti "heroMobile" di
                bawah): versi ringkas panel kiri — bukan panel penuh yang
                disembunyikan/dipindah lewat CSS order (itu penyebab masalah
                lama: user harus scroll panjang). Konten & ukurannya memang
                beda dari panel kiri desktop (judul lebih pendek, gambar jauh
                lebih kecil) makanya disediakan lewat slot terpisah oleh
                halaman pemanggil (login/register), bukan reuse slot "hero"
                yang sama. Jarak ke card diatur lewat mb-5 (20px) di sini,
                LEPAS dari gap grid manapun.
            --}}
            <div class="mb-5 animate-fade-in-up text-center md:hidden">
                {{ $heroMobile }}
            </div>

            <div class="grid grid-cols-1 items-center gap-10 md:grid-cols-[2fr_3fr] md:gap-8 lg:grid-cols-2 lg:gap-16">
                {{-- Panel kiri penuh: disembunyikan di mobile (diganti heroMobile di atas), TIDAK diubah sama sekali di tablet/desktop. --}}
                <div class="hidden animate-fade-in-up md:block">
                    {{ $hero }}
                </div>

                <div class="animate-fade-in-up">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-100">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
                <div class="flex gap-6">
                    <a href="{{ route('about') }}" class="hover:text-blue-700">Tentang</a>
                    <a href="{{ route('faq') }}" class="hover:text-blue-700">FAQ</a>
                </div>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
