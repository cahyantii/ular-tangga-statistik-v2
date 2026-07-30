<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/admin-dashboard.js'])
    @endpush

    @php
        $totalSelesaiMinggu = collect($chartData['games_daily']['values'])->sum();
        $rataRataPerHari = round($totalSelesaiMinggu / max(count($chartData['games_daily']['values']), 1), 2);
        $totalMode = $permainan['total_vs_robot'] + $permainan['total_multiplayer'];
        $persenVsRobot = $totalMode > 0 ? round(($permainan['total_vs_robot'] / $totalMode) * 100, 1) : 0.0;
        $persenMultiplayer = $totalMode > 0 ? round(($permainan['total_multiplayer'] / $totalMode) * 100, 1) : 0.0;
    @endphp

    {{-- Hero banner --}}
    <div class="relative mb-6 overflow-hidden rounded-[32px] shadow-sm">
        <div
            class="absolute inset-0 bg-cover bg-center"
            style="background-image: url('{{ asset('images/brand/logo-back.png') }}');"
            aria-hidden="true"
        ></div>
        <div
            class="absolute inset-0"
            style="background: linear-gradient(90deg, rgba(255,255,255,.95) 0%, rgba(255,255,255,.75) 40%, rgba(255,255,255,.15) 100%);"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 flex min-h-[240px] flex-col gap-5 p-6 sm:min-h-[260px] sm:flex-row sm:items-start sm:justify-between sm:p-8 lg:min-h-[300px] lg:p-10">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Selamat datang, {{ auth()->user()->name }}! 👋</h1>
                <p class="mt-2 max-w-md text-slate-600">Berikut ringkasan statistik permainan, soal, dan pemain.</p>
            </div>

            <form
                method="GET"
                action="{{ route('admin.dashboard') }}"
                x-data="{ open: false }"
                @click.outside="open = false"
                class="relative shrink-0"
            >
                <button
                    type="button"
                    @click="open = !open"
                    class="flex items-center gap-2 rounded-xl border border-admin-border bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                >
                    <x-player.icon name="calendar" class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                    {{ $periode['mulai']->translatedFormat('j M Y') }} - {{ $periode['selesai']->translatedFormat('j M Y') }}
                    <x-player.icon name="chevron-down" class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition
                    class="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-admin-border bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800"
                >
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">
                            Tanggal Mulai
                            <input
                                type="date"
                                name="from"
                                value="{{ $periode['mulai']->toDateString() }}"
                                max="{{ now()->toDateString() }}"
                                class="mt-1 block w-full rounded-lg border-admin-border text-sm text-slate-700 focus:border-admin-green focus:ring-admin-green dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            >
                        </label>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400">
                            Tanggal Selesai
                            <input
                                type="date"
                                name="to"
                                value="{{ $periode['selesai']->toDateString() }}"
                                max="{{ now()->toDateString() }}"
                                class="mt-1 block w-full rounded-lg border-admin-border text-sm text-slate-700 focus:border-admin-green focus:ring-admin-green dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            >
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="mt-3 w-full rounded-lg bg-admin-green px-3 py-2 text-sm font-semibold text-white transition hover:opacity-90"
                    >
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <x-player.icon name="users" class="pointer-events-none absolute -bottom-3 -right-3 h-20 w-20 text-emerald-500 opacity-10" />
            <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                <x-player.icon name="users" class="h-5 w-5" />
            </div>
            <p class="relative mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">Total Pemain</p>
            <p class="relative mt-1 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($pemain['total_pemain'], 0, ',', '.') }}</p>
            <p class="relative mt-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">↑ {{ $pemain['pemain_baru_7_hari'] }} baru dalam 7 hari terakhir</p>
        </div>

        <div class="relative overflow-hidden rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <x-player.icon name="gamepad" class="pointer-events-none absolute -bottom-3 -right-3 h-20 w-20 text-blue-500 opacity-10" />
            <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                <x-player.icon name="gamepad" class="h-5 w-5" />
            </div>
            <p class="relative mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">Total Permainan</p>
            <p class="relative mt-1 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($permainan['total_sesi'], 0, ',', '.') }}</p>
            <p class="relative mt-1 text-xs text-slate-500 dark:text-slate-400">
                <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $permainan['sesi_selesai'] }} selesai</span>
                &middot; {{ $permainan['sesi_berlangsung'] }} berlangsung
            </p>
        </div>

        <div class="relative overflow-hidden rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <x-player.icon name="document" class="pointer-events-none absolute -bottom-3 -right-3 h-20 w-20 text-violet-500 opacity-10" />
            <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400">
                <x-player.icon name="document" class="h-5 w-5" />
            </div>
            <p class="relative mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">Statistik Soal</p>
            <p class="relative mt-1 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($soal['total_soal'], 0, ',', '.') }}</p>
            <p class="relative mt-1 text-xs text-slate-500 dark:text-slate-400">
                soal aktif &middot; rata-rata akurasi <span class="font-medium text-violet-600 dark:text-violet-400">{{ $soal['rata_rata_akurasi'] }}%</span>
            </p>
        </div>

        <div class="relative overflow-hidden rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <x-player.icon name="users" class="pointer-events-none absolute -bottom-3 -right-3 h-20 w-20 text-orange-500 opacity-10" />
            <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-orange-50 text-orange-600 dark:bg-orange-500/15 dark:text-orange-400">
                <x-player.icon name="users" class="h-5 w-5" />
            </div>
            <p class="relative mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">Distribusi Mode</p>
            <p class="relative mt-1 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalMode, 0, ',', '.') }}</p>
            <p class="relative mt-1 text-xs text-slate-500 dark:text-slate-400">
                <span class="font-medium text-orange-600 dark:text-orange-400">{{ $permainan['total_vs_robot'] }} vs robot</span>
                &middot; {{ $permainan['total_multiplayer'] }} multiplayer
            </p>
        </div>
    </div>

    {{-- Line chart + Donut chart --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Permainan Selesai per Hari ({{ $hariPeriode }} hari terakhir)</h2>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <x-player.icon name="trend-up" class="h-4 w-4" />
                </span>
            </div>
            <div class="relative h-64">
                <div id="skeleton-chart-games-daily" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-700"></div>
                <p id="empty-games-daily" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                    Belum ada data permainan selesai.
                </p>
                <canvas id="chart-games-daily" class="hidden"></canvas>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-4 border-t border-admin-border pt-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                        <x-player.icon name="check-circle" class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Selesai</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalSelesaiMinggu, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                        <x-player.icon name="clock" class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Rata-rata per Hari</p>
                        <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($rataRataPerHari, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Distribusi Mode Permainan</h2>

            <div class="flex flex-col items-center gap-6 sm:flex-row lg:flex-col">
                <div class="relative h-40 w-40 shrink-0">
                    <div id="skeleton-chart-mode-distribution" class="absolute inset-0 animate-pulse rounded-full bg-slate-100 dark:bg-slate-700"></div>
                    <p id="empty-mode-distribution" class="hidden absolute inset-0 flex items-center justify-center text-center text-xs text-slate-400 dark:text-slate-500">
                        Belum ada permainan yang selesai.
                    </p>
                    <canvas id="chart-mode-distribution" class="hidden"></canvas>
                    @if ($totalMode > 0)
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalMode, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Total Permainan</span>
                        </div>
                    @endif
                </div>

                <div class="w-full space-y-3">
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-admin-green"></span>
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Melawan Robot (AI)</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $permainan['total_vs_robot'] }} ({{ $persenVsRobot }}%)</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-600"></span>
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Multiplayer</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $permainan['total_multiplayer'] }} ({{ $persenMultiplayer }}%)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Achievement terbaru --}}
    <div class="mt-6 rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <x-player.icon name="trophy" class="h-5 w-5" />
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Achievement Terbaru</h2>
                    @if (count($achievementTerbaru) === 0)
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Belum ada achievement yang diraih pemain dalam {{ $hariPeriode }} hari terakhir.</p>
                    @else
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ count($achievementTerbaru) }} achievement diraih pemain dalam {{ $hariPeriode }} hari terakhir.</p>
                    @endif
                </div>
            </div>

            <a
                href="{{ route('admin.management.achievements.index') }}"
                class="inline-flex shrink-0 items-center gap-1.5 self-start rounded-full border border-admin-green px-4 py-2 text-sm font-medium text-admin-green hover:bg-emerald-50 dark:hover:bg-emerald-500/15 sm:self-auto"
            >
                Lihat Semua
                <x-player.icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        @if (count($achievementTerbaru) > 0)
            <ol class="mt-4 divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-700 dark:border-slate-700">
                @foreach ($achievementTerbaru as $entry)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                                <x-player.icon name="star" class="h-4 w-4" />
                            </span>
                            <p class="text-sm text-slate-700 dark:text-slate-300">
                                <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $entry['nama_pemain'] }}</span>
                                meraih <span class="font-medium">{{ $entry['nama_achievement'] }}</span>
                            </p>
                        </div>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $entry['earned_at']->translatedFormat('j M, H:i') }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- Statistik tambahan --}}
    <h2 class="mb-4 mt-10 text-lg font-bold text-slate-900 dark:text-slate-100">Statistik Tambahan</h2>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">Akurasi Jawaban per Kategori Materi</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-akurasi-kategori" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-700"></div>
                <p id="empty-akurasi-kategori" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                    Belum ada jawaban yang tercatat.
                </p>
                <canvas id="chart-akurasi-kategori" class="hidden"></canvas>
            </div>
        </div>

        <div class="rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h2 class="mb-4 text-sm font-semibold text-slate-700 dark:text-slate-200">10 Soal dengan Tingkat Kesalahan Tertinggi</h2>
            <div class="relative h-64">
                <div id="skeleton-chart-soal-tersulit" class="absolute inset-0 animate-pulse rounded-lg bg-slate-100 dark:bg-slate-700"></div>
                <p id="empty-soal-tersulit" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                    Belum ada jawaban yang tercatat.
                </p>
                <canvas id="chart-soal-tersulit" class="hidden"></canvas>
            </div>
        </div>
    </div>

    {{-- Leaderboard ringkas --}}
    <div class="mt-6 rounded-[20px] border border-admin-border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Leaderboard Ringkas</h2>
            <span class="text-xs text-slate-400 dark:text-slate-500">Top 5 pemain berdasarkan total skor</span>
        </div>

        @if (count($leaderboard) === 0)
            <p class="py-6 text-center text-sm text-slate-400 dark:text-slate-500">Belum ada permainan yang selesai.</p>
        @else
            <ol class="divide-y divide-slate-100 dark:divide-slate-700">
                @foreach ($leaderboard as $index => $entry)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $entry['nama'] }}</span>
                        </div>
                        <div class="text-right text-sm text-slate-500 dark:text-slate-400">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($entry['total_skor']) }}</span> poin
                            &middot; {{ $entry['total_menang'] }} menang
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <div id="admin-dashboard-data" data-charts="{{ json_encode($chartData) }}"></div>
</x-admin-layout>
