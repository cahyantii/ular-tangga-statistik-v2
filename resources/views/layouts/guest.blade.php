<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dark mode -->
        @vite('resources/js/dark-mode.js')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased transition-colors duration-200 dark:bg-dark-bg dark:text-dark-text">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 transition-colors duration-200 dark:bg-dark-bg">
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6">
                <x-dark-mode-toggle />
            </div>
            <div>
                <a href="/">
                    <img
                        src="{{ asset('images/brand/logo-baruuuu.png') }}"
                        alt="Logo Ular Tangga Statistik"
                        class="h-14 w-14 object-contain drop-shadow-md"
                    >
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg transition-colors duration-200 dark:border dark:border-dark-border dark:bg-dark-surface dark:shadow-slate-800/50">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
