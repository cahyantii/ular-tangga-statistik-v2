<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Kategori Materi</h1>

    <form method="POST" action="{{ route('admin.management.kategori-materi.update', $kategori) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <x-admin.input label="Nama" name="nama" :value="$kategori->nama" required />
        <x-admin.input label="Slug" name="slug" :value="$kategori->slug" />
        <x-admin.input label="Icon (opsional, nama ikon/emoji)" name="icon" :value="$kategori->icon" />
        <x-admin.input label="Urutan" name="urutan" type="number" :value="$kategori->urutan" required />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="$kategori->is_active" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.kategori-materi.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
