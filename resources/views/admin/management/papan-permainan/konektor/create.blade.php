<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Tambah Konektor — {{ $papan->nama }}</h1>

    <form method="POST" action="{{ route('admin.management.papan-permainan.konektor.store', $papan) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        <x-admin.select label="Jenis" name="jenis" required :options="['tangga' => 'Tangga', 'ular' => 'Ular']" placeholder="Pilih jenis" />
        <x-admin.input label="Posisi Awal" name="posisi_awal" type="number" required />
        <x-admin.input label="Posisi Akhir" name="posisi_akhir" type="number" required />
        <x-admin.input label="Label (opsional)" name="label" />
        <x-admin.input label="Icon (opsional)" name="icon" />

        <p class="text-xs text-slate-500">
            Posisi awal dan akhir tidak boleh petak Start/Finish, tidak boleh sama, dan tidak boleh membentuk
            rantai dengan konektor lain (posisi akhir tidak boleh sama dengan posisi awal konektor lain).
        </p>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.papan-permainan.konektor.index', $papan) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
