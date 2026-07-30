<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Papan Permainan</h1>

    <form method="POST" action="{{ route('admin.management.papan-permainan.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        @csrf

        <x-admin.input label="Nama Papan" name="nama" required />
        <x-admin.input label="Deskripsi (opsional)" name="deskripsi" type="textarea" rows="3" />
        <x-admin.input label="Jumlah Petak" name="jumlah_petak" type="number" :value="100" required />
        <x-admin.input label="Jumlah Kolom (untuk tata letak zig-zag)" name="jumlah_kolom" type="number" :value="10" required />
        <x-admin.input label="Thumbnail (opsional, URL/path gambar)" name="thumbnail" />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="true" />

        <p class="text-xs text-slate-500 dark:text-slate-400">
            Jumlah petak tidak bisa diubah setelah papan dibuat. Petak akan otomatis dibuat (posisi 1 = Start,
            posisi terakhir = Finish, sisanya petak biasa) dan bisa diatur lebih lanjut di Editor Petak.
        </p>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.papan-permainan.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
