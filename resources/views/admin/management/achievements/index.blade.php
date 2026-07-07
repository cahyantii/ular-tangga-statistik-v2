<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Achievement</h1>
        <a href="{{ route('admin.management.achievements.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
            + Tambah Achievement
        </a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach (['active' => 'Aktif', 'trashed' => 'Sampah', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('admin.management.achievements.index', ['filter' => $value]) }}"
               class="rounded-full px-3 py-1 font-medium {{ $filter === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Syarat</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($achievements as $achievement)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $achievement->kode }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $achievement->nama }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $achievement->syarat_type->label() }} &ge; {{ $achievement->syarat_value }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($achievement->trashed())
                                <span class="text-red-500">Dihapus</span>
                            @elseif ($achievement->is_active)
                                <span class="text-emerald-600">Aktif</span>
                            @else
                                <span class="text-slate-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($achievement->trashed())
                                <form method="POST" action="{{ route('admin.management.achievements.restore', $achievement->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800">Pulihkan</button>
                                </form>
                            @else
                                <a href="{{ route('admin.management.achievements.edit', $achievement) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.management.achievements.destroy', $achievement) }}" class="inline"
                                      onsubmit="return confirm('Hapus achievement {{ $achievement->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data achievement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $achievements->links() }}
    </div>
</x-admin-layout>
