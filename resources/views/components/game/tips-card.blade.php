{{--
    Tips diambil acak dari kolom deskripsi kotak (petak.deskripsi) milik papan
    yang sedang dimainkan — data admin sungguhan, bukan teks karangan. Jika
    admin belum mengisi deskripsi kotak sama sekali, tampilkan panduan umum
    bermain sebagai fallback (jelas bukan klaim data dari database).
--}}
@props(['papan'])

@php
    $deskripsiTersedia = $papan->petak
        ->pluck('deskripsi')
        ->filter(fn ($d) => filled($d))
        ->values();

    $tip = $deskripsiTersedia->isNotEmpty() ? $deskripsiTersedia->random() : null;

    $fallback = 'Jawab soal dengan benar untuk mendapatkan poin ekstra dan naik ke papan lebih cepat!';
@endphp

<div {{ $attributes->merge(['class' => 'animate-fade-in-up flex items-center gap-4 rounded-2xl bg-gradient-to-r from-accent-50 to-amber-50 dark:from-accent-900/20 dark:to-amber-900/20 p-4 shadow-sm sm:p-5']) }}>
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent-500 text-white shadow-sm">
        <x-player.icon name="help" class="h-5 w-5" />
    </span>
    <div class="min-w-0">
        <p class="text-sm font-bold text-accent-700 dark:text-accent-400">Tips</p>
        <p class="mt-0.5 text-sm text-accent-700/90 dark:text-accent-400/90">{{ $tip ?: $fallback }}</p>
    </div>
</div>
