<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Papan Permainan</h1>

    <form method="POST" action="{{ route('admin.management.papan-permainan.update', $papan) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_version" value="{{ $version }}">

        <x-admin.input label="Nama Papan" name="nama" :value="$papan->nama" required />
        <x-admin.input label="Deskripsi (opsional)" name="deskripsi" type="textarea" rows="3" :value="$papan->deskripsi" />

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah Petak</label>
            <input type="number" value="{{ $papan->jumlah_petak }}" disabled
                   class="w-full rounded-lg border-slate-200 bg-slate-100 text-sm text-slate-400">
            <p class="mt-1 text-xs text-slate-400">Jumlah petak terkunci setelah papan dibuat.</p>
        </div>

        <x-admin.input label="Jumlah Kolom (untuk tata letak zig-zag)" name="jumlah_kolom" type="number" :value="$papan->jumlah_kolom" required />
        <x-admin.input label="Thumbnail (opsional, URL/path gambar)" name="thumbnail" :value="$papan->thumbnail" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.papan-permainan.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
