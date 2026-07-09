<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Progress Belajar</h2>
    </x-slot>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-player.stat-card icon="chart-bar" label="Kategori Selesai" :value="$overall['kategori_dengan_progress'].'/'.$overall['total_kategori_aktif']" color="blue" :delay="0" />
        <x-player.stat-card icon="star" label="Akurasi Keseluruhan" :value="$overall['akurasi_keseluruhan'].'%'" color="green" :delay="75" />
        <x-player.stat-card icon="check-circle" label="Total Dijawab" :value="$overall['total_dijawab']" color="amber" :delay="150" />
        <x-player.stat-card icon="trophy" label="Total Benar" :value="$overall['total_benar']" color="purple" :delay="225" />
    </div>

    <div class="space-y-5 rounded-3xl bg-white p-5 shadow-sm sm:p-6">
        @foreach ($perKategori as $row)
            <x-player.progress-card
                :kategori="$row['kategori']"
                :totalDijawab="$row['total_dijawab']"
                :akurasi="$row['akurasi']"
            />
        @endforeach
    </div>
</x-player-layout>
