<x-player-layout>
    <div class="space-y-6">
        {{-- Hero: sapaan + logo-dhas.png sebagai background penuh (cover) di belakang teks --}}
        <div
            class="relative min-h-[170px] overflow-hidden rounded-[28px] bg-white p-6 shadow-sm dark:bg-slate-800 sm:p-8"
            style="background-image: linear-gradient(90deg, rgba(255,255,255,0.97) 0%, rgba(255,255,255,0.92) 25%, rgba(255,255,255,0.55) 50%, rgba(255,255,255,0) 72%), url('{{ asset('images/brand/logo-dhas.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
        >
            <div class="relative z-10 flex h-full max-w-md flex-col justify-center">
                <h1 class="text-2xl font-bold text-slate-800 sm:text-3xl lg:text-[42px]">
                    Halo, {{ auth()->user()->name }} <span class="inline-block animate-float">&#128075;</span>
                </h1>
                <p class="mt-2 text-lg text-slate-600">Siap bermain dan belajar hari ini?</p>
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
               class="block rounded-2xl border-2 border-secondary-400 bg-secondary-50 p-6 transition-all duration-300 hover:-translate-y-0.5 hover:bg-secondary-100 hover:shadow-soft dark:border-secondary-500/40 dark:bg-secondary-500/10 dark:hover:bg-secondary-500/15">
                <p class="text-sm font-medium text-secondary-600 dark:text-secondary-400">Sesi permainan sedang berlangsung</p>
                <p class="mt-1 text-xl font-bold text-secondary-700 dark:text-secondary-400">
                    Lanjutkan Permainan &mdash; {{ $activeSession->mode->label() }}
                </p>
                <p class="mt-1 text-sm text-secondary-600 dark:text-secondary-400">Status: {{ $activeSession->status->label() }}</p>
            </a>
        @else
            <div class="grid grid-cols-1 items-stretch gap-6 sm:grid-cols-2">
                <x-player.robot-hero
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
            <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft dark:bg-slate-800 sm:p-6 lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="chart-bar" class="h-5 w-5 text-primary-500" />
                        Progress Belajar per Kategori
                    </h3>
                    <a href="{{ route('player.progress') }}" class="text-xs font-semibold text-primary-500 transition hover:text-primary-600 dark:text-primary-400 dark:hover:text-primary-300">Lihat detail &rarr;</a>
                </div>

                @if ($progressPerKategori->isEmpty())
                    <p class="text-sm text-slate-400 dark:text-slate-500">Belum ada kategori materi.</p>
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
            <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft dark:bg-slate-800 sm:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-slate-200">
                        <x-player.icon name="trophy" class="h-5 w-5 text-accent-500" />
                        Achievement
                    </h3>
                    <a href="{{ route('player.achievements') }}" class="text-xs font-semibold text-primary-500 transition hover:text-primary-600 dark:text-primary-400 dark:hover:text-primary-300">Lihat semua &rarr;</a>
                </div>
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">{{ $totalAchievementDiraih }} achievement telah diraih.</p>

                @if ($nearestAchievement)
                    <div class="rounded-2xl bg-accent-50 p-4 dark:bg-accent-500/10">
                        <p class="text-sm font-semibold text-accent-600 dark:text-accent-400">{{ $nearestAchievement['achievement']->nama }}</p>
                        <p class="mt-1 text-xs text-accent-600/80 dark:text-accent-400/80">
                            {{ $nearestAchievement['current'] }} / {{ $nearestAchievement['target'] }}
                            ({{ $nearestAchievement['achievement']->syarat_type->label() }})
                        </p>
                        <div class="mt-2.5 h-2.5 w-full rounded-full bg-accent-100 dark:bg-accent-500/20">
                            <div class="h-2.5 rounded-full bg-accent-500 transition-all duration-700 ease-out"
                                 style="width: {{ min(100, (int) round($nearestAchievement['current'] / max($nearestAchievement['target'], 1) * 100)) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500">Semua achievement aktif telah diraih. Selamat!</p>
                @endif
            </div>
        </div>

        {{-- Sertifikat widget --}}
        <x-player.certificate-card :sudahPunya="$sudahPunyaSertifikat" :checklist="$sertifikatChecklist" />
    </div>
</x-player-layout>
