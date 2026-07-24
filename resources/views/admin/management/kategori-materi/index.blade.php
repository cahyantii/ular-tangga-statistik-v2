<x-admin-layout>
    @php
        $rowColorClasses = [
            'bg-green-100 text-green-600',
            'bg-blue-100 text-blue-600',
            'bg-amber-100 text-amber-500',
            'bg-violet-100 text-violet-500',
        ];
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">Kelola Kategori Materi</h1>
            <p class="mt-1 text-slate-500">Kelola semua kategori materi yang tersedia dalam sistem</p>
        </div>

        <a href="{{ route('admin.management.kategori-materi.create') }}"
           class="admin-nav-active inline-flex shrink-0 items-center gap-2 self-start rounded-full px-5 py-3 text-sm font-semibold text-white transition duration-300 ease-in-out hover:-translate-y-0.5 sm:self-auto">
            <x-player.icon name="plus" class="h-4 w-4" />
            Tambah Kategori
        </a>
    </div>

    {{-- Filter pills --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach ([
            'active' => ['label' => 'Aktif', 'icon' => 'check-circle'],
            'trashed' => ['label' => 'Sampah', 'icon' => 'trash'],
            'all' => ['label' => 'Semua', 'icon' => 'grid'],
        ] as $value => $meta)
            <a href="{{ route('admin.management.kategori-materi.index', ['filter' => $value]) }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                <x-player.icon :name="$meta['icon']" class="h-4 w-4" />
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                <x-player.icon name="grid" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Total Kategori</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['kategori']) }}</p>
            <p class="mt-1 text-xs font-medium text-green-600">Kategori terdaftar</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                <x-player.icon name="book" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Total Materi</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['materi']) }}</p>
            <p class="mt-1 text-xs font-medium text-blue-600">Materi dalam kategori</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500">
                <x-player.icon name="document" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Total Soal</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['soal']) }}</p>
            <p class="mt-1 text-xs font-medium text-violet-500">Soal tersedia</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-500">
                <x-player.icon name="check-circle" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Kategori Aktif</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['aktif']) }}</p>
            <p class="mt-1 text-xs font-medium text-amber-500">{{ $stats['kategori'] > 0 ? round($stats['aktif'] / $stats['kategori'] * 100) : 0 }}% dari total</p>
        </div>
    </div>

    {{-- Search + refresh --}}
    <form method="GET" class="mt-6 rounded-3xl border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)] sm:p-5">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="flex items-center gap-3">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <x-player.icon name="search" class="h-4 w-4" />
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari kategori materi..."
                    class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500"
                >
            </div>

            <a href="{{ route('admin.management.kategori-materi.index', ['filter' => $filter]) }}" aria-label="Reset pencarian"
               class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-green-500 text-green-600 transition duration-200 ease-in-out hover:bg-green-50">
                <x-player.icon name="refresh" class="h-4 w-4" />
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_40px_rgba(0,0,0,.06)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-green-50/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 sm:px-6">#</th>
                        <th class="px-4 py-3.5 sm:px-6">Nama Kategori</th>
                        <th class="px-4 py-3.5 sm:px-6">Slug</th>
                        <th class="px-4 py-3.5 sm:px-6">Urutan</th>
                        <th class="px-4 py-3.5 sm:px-6">Materi</th>
                        <th class="px-4 py-3.5 sm:px-6">Soal</th>
                        <th class="px-4 py-3.5 sm:px-6">Status</th>
                        <th class="px-4 py-3.5 text-right sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kategoriList as $index => $kategori)
                        <tr class="transition-colors duration-200 hover:bg-slate-50/60">
                            <td class="px-4 py-4 text-slate-500 sm:px-6">{{ $kategoriList->firstItem() + $index }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $rowColorClasses[$index % count($rowColorClasses)] }}">
                                        <x-player.icon :name="$kategori->icon ?? 'book'" class="h-5 w-5" />
                                    </span>
                                    <p class="font-semibold text-slate-800">{{ $kategori->nama }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-500 sm:px-6">{{ $kategori->slug }}</td>
                            <td class="px-4 py-4 text-slate-500 sm:px-6">{{ $kategori->urutan }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                    {{ $kategori->materi_count }} Materi
                                </span>
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $kategori->soal_count }} Soal
                                </span>
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                @if ($kategori->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @elseif ($kategori->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($kategori->trashed())
                                        <form method="POST" action="{{ route('admin.management.kategori-materi.restore', $kategori->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" aria-label="Pulihkan {{ $kategori->nama }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 text-green-600 transition duration-200 ease-in-out hover:border-green-400 hover:bg-green-50">
                                                <x-player.icon name="refresh" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.management.kategori-materi.edit', $kategori) }}" aria-label="Edit {{ $kategori->nama }}"
                                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600">
                                            <x-player.icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.management.kategori-materi.destroy', $kategori) }}"
                                              onsubmit="return confirm('Hapus kategori {{ $kategori->nama }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus {{ $kategori->nama }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-500 transition duration-200 ease-in-out hover:border-red-400 hover:bg-red-50 hover:text-red-700">
                                                <x-player.icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">Tidak ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $kategoriList->firstItem() ?? 0 }} - {{ $kategoriList->lastItem() ?? 0 }} dari {{ $kategoriList->total() }} kategori
        </p>
        {{ $kategoriList->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
