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
            <h1 class="text-2xl font-bold text-slate-900">Preview — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">Tampilan read-only, sama seperti yang akan dilihat pemain.</p>
        </div>
        <a href="{{ route('admin.management.papan-permainan.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
            &larr; Kembali ke daftar papan
        </a>
    </div>

    <div id="board-grid" class="rounded-2xl border border-slate-200 bg-white p-4"></div>

    <div id="board-editor-data" data-board="{{ json_encode($boardConfig) }}"></div>
</x-admin-layout>
