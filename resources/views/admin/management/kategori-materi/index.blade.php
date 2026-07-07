<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Kategori Materi</h1>
        <a href="{{ route('admin.management.kategori-materi.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
            + Tambah Kategori
        </a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach (['active' => 'Aktif', 'trashed' => 'Sampah', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('admin.management.kategori-materi.index', ['filter' => $value]) }}"
               class="rounded-full px-3 py-1 font-medium {{ $filter === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Urutan</th>
                    <th class="px-4 py-3">Materi</th>
                    <th class="px-4 py-3">Soal</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($kategoriList as $kategori)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $kategori->nama }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $kategori->slug }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $kategori->urutan }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $kategori->materi_count }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $kategori->soal_count }}</td>
                        <td class="px-4 py-3">
                            @if ($kategori->trashed())
                                <span class="text-red-500">Dihapus</span>
                            @elseif ($kategori->is_active)
                                <span class="text-emerald-600">Aktif</span>
                            @else
                                <span class="text-slate-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($kategori->trashed())
                                <form method="POST" action="{{ route('admin.management.kategori-materi.restore', $kategori->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800">Pulihkan</button>
                                </form>
                            @else
                                <a href="{{ route('admin.management.kategori-materi.edit', $kategori) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.management.kategori-materi.destroy', $kategori) }}" class="inline"
                                      onsubmit="return confirm('Hapus kategori {{ $kategori->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada data kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $kategoriList->links() }}
    </div>
</x-admin-layout>
