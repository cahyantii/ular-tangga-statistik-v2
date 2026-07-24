<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Tambah Achievement</h1>

    <form method="POST" action="{{ route('admin.management.achievements.store') }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf

        <x-admin.input label="Kode (unik, huruf/angka/underscore)" name="kode" required />
        <x-admin.input label="Nama" name="nama" required />
        <x-admin.input label="Deskripsi" name="deskripsi" type="textarea" rows="2" />
        <x-admin.input label="Icon (opsional, nama icon)" name="icon" />

        <x-admin.select label="Warna Badge" name="warna_badge"
                         :options="['green' => 'Hijau', 'blue' => 'Biru', 'orange' => 'Orange', 'purple' => 'Ungu', 'pink' => 'Pink', 'amber' => 'Kuning', 'turquoise' => 'Turquoise']"
                         placeholder="Pilih warna" />

        <x-admin.select label="Syarat" name="syarat_type" required
                         :options="['total_menang' => 'Total Kemenangan', 'total_permainan' => 'Total Permainan', 'akurasi_keseluruhan' => 'Akurasi Keseluruhan (%)', 'selisih_kemenangan_terbesar' => 'Selisih Kemenangan Terjauh', 'total_angka_enam' => 'Total Dadu Bernilai 6']"
                         placeholder="Pilih syarat" />
        <x-admin.input label="Nilai Syarat" name="syarat_value" type="number" required />
        <x-admin.input label="Reward Poin" name="reward_poin" type="number" :value="0" required />
        <x-admin.input label="Urutan" name="urutan" type="number" :value="0" required />
        <x-admin.checkbox label="Aktif" name="is_active" :checked="true" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.achievements.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
