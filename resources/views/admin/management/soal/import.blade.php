<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Import Soal</h1>

    <div class="max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Format file (.csv atau .xlsx), baris pertama adalah header dengan kolom persis berikut:
        </p>
        <code class="block rounded-lg bg-slate-50 p-3 text-xs text-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
            kategori, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, pembahasan, is_active
        </code>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Kolom <strong>kategori</strong> diisi dengan nama kategori materi yang sudah ada (bukan ID).
            Kolom <strong>kunci_jawaban</strong> harus salah satu dari A, B, C, D.
            Kolom <strong>is_active</strong> opsional (1/0), default aktif.
        </p>

        <form method="POST" action="{{ route('admin.management.soal.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-admin.input label="File" name="file" type="file" required accept=".csv,.txt,.xlsx" />

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.management.soal.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">Batal</a>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Pratinjau</button>
            </div>
        </form>
    </div>
</x-admin-layout>
