<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Soal</h1>

    <form method="POST" action="{{ route('admin.management.soal.update', $soal) }}" class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_version" value="{{ $version }}">

        <x-admin.select label="Kategori" name="kategori_id" required :options="$kategoriOptions" :value="$soal->kategori_id" />
        <x-admin.input label="Pertanyaan" name="pertanyaan" type="textarea" :value="$soal->pertanyaan" required rows="3" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <x-admin.input label="Opsi A" name="opsi_a" :value="$soal->opsi_jawaban['A'] ?? ''" required />
            <x-admin.input label="Opsi B" name="opsi_b" :value="$soal->opsi_jawaban['B'] ?? ''" required />
            <x-admin.input label="Opsi C" name="opsi_c" :value="$soal->opsi_jawaban['C'] ?? ''" required />
            <x-admin.input label="Opsi D" name="opsi_d" :value="$soal->opsi_jawaban['D'] ?? ''" required />
        </div>

        <x-admin.select label="Kunci Jawaban" name="kunci_jawaban" required
                         :options="['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D']" :value="$soal->kunci_jawaban" />
        <x-admin.input label="Pembahasan" name="pembahasan" type="textarea" :value="$soal->pembahasan" required rows="3" />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="$soal->is_active" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.soal.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
