<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/admin-dashboard.js'])
    @endpush

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-slate-600">Ringkasan statistik permainan, soal, dan pemain.</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Total Pemain</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($pemain['total_pemain']) }}</p>
            <p class="mt-1 text-xs text-emerald-600">+{{ $pemain['pemain_baru_7_hari'] }} baru dalam 7 hari terakhir</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Total Permainan</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($permainan['total_sesi']) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $permainan['sesi_selesai'] }} selesai &middot; {{ $permainan['sesi_berlangsung'] }} berlangsung
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Statistik Soal</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($soal['total_soal']) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                soal aktif &middot; rata-rata akurasi {{ $soal['rata_rata_akurasi'] }}%
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-medium text-slate-500">Distribusi Mode</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($permainan['total_vs_robot'] + $permainan['total_multiplayer']) }}</p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $permainan['total_vs_robot'] }} vs robot &middot; {{ $permainan['total_multiplayer'] }} multiplayer
            </p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-700">Permainan Selesai per Hari (7 hari terakhir)</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-games-daily" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100"></div>
                <p id="empty-games-daily" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                    Belum ada data permainan selesai.
                </p>
                <canvas id="chart-games-daily" class="hidden"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-700">Distribusi Mode Permainan</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-mode-distribution" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100"></div>
                <p id="empty-mode-distribution" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                    Belum ada permainan yang selesai.
                </p>
                <canvas id="chart-mode-distribution" class="hidden"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-700">Akurasi Jawaban per Kategori Materi</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-akurasi-kategori" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100"></div>
                <p id="empty-akurasi-kategori" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                    Belum ada jawaban yang tercatat.
                </p>
                <canvas id="chart-akurasi-kategori" class="hidden"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 text-sm font-semibold text-slate-700">10 Soal dengan Tingkat Kesalahan Tertinggi</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-soal-tersulit" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100"></div>
                <p id="empty-soal-tersulit" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                    Belum ada jawaban yang tercatat.
                </p>
                <canvas id="chart-soal-tersulit" class="hidden"></canvas>
            </div>
        </div>
    </div>

    {{-- Leaderboard ringkas --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Leaderboard Ringkas</h2>
            <span class="text-xs text-slate-400">Top 5 pemain berdasarkan total skor</span>
        </div>

        @if (count($leaderboard) === 0)
            <p class="py-6 text-center text-sm text-slate-400">Belum ada permainan yang selesai.</p>
        @else
            <ol class="divide-y divide-slate-100">
                @foreach ($leaderboard as $index => $entry)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-slate-800">{{ $entry['nama'] }}</span>
                        </div>
                        <div class="text-right text-sm text-slate-500">
                            <span class="font-semibold text-slate-900">{{ number_format($entry['total_skor']) }}</span> poin
                            &middot; {{ $entry['total_menang'] }} menang
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <div id="admin-dashboard-data" data-charts="{{ json_encode($chartData) }}"></div>
</x-admin-layout>
