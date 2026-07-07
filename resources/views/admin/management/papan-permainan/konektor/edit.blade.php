<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Edit Konektor — {{ $papan->nama }}</h1>

    <form method="POST" action="{{ route('admin.management.papan-permainan.konektor.update', [$papan, $konektor]) }}" class="max-w-xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_version" value="{{ $version }}">

        <x-admin.select label="Jenis" name="jenis" required :options="['tangga' => 'Tangga', 'ular' => 'Ular']" :value="$konektor->jenis->value" />
        <x-admin.input label="Posisi Awal" name="posisi_awal" type="number" :value="$konektor->posisi_awal" required />
        <x-admin.input label="Posisi Akhir" name="posisi_akhir" type="number" :value="$konektor->posisi_akhir" required />
        <x-admin.input label="Label (opsional)" name="label" :value="$konektor->label" />
        <x-admin.input label="Icon (opsional)" name="icon" :value="$konektor->icon" />

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.management.papan-permainan.konektor.index', $papan) }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</x-admin-layout>
