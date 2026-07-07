<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Progress Belajar</h2>
    </x-slot>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">Kategori Selesai</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $overall['kategori_dengan_progress'] }}/{{ $overall['total_kategori_aktif'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">Akurasi Keseluruhan</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $overall['akurasi_keseluruhan'] }}%</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">Total Dijawab</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $overall['total_dijawab'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-medium text-slate-500">Total Benar</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ $overall['total_benar'] }}</p>
        </div>
    </div>

    <div class="space-y-3">
        @foreach ($perKategori as $row)
            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-800">{{ $row['kategori'] }}</p>
                    <p class="text-sm text-slate-500">{{ $row['total_dijawab'] }} soal dijawab &middot; {{ $row['akurasi'] }}% akurasi</p>
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, $row['akurasi']) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
</x-player-layout>
