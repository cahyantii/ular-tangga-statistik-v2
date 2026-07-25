{{--
    Banner statistik papan: jumlah kotak/soal/tangga/ular selalu dihitung live
    dari relasi $papan->petak / $papan->papanKonektor (sudah di-eager-load oleh
    GameController::show()) — tidak ada angka hardcode, otomatis menyesuaikan
    ukuran papan berapa pun (50, 100, dst).

    Versi ramping (v2): dulu banner ini tinggi (ilustrasi SVG kota/pohon
    latar belakang) dan mendorong papan jauh ke bawah sebelum terlihat -
    disederhanakan jadi satu baris pil tipis saja, info yang sama tanpa
    ilustrasi, supaya papan permainan lebih cepat terlihat di layar.
--}}
@props(['papan'])

@php
    $jumlahSoal = $papan->petak->where('jenis_petak', \App\Enums\TileType::Soal)->count();
    $jumlahTangga = $papan->papanKonektor->where('jenis', \App\Enums\ConnectorType::Tangga)->count();
    $jumlahUlar = $papan->papanKonektor->where('jenis', \App\Enums\ConnectorType::Ular)->count();
@endphp

<div {{ $attributes->merge(['class' => 'animate-fade-in-up flex flex-wrap items-center gap-2 rounded-2xl bg-white px-3 py-2.5 shadow-sm sm:gap-3 sm:px-4']) }}>
    <span class="inline-flex items-center gap-1.5 rounded-xl bg-primary-50 px-3 py-1.5 text-xs font-bold text-primary-700 sm:text-sm">
        <x-player.icon name="grid" class="h-4 w-4" />
        {{ $papan->jumlah_petak }} Kotak
    </span>
    <span class="inline-flex items-center gap-1.5 rounded-xl bg-primary-50 px-3 py-1.5 text-xs font-bold text-primary-700 sm:text-sm">
        <x-player.icon name="book" class="h-4 w-4" />
        {{ $jumlahSoal }} Soal
    </span>
    <span class="inline-flex items-center gap-1.5 rounded-xl bg-secondary-50 px-3 py-1.5 text-xs font-bold text-secondary-700 sm:text-sm">
        <x-player.icon name="ladder" class="h-4 w-4" />
        {{ $jumlahTangga }} Tangga
    </span>
    <span class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 sm:text-sm">
        <x-player.icon name="snake" class="h-4 w-4" />
        {{ $jumlahUlar }} Ular
    </span>
</div>
