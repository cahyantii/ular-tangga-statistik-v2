<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Sertifikat Digital</h2>
    </x-slot>

    @if ($certificate)
        <div class="mx-auto max-w-lg rounded-3xl border-2 border-secondary-400 bg-secondary-50 p-6 text-center shadow-sm sm:p-8">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-secondary-500 text-white">
                <x-player.icon name="certificate" class="h-7 w-7" />
            </span>
            <p class="mt-3 text-sm font-semibold uppercase tracking-wide text-secondary-600">Selamat!</p>
            <p class="mt-2 text-xl font-bold text-secondary-700">{{ $certificate->judul }}</p>
            <p class="mt-1 text-sm text-slate-600">Nomor: {{ $certificate->nomor_sertifikat }}</p>
            <p class="text-sm text-slate-600">Terbit: {{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
            <p class="mt-1 text-xs text-slate-500">Kode verifikasi: {{ $certificate->verification_code }}</p>

            <x-player.button variant="secondary" :href="route('player.certificate.download', $certificate)" class="mt-5">
                <x-player.icon name="download" class="h-4 w-4" />
                Unduh PDF
            </x-player.button>
        </div>
    @else
        <div class="mx-auto max-w-lg rounded-3xl bg-white p-6 shadow-sm sm:p-8">
            <p class="text-sm text-slate-600">
                Selesaikan tiga syarat berikut untuk mendapatkan Sertifikat Penguasaan Statistik Dasar:
            </p>

            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3">
                    <span class="text-slate-600">Semua kategori materi selesai</span>
                    <span class="font-semibold {{ $checklist['kategori_selesai_terpenuhi'] ? 'text-secondary-600' : 'text-slate-400' }}">
                        {{ $checklist['kategori_selesai_label'] }}
                    </span>
                </li>
                <li class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3">
                    <span class="text-slate-600">Akurasi keseluruhan &ge;80%</span>
                    <span class="font-semibold {{ $checklist['akurasi_terpenuhi'] ? 'text-secondary-600' : 'text-slate-400' }}">
                        {{ $checklist['akurasi'] }}%
                    </span>
                </li>
                <li class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3">
                    <span class="text-slate-600">Minimal 1 permainan selesai</span>
                    <span class="font-semibold {{ $checklist['sudah_main_terpenuhi'] ? 'text-secondary-600' : 'text-slate-400' }}">
                        {{ $checklist['sudah_main_terpenuhi'] ? 'Terpenuhi' : 'Belum' }}
                    </span>
                </li>
            </ul>
        </div>
    @endif
</x-player-layout>
