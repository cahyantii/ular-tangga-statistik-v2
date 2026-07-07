@php
    $boardConfig = [
        'jumlah_kolom' => $papan->jumlah_kolom,
        'jumlah_petak' => $papan->jumlah_petak,
        'petak' => $papan->petak->map(fn ($p) => [
            'id' => $p->id,
            'posisi' => $p->posisi,
            'jenis_petak' => $p->jenis_petak->value,
            'label' => $p->label,
        ])->values(),
        'konektor' => $papan->papanKonektor->map(fn ($k) => [
            'posisi_awal' => $k->posisi_awal,
            'posisi_akhir' => $k->posisi_akhir,
        ])->values(),
    ];
@endphp

<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/admin-board-editor.js'])
    @endpush

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Editor Petak — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">Klik sebuah petak untuk mengubah jenisnya.</p>
        </div>
        <a href="{{ route('admin.management.papan-permainan.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
            &larr; Kembali ke daftar papan
        </a>
    </div>

    <div class="mb-4 flex flex-wrap gap-3 text-xs">
        @foreach (['start' => 'Start', 'finish' => 'Finish', 'biasa' => 'Biasa', 'soal' => 'Soal', 'tangga' => 'Tangga', 'ular' => 'Ular', 'bonus' => 'Bonus', 'penalti' => 'Penalti', 'mystery' => 'Mystery'] as $jenis => $label)
            <span class="inline-flex items-center gap-1">
                <span class="h-3 w-3 rounded" style="background-color: {{ [
                    'start' => '#059669', 'finish' => '#7c3aed', 'biasa' => '#f1f5f9', 'soal' => '#2563eb',
                    'tangga' => '#10b981', 'ular' => '#e11d48', 'bonus' => '#d97706', 'penalti' => '#f43f5e', 'mystery' => '#6366f1',
                ][$jenis] }}"></span>
                {{ $label }}
            </span>
        @endforeach
    </div>

    <div id="board-grid" class="rounded-2xl border border-slate-200 bg-white p-4"></div>

    <div id="board-editor-data"
         data-board="{{ json_encode($boardConfig) }}"
         data-edit-base-url="{{ route('admin.management.papan-permainan.petak.index', $papan) }}"></div>
</x-admin-layout>
