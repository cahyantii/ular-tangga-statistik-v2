{{--
    Swatch bulat untuk memilih warna pion (dipakai di form Vs Robot, lihat
    robot-hero.blade.php) — radio input disembunyikan (sr-only), lingkaran
    warna sebagai label-nya, ring muncul di swatch yang sedang terpilih lewat
    `peer-checked`. Nilai radio LANGSUNG kode hex (App\Enums\PawnColor) yang
    dikirim apa adanya ke server sebagai `pawn_color`.
--}}
@props(['name' => 'pawn_color', 'selected' => null, 'dark' => true])
@php
    $selectedValue = $selected ?? \App\Enums\PawnColor::Biru->value;
    // `dark`: true = dipasang di atas latar gelap (mis. robot-hero), swatch
    // butuh border/ring PUTIH supaya kontras. false = latar terang/kartu
    // putih (mis. lobby multiplayer), border/ring perlu warna GELAP supaya
    // tetap terlihat (ring putih di atas putih = tidak kelihatan).
    $ringClass = $dark
        ? 'border-white/70 peer-checked:ring-white peer-focus-visible:ring-white'
        : 'border-slate-200 dark:border-dark-border peer-checked:ring-slate-800 dark:peer-checked:ring-white peer-focus-visible:ring-slate-800 dark:peer-focus-visible:ring-white';
@endphp

<div class="flex flex-wrap gap-2.5" role="radiogroup" aria-label="Pilih warna pion">
    @foreach (\App\Enums\PawnColor::cases() as $color)
        <label class="cursor-pointer">
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $color->value }}"
                class="peer sr-only"
                {{ $color->value === $selectedValue ? 'checked' : '' }}
            >
            <span
                class="block h-8 w-8 rounded-full border-2 {{ $ringClass }} shadow-sm transition-all duration-150 peer-checked:scale-110 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-offset-transparent"
                style="background-color: {{ $color->value }}"
                title="{{ $color->label() }}"
            ></span>
            <span class="sr-only">{{ $color->label() }}</span>
        </label>
    @endforeach
</div>
