<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Konektor — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola posisi tangga dan ular pada papan ini.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.management.papan-permainan.index') }}" class="text-sm text-slate-600 hover:text-slate-900 self-center">
                &larr; Kembali
            </a>
            <a href="{{ route('admin.management.papan-permainan.konektor.create', $papan) }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                + Tambah Konektor
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Posisi Awal</th>
                    <th class="px-4 py-3">Posisi Akhir</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($konektorList as $konektor)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $konektor->jenis->label() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->posisi_awal }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->posisi_akhir }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->label ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.management.papan-permainan.konektor.edit', [$papan, $konektor]) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                            <form method="POST" action="{{ route('admin.management.papan-permainan.konektor.destroy', [$papan, $konektor]) }}" class="inline"
                                  onsubmit="return confirm('Hapus konektor ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada konektor pada papan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
