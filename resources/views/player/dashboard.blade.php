<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Halo, {{ auth()->user()->name }}</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Kartu pintasan kontekstual --}}
        @if ($activeSession)
            @php
                // Tahap 12c: sesi Multiplayer yang masih Waiting (mengantre/menunggu
                // lawan) diarahkan ke Waiting Room (kode room, tombol batalkan),
                // bukan ke papan permainan yang belum siap dimainkan.
                $continueUrl = ($activeSession->status->value === 'waiting' && $activeSession->room)
                    ? route('game.room.show', $activeSession->room)
                    : route('game.show', $activeSession);
            @endphp
            <a href="{{ $continueUrl }}"
               class="block rounded-2xl border-2 border-emerald-500 bg-emerald-50 p-6 transition hover:bg-emerald-100">
                <p class="text-sm font-medium text-emerald-700">Sesi permainan sedang berlangsung</p>
                <p class="mt-1 text-xl font-bold text-emerald-900">
                    Lanjutkan Permainan &mdash; {{ $activeSession->mode->label() }}
                </p>
                <p class="mt-1 text-sm text-emerald-700">Status: {{ $activeSession->status->label() }}</p>
            </a>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <form method="POST" action="{{ route('game.robot.store') }}"
                      class="block rounded-2xl border border-emerald-200 bg-white p-6 text-left transition hover:border-emerald-400 hover:bg-emerald-50">
                    @csrf
                    <button type="submit" class="w-full text-left">
                        <p class="text-lg font-bold text-slate-800">Main vs Robot</p>
                        <p class="mt-1 text-sm text-slate-500">Bermain sendiri melawan robot. Papan dipilih acak, giliran robot dijalankan otomatis.</p>
                    </button>
                </form>
                <a href="{{ route('game.multiplayer.lobby') }}"
                   class="block rounded-2xl border border-emerald-200 bg-white p-6 text-left transition hover:border-emerald-400 hover:bg-emerald-50">
                    <p class="text-lg font-bold text-slate-800">Main Multiplayer</p>
                    <p class="mt-1 text-sm text-slate-500">Quick Match otomatis atau buat/gabung Private Room dengan teman.</p>
                </a>
            </div>
        @endif

        {{-- Statistik pribadi --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Total Main</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total_main'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Total Menang</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['total_menang'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Skor Tertinggi</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['skor_tertinggi'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Rata-rata Skor</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $stats['rata_rata_skor'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500">Total Waktu Main</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ intdiv($stats['total_waktu_detik'], 60) }} menit</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Progress belajar --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Progress Belajar per Kategori</h3>
                    <a href="{{ route('player.progress') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Lihat detail &rarr;</a>
                </div>

                @if ($progressPerKategori->isEmpty())
                    <p class="text-sm text-slate-400">Belum ada kategori materi.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($progressPerKategori as $item)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="font-medium text-slate-700">{{ $item['kategori'] }}</span>
                                    <span class="text-slate-500">
                                        {{ $item['total_dijawab'] > 0 ? $item['akurasi'].'% akurasi' : 'Belum dimainkan' }}
                                    </span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $item['akurasi'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Achievement teaser --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Achievement</h3>
                    <a href="{{ route('player.achievements') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Lihat semua &rarr;</a>
                </div>
                <p class="mb-4 text-sm text-slate-500">{{ $totalAchievementDiraih }} achievement telah diraih.</p>

                @if ($nearestAchievement)
                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-800">{{ $nearestAchievement['achievement']->nama }}</p>
                        <p class="mt-1 text-xs text-amber-700">
                            {{ $nearestAchievement['current'] }} / {{ $nearestAchievement['target'] }}
                            ({{ $nearestAchievement['achievement']->syarat_type->label() }})
                        </p>
                        <div class="mt-2 h-2 w-full rounded-full bg-amber-100">
                            <div class="h-2 rounded-full bg-amber-500"
                                 style="width: {{ min(100, (int) round($nearestAchievement['current'] / max($nearestAchievement['target'], 1) * 100)) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Semua achievement aktif telah diraih. Selamat!</p>
                @endif
            </div>
        </div>

        {{-- Sertifikat widget --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Sertifikat Digital</h3>
                <a href="{{ route('player.certificate') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Buka halaman Sertifikat &rarr;</a>
            </div>

            @if ($sudahPunyaSertifikat)
                <p class="text-sm text-emerald-700">Anda sudah meraih sertifikat digital. Lihat di halaman Sertifikat.</p>
            @else
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <span class="{{ $sertifikatChecklist['kategori_selesai_terpenuhi'] ? 'text-emerald-600' : 'text-slate-300' }}">&#10003;</span>
                        <span class="text-slate-700">
                            Menyelesaikan semua kategori materi ({{ $sertifikatChecklist['kategori_selesai_label'] }})
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="{{ $sertifikatChecklist['akurasi_terpenuhi'] ? 'text-emerald-600' : 'text-slate-300' }}">&#10003;</span>
                        <span class="text-slate-700">
                            Akurasi keseluruhan &ge; 80% (saat ini {{ $sertifikatChecklist['akurasi'] }}%)
                        </span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="{{ $sertifikatChecklist['sudah_main_terpenuhi'] ? 'text-emerald-600' : 'text-slate-300' }}">&#10003;</span>
                        <span class="text-slate-700">Menyelesaikan minimal 1 permainan penuh</span>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</x-player-layout>
