<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Materi</h1>

    <form method="POST" action="{{ route('admin.management.materi.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        @csrf

        <x-admin.select label="Kategori" name="kategori_id" required :options="$kategoriOptions" placeholder="Pilih kategori" />
        <x-admin.input label="Judul" name="judul" required />
        <x-admin.input label="Konten" name="konten" type="textarea" required rows="8" />
        <x-admin.input label="Urutan" name="urutan" type="number" :value="0" required />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="true" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.materi.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
