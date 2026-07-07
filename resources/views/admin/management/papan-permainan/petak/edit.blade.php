<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Petak #{{ $petak->posisi }} — {{ $papan->nama }}</h1>

    <form method="POST" action="{{ route('admin.management.papan-permainan.petak.update', [$papan, $petak]) }}"
          x-data="{ jenis: '{{ old('jenis_petak', $petak->jenis_petak->value) }}' }"
          class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_version" value="{{ $version }}">

        <x-admin.select label="Jenis Petak" name="jenis_petak" required x-model="jenis"
                         :options="['biasa' => 'Biasa', 'soal' => 'Soal', 'bonus' => 'Bonus', 'penalti' => 'Penalti', 'mystery' => 'Mystery']"
                         :value="$petak->jenis_petak->value" />

        <div x-show="jenis === 'soal'">
            <x-admin.select label="Kategori (untuk petak Soal)" name="kategori_id" :options="$kategoriOptions"
                            :value="$petak->kategori_id" placeholder="Pilih kategori" />
        </div>

        <x-admin.input label="Label (opsional)" name="label" :value="$petak->label" />
        <x-admin.input label="Icon (opsional)" name="icon" :value="$petak->icon" />
        <x-admin.input label="Warna (opsional, kode hex)" name="warna" :value="$petak->warna" />
        <x-admin.input label="Deskripsi (opsional)" name="deskripsi" type="textarea" :value="$petak->deskripsi" rows="3" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.papan-permainan.petak.index', $papan) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
