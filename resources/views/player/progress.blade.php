@php
    $categoryColors = ['blue', 'green', 'purple', 'amber', 'rose'];
@endphp

<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Progress Belajar</h1>
        <p class="mt-0.5 text-sm text-slate-500">Pantau perkembangan belajarmu dan raih tujuanmu! &#128640;</p>
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
            <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6 lg:col-span-2">
                <h3 class="text-base font-bold text-slate-700">Perkembangan Belajar</h3>

                <div class="mt-5 flex flex-col gap-6 lg:flex-row lg:items-start">
                    <div class="flex shrink-0 flex-col items-center gap-2 lg:w-36">
                        <div class="relative w-full rounded-2xl bg-primary-50 px-3.5 py-2.5 text-center text-xs font-medium leading-relaxed text-slate-600 shadow-sm">
                            Belajar sedikit demi sedikit, hasil luar biasa akan mengikutimu!
                            <span class="absolute -bottom-1.5 left-1/2 h-3 w-3 -translate-x-1/2 rotate-45 bg-primary-50" aria-hidden="true"></span>
                        </div>

                        <svg viewBox="0 0 100 120" class="mt-3 h-24 w-24 animate-float select-none" aria-hidden="true">
                            <line x1="50" y1="6" x2="50" y2="16" stroke="#0F4CBA" stroke-width="3" stroke-linecap="round" />
                            <circle cx="50" cy="5" r="4" fill="#F68B1F" />
                            <rect x="24" y="16" width="52" height="40" rx="16" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                            <circle cx="40" cy="36" r="4.5" fill="#0F4CBA" />
                            <circle cx="60" cy="36" r="4.5" fill="#0F4CBA" />
                            <path d="M40 45c3 3 9 3 12 0" stroke="#0F4CBA" stroke-width="2.5" stroke-linecap="round" fill="none" />
                            <rect x="20" y="58" width="60" height="46" rx="18" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="3" />
                            <rect x="6" y="68" width="14" height="10" rx="5" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                            <rect x="80" y="68" width="14" height="10" rx="5" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                            <rect x="34" y="72" width="32" height="22" rx="4" fill="#FFFFFF" stroke="#00A65A" stroke-width="2.5" />
                            <path d="M39 83h10M39 88h16" stroke="#00A65A" stroke-width="2" stroke-linecap="round" />
                        </svg>
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
                            <p class="text-sm text-slate-400">Belum ada kategori materi.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3 rounded-2xl bg-primary-50 p-4 text-sm">
                    <x-player.icon name="trophy" class="h-5 w-5 shrink-0 text-primary-500" />
                    <p class="text-primary-700">
                        <span class="font-bold">Konsistensi adalah kunci kesuksesan!</span>
                        Terus belajar dan raih semua pencapaianmu! &#128170;
                    </p>
                </div>
            </div>

            {{-- Level, streak, tips --}}
            <div class="space-y-6">
                <x-player.level-card :level="$level" />
                <x-player.streak-card :streak="$streak" />
                <x-player.tips-card :tip="$tip" />
            </div>
        </div>

        <x-player.recent-activity-card :activities="$recentActivity" />
    </div>
</x-player-layout>
