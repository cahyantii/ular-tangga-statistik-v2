<x-admin-layout>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Soal</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.management.soal.export', ['format' => 'xlsx']) }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Export .xlsx
            </a>
            <a href="{{ route('admin.management.soal.export', ['format' => 'csv']) }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Export .csv
            </a>
            <a href="{{ route('admin.management.soal.import.create') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Import
            </a>
            <a href="{{ route('admin.management.soal.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                + Tambah Soal
            </a>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach (['active' => 'Aktif', 'trashed' => 'Sampah', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('admin.management.soal.index', ['filter' => $value]) }}"
               class="rounded-full px-3 py-1 font-medium {{ $filter === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pertanyaan..."
               class="w-64 rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <select name="kategori_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriOptions as $id => $nama)
                <option value="{{ $id }}" @selected((string) request('kategori_id') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
            Filter
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Pertanyaan</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Kunci</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($soalList as $soal)
                    <tr>
                        <td class="max-w-md px-4 py-3 font-medium text-slate-800">{{ \Illuminate\Support\Str::limit($soal->pertanyaan, 80) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $soal->kategori->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $soal->kunci_jawaban }}</td>
                        <td class="px-4 py-3">
                            @if ($soal->trashed())
                                <span class="text-red-500">Dihapus</span>
                            @elseif ($soal->is_active)
                                <span class="text-emerald-600">Aktif</span>
                            @else
                                <span class="text-slate-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($soal->trashed())
                                <form method="POST" action="{{ route('admin.management.soal.restore', $soal->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800">Pulihkan</button>
                                </form>
                            @else
                                <a href="{{ route('admin.management.soal.edit', $soal) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.management.soal.destroy', $soal) }}" class="inline"
                                      onsubmit="return confirm('Hapus soal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data soal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $soalList->links() }}
    </div>
</x-admin-layout>
