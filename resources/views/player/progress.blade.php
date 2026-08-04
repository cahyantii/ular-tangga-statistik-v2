@php
    $categoryColors = ['blue', 'green', 'purple', 'amber', 'rose'];
@endphp

<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl dark:text-dark-text">Progress Belajar</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-dark-muted">Pantau perkembangan belajarmu dan raih tujuanmu! &#128640;</p>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-player.stat-card icon="chart-bar" label="Kategori Selesai" :value="$overall['kategori_dengan_progress'].'/'.$overall['total_kategori_aktif']" color="blue" :delay="0" />
            <x-player.stat-card icon="star" label="Akurasi Keseluruhan" :value="$overall['akurasi_keseluruhan'].'%'" color="green" :delay="75" />
            <x-player.stat-card icon="check-circle" label="Total Dijawab" :value="$overall['total_dijawab']" color="amber" :delay="150" />
            <x-player.stat-card icon="trophy" label="Total Benar" :value="$overall['total_benar']" color="purple" :delay="225" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Perkembangan belajar --}}
            <div class="group rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6 lg:col-span-2 dark:bg-dark-surface dark:shadow-none">
                <h3 class="text-base font-bold text-slate-700 dark:text-dark-text">Perkembangan Belajar</h3>

                <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="flex shrink-0 flex-col items-center gap-2 lg:w-36">
                        <div class="relative w-full rounded-2xl bg-primary-50 px-3.5 py-2.5 text-center text-xs font-medium leading-relaxed text-slate-600 shadow-sm dark:bg-primary-900/30 dark:text-primary-100 dark:shadow-none">
                            Belajar sedikit demi sedikit, hasil luar biasa akan mengikutimu!
                            <span class="absolute -bottom-1.5 left-1/2 h-3 w-3 -translate-x-1/2 rotate-45 bg-primary-50 dark:bg-[#162a55]" aria-hidden="true"></span>
                        </div>

                        <img
                            src="{{ asset('images/brand/logo-robott.png') }}"
                            alt=""
                            aria-hidden="true"
                            class="mt-3 h-[100px] w-[100px] select-none object-contain pointer-events-none animate-float transition-all duration-300 ease-out group-hover:scale-105 drop-shadow-[0_10px_20px_rgba(37,99,235,0.18)] sm:h-[120px] sm:w-[120px] lg:h-[140px] lg:w-[140px]"
                        >
                    </div>

                    <div class="min-w-0 flex-1 space-y-5">
                        @forelse ($perKategori as $item)
                            <x-player.progress-category-card
                                :kategori="$item['kategori']"
                                :icon="$item['icon']"
                                :totalDijawab="$item['total_dijawab']"
                                :totalSoal="$item['total_soal']"
                                :akurasi="$item['akurasi']"
                                :progressPercent="$item['progress_percent']"
                                :status="$item['status']"
                                :color="$categoryColors[$loop->index % count($categoryColors)]"
                                :delay="$loop->index * 75"
                            />
                        @empty
                            <p class="text-sm text-slate-400 dark:text-dark-muted">Belum ada kategori materi.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3 rounded-2xl bg-primary-50 p-4 text-sm dark:bg-primary-900/30">
                    <x-player.icon name="trophy" class="h-5 w-5 shrink-0 text-primary-500 dark:text-primary-400" />
                    <p class="text-primary-700 dark:text-primary-300">
                        <span class="font-bold">Konsistensi adalah kunci kesuksesan!</span>
                        Terus belajar dan raih semua pencapaianmu! &#128170;
                    </p>
                </div>
            </div>

            {{-- Level, streak, tips --}}
            <div class="space-y-6">
                <x-player.level-card :level="$level" />
                <x-player.streak-card :streak="$streak" />
                <x-player.tips-card :tip="$tip" :illustration="asset('images/brand/logo-buku.png')" illustration-position="right" />
            </div>
        </div>

        <x-player.recent-activity-card :activities="$recentActivity" />
    </div>
</x-player-layout>
