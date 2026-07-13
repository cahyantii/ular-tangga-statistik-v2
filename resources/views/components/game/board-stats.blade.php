{{--
    Banner statistik papan: jumlah kotak/soal/tangga/ular selalu dihitung live
    dari relasi $papan->petak / $papan->papanKonektor (sudah di-eager-load oleh
    GameController::show()) — tidak ada angka hardcode, otomatis menyesuaikan
    ukuran papan berapa pun (50, 100, dst).
--}}
@props(['papan'])

@php
    $jumlahSoal = $papan->petak->where('jenis_petak', \App\Enums\TileType::Soal)->count();
    $jumlahTangga = $papan->papanKonektor->where('jenis', \App\Enums\ConnectorType::Tangga)->count();
    $jumlahUlar = $papan->papanKonektor->where('jenis', \App\Enums\ConnectorType::Ular)->count();
@endphp

<div class="animate-fade-in-up relative overflow-hidden rounded-3xl bg-gradient-to-b from-secondary-100 via-secondary-50 to-white p-4 shadow-sm sm:p-6">
    <svg viewBox="0 0 1000 200" preserveAspectRatio="none" class="pointer-events-none absolute inset-0 h-full w-full select-none" aria-hidden="true">
        <ellipse cx="120" cy="40" rx="70" ry="20" fill="#ffffff" opacity="0.6" />
        <ellipse cx="180" cy="30" rx="46" ry="16" fill="#ffffff" opacity="0.5" />
        <ellipse cx="860" cy="35" rx="60" ry="18" fill="#ffffff" opacity="0.55" />

        <path d="M0 200V150c120-30 220 20 340 5s180-55 300-40 220 45 360 20V200Z" fill="#BBF7D0" opacity="0.7" />
        <path d="M0 200V175c160-20 260 10 400 0s220-35 330-20 200 25 270 10V200Z" fill="#86EFAC" opacity="0.55" />

        <g transform="translate(600 60)" opacity="0.9">
            <rect x="0" y="55" width="80" height="55" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2.5" />
            <rect x="-14" y="35" width="20" height="75" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2.5" />
            <rect x="74" y="35" width="20" height="75" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="2.5" />
            <path d="M-14 35 -4 18l10 17Z" fill="#3E75D1" stroke="#0F4CBA" stroke-width="2" />
            <path d="M74 35l10-17 10 17Z" fill="#3E75D1" stroke="#0F4CBA" stroke-width="2" />
            <rect x="30" y="70" width="20" height="40" fill="#0F4CBA" fill-opacity="0.15" stroke="#0F4CBA" stroke-width="2" />
            <line x1="-4" y1="18" x2="-4" y2="4" stroke="#0F4CBA" stroke-width="2" />
            <path d="M-4 4h12l-12 8Z" fill="#3E75D1" />
        </g>

        <g transform="translate(730 100)" opacity="0.9">
            <path d="M-7 0h14v9a7 7 0 0 1-14 0V0Z" fill="#FCD34D" stroke="#F59E0B" stroke-width="1.8" />
            <path d="M-7 1.5h-6v2.5a6 6 0 0 0 6 6M7 1.5h6v2.5a6 6 0 0 1-6 6" stroke="#F59E0B" stroke-width="1.8" fill="none" />
            <rect x="-3" y="15" width="6" height="8" fill="#F59E0B" />
            <path d="M-7 23h14l-2 7h-10Z" fill="#F59E0B" />
        </g>

        <g transform="translate(430 130)" opacity="0.85">
            <rect x="-3" y="10" width="6" height="30" fill="#7C4A26" />
            <circle cx="0" cy="0" r="22" fill="#22C55E" />
        </g>
        <g transform="translate(470 145)" opacity="0.85">
            <rect x="-2.5" y="8" width="5" height="22" fill="#7C4A26" />
            <circle cx="0" cy="0" r="16" fill="#16A34A" />
        </g>
    </svg>

    <div class="relative flex flex-wrap items-center gap-2 sm:gap-3">
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm sm:px-4 sm:py-2.5 sm:text-sm">
            <x-player.icon name="grid" class="h-4 w-4 text-primary-500" />
            {{ $papan->jumlah_petak }} Kotak
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm sm:px-4 sm:py-2.5 sm:text-sm">
            <x-player.icon name="book" class="h-4 w-4 text-primary-500" />
            {{ $jumlahSoal }} Soal
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm sm:px-4 sm:py-2.5 sm:text-sm">
            <x-player.icon name="ladder" class="h-4 w-4 text-secondary-600" />
            {{ $jumlahTangga }} Tangga
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm sm:px-4 sm:py-2.5 sm:text-sm">
            <x-player.icon name="snake" class="h-4 w-4 text-rose-600" />
            {{ $jumlahUlar }} Ular
        </span>
    </div>
</div>
