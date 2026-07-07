<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Sertifikat Digital</h2>
    </x-slot>

    @if ($certificate)
        <div class="mx-auto max-w-lg rounded-2xl border-2 border-emerald-500 bg-emerald-50 p-6 text-center">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Selamat!</p>
            <p class="mt-2 text-xl font-bold text-emerald-900">{{ $certificate->judul }}</p>
            <p class="mt-1 text-sm text-slate-600">Nomor: {{ $certificate->nomor_sertifikat }}</p>
            <p class="text-sm text-slate-600">Terbit: {{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
            <p class="mt-1 text-xs text-slate-500">Kode verifikasi: {{ $certificate->verification_code }}</p>

            <a href="{{ route('player.certificate.download', $certificate) }}"
               class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Unduh PDF
            </a>
        </div>
    @else
        <div class="mx-auto max-w-lg rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-sm text-slate-600">
                Selesaikan tiga syarat berikut untuk mendapatkan Sertifikat Penguasaan Statistik Dasar:
            </p>

            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                    <span>Semua kategori materi selesai</span>
                    <span class="font-semibold {{ $checklist['kategori_selesai_terpenuhi'] ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $checklist['kategori_selesai_label'] }}
                    </span>
                </li>
                <li class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                    <span>Akurasi keseluruhan &ge;80%</span>
                    <span class="font-semibold {{ $checklist['akurasi_terpenuhi'] ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $checklist['akurasi'] }}%
                    </span>
                </li>
                <li class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                    <span>Minimal 1 permainan selesai</span>
                    <span class="font-semibold {{ $checklist['sudah_main_terpenuhi'] ? 'text-emerald-600' : 'text-slate-400' }}">
                        {{ $checklist['sudah_main_terpenuhi'] ? 'Terpenuhi' : 'Belum' }}
                    </span>
                </li>
            </ul>
        </div>
    @endif
</x-player-layout>
