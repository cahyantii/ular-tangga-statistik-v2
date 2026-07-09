<x-player-layout>
    <div class="space-y-6">
        {{-- Hero: sapaan + ilustrasi dekoratif papan ular tangga --}}
        <div class="relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm sm:p-8">
            <div class="relative z-10 max-w-md">
                <h1 class="text-2xl font-bold text-slate-800 sm:text-3xl">
                    Halo, {{ auth()->user()->name }} <span class="inline-block animate-float">&#128075;</span>
                </h1>
                <p class="mt-2 text-slate-500">Siap bermain dan belajar hari ini?</p>
            </div>

            <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-[420px] select-none lg:block" aria-hidden="true">
                <svg viewBox="0 0 420 200" class="h-full w-full">
                    <circle cx="70" cy="35" r="16" fill="#EAF1FC" />
                    <circle cx="95" cy="30" r="12" fill="#EAF1FC" />
                    <circle cx="230" cy="24" r="14" fill="#EAF1FC" />
                    <circle cx="252" cy="30" r="10" fill="#EAF1FC" />

                    <path d="M0 200V140c40-30 90-30 130-10s90 10 130-14 100-16 160 6V200Z" fill="#E5F8EE" />
                    <path d="M0 200V165c60-18 120-6 170 8s110 4 160-14 60-8 90 2V200Z" fill="#CCF1DD" />

                    <g transform="translate(300 40)">
                        <path d="M18 62V10M18 10l-9 7M18 10l9 7" stroke="#F68B1F" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <path d="M2 62h32l-6 14H8Z" fill="#F68B1F" />
                        <circle cx="18" cy="0" r="7" fill="#F6E27A" stroke="#F68B1F" stroke-width="2" />
                    </g>

                    <g transform="translate(150 118)">
                        <line x1="0" y1="0" x2="0" y2="46" stroke="#0F4CBA" stroke-width="4" stroke-linecap="round" />
                        <line x1="22" y1="0" x2="22" y2="46" stroke="#0F4CBA" stroke-width="4" stroke-linecap="round" />
                        <line x1="0" y1="8" x2="22" y2="8" stroke="#0F4CBA" stroke-width="3" />
                        <line x1="0" y1="20" x2="22" y2="20" stroke="#0F4CBA" stroke-width="3" />
                        <line x1="0" y1="32" x2="22" y2="32" stroke="#0F4CBA" stroke-width="3" />
                        <line x1="0" y1="44" x2="22" y2="44" stroke="#0F4CBA" stroke-width="3" />
                    </g>

                    <g class="animate-float" transform="translate(40 120)">
                        <path d="M0 46c0-24 40-24 40-48 0-12-12-15-21-9" stroke="#00A65A" stroke-width="9" stroke-linecap="round" fill="none" />
                        <circle cx="21" cy="-13" r="7" fill="#00A65A" />
                        <circle cx="19" cy="-15" r="1.4" fill="#0A317A" />
                        <path d="M14 -10c1.8 2 5 2 6.8 0" stroke="#0A317A" stroke-width="1.4" stroke-linecap="round" fill="none" />
                    </g>

                    <g transform="translate(230 150)">
                        <rect x="0" y="0" width="26" height="26" rx="6" fill="#ffffff" stroke="#0F4CBA" stroke-width="2" transform="rotate(-10 13 13)" />
                        <circle cx="8" cy="9" r="2" fill="#0F4CBA" transform="rotate(-10 13 13)" />
                        <circle cx="18" cy="13" r="2" fill="#0F4CBA" transform="rotate(-10 13 13)" />
                        <circle cx="8" cy="18" r="2" fill="#0F4CBA" transform="rotate(-10 13 13)" />
                    </g>
                </svg>
            </div>
        </div>

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
               class="block rounded-2xl border-2 border-secondary-400 bg-secondary-50 p-6 transition-all duration-300 hover:-translate-y-0.5 hover:bg-secondary-100 hover:shadow-soft">
                <p class="text-sm font-medium text-secondary-600">Sesi permainan sedang berlangsung</p>
                <p class="mt-1 text-xl font-bold text-secondary-700">
                    Lanjutkan Permainan &mdash; {{ $activeSession->mode->label() }}
                </p>
                <p class="mt-1 text-sm text-secondary-600">Status: {{ $activeSession->status->label() }}</p>
            </a>
        @else
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <x-player.game-card
                    variant="robot"
                    title="Main vs Robot"
                    description="Bermain sendiri melawan robot. Papan dipilih acak, giliran robot dijalankan otomatis."
                    ctaLabel="Mulai Bermain"
                    :href="route('game.robot.store')"
                    method="post"
                />

                <x-player.game-card
                    variant="multiplayer"
                    title="Main Multiplayer"
                    description="Quick Match otomatis atau buat/gabung Private Room dengan teman."
                    ctaLabel="Main Sekarang"
                    :href="route('game.multiplayer.lobby')"
                />
            </div>
        @endif

        {{-- Statistik pribadi --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <x-player.stat-card icon="gamepad" label="Total Main" :value="$stats['total_main']" color="blue" :delay="0" />
            <x-player.stat-card icon="trophy" label="Total Menang" :value="$stats['total_menang']" color="green" :delay="75" />
            <x-player.stat-card icon="star" label="Skor Tertinggi" :value="$stats['skor_tertinggi']" color="amber" :delay="150" />
            <x-player.stat-card icon="chart-bar" label="Rata-rata Skor" :value="$stats['rata_rata_skor']" color="purple" :delay="225" />
            <x-player.stat-card icon="clock" label="Total Waktu Main" :value="intdiv($stats['total_waktu_detik'], 60).' menit'" color="rose" :delay="300" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Progress belajar --}}
            <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6 lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700">
                        <x-player.icon name="chart-bar" class="h-5 w-5 text-primary-500" />
                        Progress Belajar per Kategori
                    </h3>
                    <a href="{{ route('player.progress') }}" class="text-xs font-semibold text-primary-500 transition hover:text-primary-600">Lihat detail &rarr;</a>
                </div>

                @if ($progressPerKategori->isEmpty())
                    <p class="text-sm text-slate-400">Belum ada kategori materi.</p>
                @else
                    <div class="space-y-5">
                        @foreach ($progressPerKategori as $item)
                            <x-player.progress-card
                                :kategori="$item['kategori']"
                                :totalDijawab="$item['total_dijawab']"
                                :akurasi="$item['akurasi']"
                            />
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Achievement teaser --}}
            <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700">
                        <x-player.icon name="trophy" class="h-5 w-5 text-accent-500" />
                        Achievement
                    </h3>
                    <a href="{{ route('player.achievements') }}" class="text-xs font-semibold text-primary-500 transition hover:text-primary-600">Lihat semua &rarr;</a>
                </div>
                <p class="mb-4 text-sm text-slate-500">{{ $totalAchievementDiraih }} achievement telah diraih.</p>

                @if ($nearestAchievement)
                    <div class="rounded-2xl bg-accent-50 p-4">
                        <p class="text-sm font-semibold text-accent-600">{{ $nearestAchievement['achievement']->nama }}</p>
                        <p class="mt-1 text-xs text-accent-600/80">
                            {{ $nearestAchievement['current'] }} / {{ $nearestAchievement['target'] }}
                            ({{ $nearestAchievement['achievement']->syarat_type->label() }})
                        </p>
                        <div class="mt-2.5 h-2.5 w-full rounded-full bg-accent-100">
                            <div class="h-2.5 rounded-full bg-accent-500 transition-all duration-700 ease-out"
                                 style="width: {{ min(100, (int) round($nearestAchievement['current'] / max($nearestAchievement['target'], 1) * 100)) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Semua achievement aktif telah diraih. Selamat!</p>
                @endif
            </div>
        </div>

        {{-- Sertifikat widget --}}
        <x-player.certificate-card :sudahPunya="$sudahPunyaSertifikat" :checklist="$sertifikatChecklist" />
    </div>
</x-player-layout>
