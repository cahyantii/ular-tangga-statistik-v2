<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Soal</h1>

    <form method="POST" action="{{ route('admin.management.soal.store') }}" class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        @csrf

        <x-admin.select label="Kategori" name="kategori_id" required :options="$kategoriOptions" placeholder="Pilih kategori" />
        <x-admin.input label="Pertanyaan" name="pertanyaan" type="textarea" required rows="3" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-admin.input label="Opsi A" name="opsi_a" required />
            <x-admin.input label="Opsi B" name="opsi_b" required />
            <x-admin.input label="Opsi C" name="opsi_c" required />
            <x-admin.input label="Opsi D" name="opsi_d" required />
        </div>

        <x-admin.select label="Kunci Jawaban" name="kunci_jawaban" required
                         :options="['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D']" placeholder="Pilih kunci jawaban" />
        <x-admin.input label="Pembahasan" name="pembahasan" type="textarea" required rows="3" />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="true" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.soal.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
