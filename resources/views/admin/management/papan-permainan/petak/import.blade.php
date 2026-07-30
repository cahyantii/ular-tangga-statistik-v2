<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Import Petak — {{ $papan->nama }}</h1>

    <div class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Upload file (.csv atau .xlsx) hasil Export Petak papan ini untuk mengubah banyak petak sekaligus.
            Baris pertama adalah header dengan kolom persis berikut:
        </p>
        <code class="block whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-xs text-slate-700 dark:bg-slate-900/50 dark:text-slate-300">papan_id, papan_nama, posisi, jenis_petak, kategori, label, icon, warna, border_warna, deskripsi</code>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Hanya baris dengan <strong>papan_id = {{ $papan->id }}</strong> yang diproses. Petak Start/Finish/Tangga/Ular
            tidak bisa diubah lewat import ini — kelola lewat Editor Petak/Konektor. Jenis petak yang bisa diisi lewat
            import ini hanya <strong>biasa</strong> dan <strong>mystery</strong>; kolom <strong>kategori</strong>
            dipertahankan di file untuk kompatibilitas tapi tidak lagi dipakai.
        </p>

        <form method="POST" action="{{ route('admin.management.papan-permainan.petak.import.preview', $papan) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-admin.input label="File" name="file" type="file" required accept=".csv,.txt,.xlsx" />

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.management.papan-permainan.petak.index', $papan) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Pratinjau</button>
            </div>
        </form>
    </div>
</x-admin-layout>
