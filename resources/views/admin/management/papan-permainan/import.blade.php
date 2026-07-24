<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Import Papan Permainan</h1>

    <div class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        <p class="text-sm text-slate-600">
            Upload file <strong>.json</strong> hasil Export Papan (tombol Export pada daftar papan). Import selalu
            membuat papan baru — tidak pernah menimpa papan yang sudah ada.
        </p>
        <code class="block whitespace-pre rounded-lg bg-slate-50 p-3 text-xs text-slate-700">{
  "papan": { "nama": "...", "jumlah_petak": 100, "jumlah_kolom": 10, "deskripsi": "...", "is_active": true },
  "petak": [ { "posisi": 1, "jenis_petak": "start", ... }, ... ],
  "konektor": [ { "jenis": "tangga", "posisi_awal": 8, "posisi_akhir": 22, ... }, ... ]
}</code>
        <p class="text-sm text-slate-500">
            Jumlah baris <strong>petak</strong> harus sama persis dengan <strong>jumlah_petak</strong>, posisi 1 harus
            <strong>start</strong> dan posisi terakhir harus <strong>finish</strong>. Kolom <strong>kategori_nama</strong>
            pada petak jenis soal diisi nama kategori materi yang sudah ada (bukan ID).
        </p>

        <form method="POST" action="{{ route('admin.management.papan-permainan.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-admin.input label="File" name="file" type="file" required accept=".json" />

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.management.papan-permainan.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Pratinjau</button>
            </div>
        </form>
    </div>
</x-admin-layout>
