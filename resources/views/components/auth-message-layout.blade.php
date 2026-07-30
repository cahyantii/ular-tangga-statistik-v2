{{--
    Layout ringan untuk halaman auth "status" yang HANYA muncul setelah user
    sudah login (verify-email, confirm-password) - beda dari auth-layout.blade.php
    yang dipakai Login/Register (guest, dua-kolom hero+form, TIDAK boleh
    diubah). Sengaja tidak reuse auth-layout karena slot hero/nav-prompt di
    sana didesain untuk cross-link Login<->Register, tidak relevan untuk
    halaman status pasca-login ini - jadi dibuat komponen kecil terpisah
    dengan bahasa visual yang sama (logo, kartu putih rounded, warna brand).
--}}
@props(['title'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-theme-init key="user-theme" />

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="overflow-x-hidden bg-[#F7FAFF] font-sans text-slate-800 antialiased dark:bg-slate-900 dark:text-slate-100">
        <header class="border-b border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900">
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-center px-3 sm:h-20 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-4">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-9 w-9 shrink-0 object-contain sm:h-11 sm:w-11"
                    >
                    <span class="leading-tight">
                        <span class="block text-sm font-bold text-slate-900 dark:text-slate-100 sm:text-base">Ular Tangga Statistik</span>
                        <span class="block text-[11px] text-slate-500 dark:text-slate-400 sm:text-xs">Belajar Statistik, Asyik &amp; Seru!</span>
                    </span>
                </a>
            </nav>
        </header>

        <main class="mx-auto flex min-h-[70vh] max-w-md items-center px-4 py-12">
            <div class="w-full animate-fade-in-up rounded-[28px] bg-white p-6 text-center shadow-[0_20px_60px_-15px_rgba(15,23,42,0.15)] dark:bg-slate-800 dark:shadow-none sm:p-10">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-slate-100 dark:border-slate-800">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-center gap-2 px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400 sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.</p>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
