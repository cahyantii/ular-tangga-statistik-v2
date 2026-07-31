@props([
    'sudahPunya',
    'checklist',
])

@php
    $items = [
        [
            'label' => "Menyelesaikan semua kategori materi ({$checklist['kategori_selesai_label']})",
            'done' => $checklist['kategori_selesai_terpenuhi'],
        ],
        [
            'label' => "Akurasi keseluruhan \u{2265} 80% (saat ini {$checklist['akurasi']}%)",
            'done' => $checklist['akurasi_terpenuhi'],
        ],
        [
            'label' => 'Menyelesaikan minimal 1 permainan penuh',
            'done' => $checklist['sudah_main_terpenuhi'],
        ],
    ];
@endphp

<div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft dark:bg-slate-800 sm:p-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-slate-200">
            <x-player.icon name="certificate" class="h-5 w-5 text-primary-500" />
            Sertifikat Digital
        </h3>
        <a href="{{ route('player.certificate') }}" class="text-xs font-semibold text-primary-500 transition hover:text-primary-600 dark:text-primary-400 dark:hover:text-primary-300">
            Buka halaman Sertifikat &rarr;
        </a>
    </div>

    <div class="grid grid-cols-1 items-center gap-6 sm:grid-cols-5">
        <div class="sm:col-span-3">
            @if ($sudahPunya)
                <p class="mb-3 text-sm font-medium text-secondary-600 dark:text-secondary-400">
                    Selamat! Anda sudah meraih sertifikat digital.
                </p>
            @endif

            <ul class="space-y-2.5 text-sm">
                @foreach ($items as $item)
                    <li class="flex items-center gap-2.5">
                        <x-player.icon name="check-circle" class="h-5 w-5 shrink-0 {{ $item['done'] ? 'text-secondary-500' : 'text-slate-300 dark:text-slate-600' }}" />
                        <span class="text-slate-600 dark:text-slate-300">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>

            <button
                @click="$dispatch('open-modal', 'sertifikat-preview')"
                class="mt-4 inline-flex items-center gap-2 rounded-xl border border-primary-100 bg-primary-50 px-4 py-2 text-sm font-semibold text-primary-600 transition hover:bg-primary-100 dark:border-primary-800/40 dark:bg-primary-950/40 dark:text-primary-300 dark:hover:bg-primary-900/50"
            >
                <x-player.icon name="eye" class="h-4 w-4" />
                Lihat Preview
            </button>
        </div>

        <div class="sm:col-span-2">
            <svg viewBox="0 0 160 120" class="mx-auto w-40 drop-shadow-md sm:w-full">
                <rect x="10" y="10" width="140" height="90" rx="8" fill="#F6FAFF" stroke="#0F4CBA" stroke-width="2" />
                <rect x="20" y="22" width="120" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.3" />
                <rect x="20" y="34" width="80" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.2" />
                <rect x="20" y="44" width="90" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.2" />
                <circle cx="120" cy="70" r="16" fill="#F68B1F" />
                <path d="M112 82l-4 16 12-6 12 6-4-16" fill="#F68B1F" />
                <path d="M114 70l4 4 8-8" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                <rect x="20" y="80" width="50" height="4" rx="2" fill="#00A65A" fill-opacity="0.5" />
            </svg>
        </div>
    </div>

    <x-player.modal name="sertifikat-preview">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary-500">Pratinjau Sertifikat</p>
            <svg viewBox="0 0 320 220" class="mx-auto mt-4 w-full max-w-sm">
                <rect x="6" y="6" width="308" height="208" rx="14" fill="#F6FAFF" stroke="#0F4CBA" stroke-width="3" />
                <rect x="18" y="18" width="284" height="184" rx="8" fill="none" stroke="#F68B1F" stroke-width="1.5" stroke-dasharray="6 6" />
                <text x="160" y="55" text-anchor="middle" font-size="13" font-weight="700" fill="#0A317A">SERTIFIKAT PENGUASAAN</text>
                <text x="160" y="72" text-anchor="middle" font-size="13" font-weight="700" fill="#0A317A">STATISTIK DASAR</text>
                <text x="160" y="105" text-anchor="middle" font-size="11" fill="#475569">diberikan kepada</text>
                <text x="160" y="130" text-anchor="middle" font-size="16" font-weight="700" fill="#0F4CBA">{{ auth()->user()->name }}</text>
                <line x1="110" y1="140" x2="210" y2="140" stroke="#0F4CBA" stroke-width="1" />
                <circle cx="160" cy="172" r="20" fill="#F68B1F" />
                <path d="M152 172l6 6 12-12" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            </svg>
            <p class="mt-4 text-sm text-slate-500">
                Selesaikan seluruh syarat di samping untuk mendapatkan sertifikat asli beserta nomor &amp; kode verifikasinya.
            </p>
        </div>
    </x-player.modal>
</div>
