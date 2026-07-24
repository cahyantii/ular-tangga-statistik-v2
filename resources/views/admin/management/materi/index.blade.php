<x-admin-layout>
    @php
        $rowColorClasses = [
            'bg-green-100 text-green-600',
            'bg-blue-100 text-blue-600',
            'bg-violet-100 text-violet-500',
            'bg-amber-100 text-amber-500',
        ];
        $badgeColorClasses = [
            'bg-green-100 text-green-700',
            'bg-blue-100 text-blue-700',
            'bg-violet-100 text-violet-700',
            'bg-amber-100 text-amber-700',
        ];
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Kelola Materi</h1>
            <p class="mt-2 text-slate-500">Kelola semua materi pembelajaran yang tersedia dalam sistem</p>
        </div>

        <a href="{{ route('admin.management.materi.create') }}"
           class="admin-nav-active inline-flex shrink-0 items-center gap-2 self-start rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-300 ease-in-out hover:-translate-y-0.5 sm:self-auto">
            <x-player.icon name="plus" class="h-4 w-4" />
            Tambah Materi
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                <x-player.icon name="book" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Total Materi</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['materi']) }}</p>
            <p class="mt-1 text-xs font-medium text-green-600">Materi terdaftar</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                <x-player.icon name="grid" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Kategori</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['kategori']) }}</p>
            <p class="mt-1 text-xs font-medium text-blue-600">Kategori materi</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500">
                <x-player.icon name="document" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Total Soal</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['soal']) }}</p>
            <p class="mt-1 text-xs font-medium text-violet-500">Soal terkait</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)]">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-500">
                <x-player.icon name="check-circle" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600">Materi Aktif</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900">{{ number_format($stats['aktif']) }}</p>
            <p class="mt-1 text-xs font-medium text-orange-500">{{ $stats['materi'] > 0 ? round($stats['aktif'] / $stats['materi'] * 100) : 0 }}% dari total</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="mt-6 rounded-3xl border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)] sm:p-5">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <x-player.icon name="search" class="h-4 w-4" />
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari judul materi..."
                    class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500"
                >
            </div>

            <select name="kategori_id" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 py-2.5 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriOptions as $id => $nama)
                    <option value="{{ $id }}" @selected((string) request('kategori_id') === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="urutan" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 py-2.5 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                <option value="">Semua Urutan</option>
                @foreach ($urutanOptions as $urutan)
                    <option value="{{ $urutan }}" @selected((string) request('urutan') === (string) $urutan)>Urutan {{ $urutan }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 py-2.5 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>

            <a href="{{ route('admin.management.materi.index', ['filter' => $filter]) }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-green-500 px-4 py-2.5 text-sm font-semibold text-green-600 transition duration-200 ease-in-out hover:bg-green-50">
                <x-player.icon name="refresh" class="h-4 w-4" />
                Reset Filter
            </a>
        </div>
    </form>

    {{-- Filter pills --}}
    <div class="mt-4 flex flex-wrap items-center gap-2">
        @foreach ([
            'active' => ['label' => 'Aktif', 'icon' => 'check-circle'],
            'trashed' => ['label' => 'Sampah', 'icon' => 'trash'],
            'all' => ['label' => 'Semua', 'icon' => 'grid'],
        ] as $value => $meta)
            <a href="{{ route('admin.management.materi.index', ['filter' => $value]) }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'border-green-200 bg-green-50 text-green-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                <x-player.icon :name="$meta['icon']" class="h-4 w-4" />
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_40px_rgba(0,0,0,.06)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-green-50/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 sm:px-6">#</th>
                        <th class="px-4 py-3.5 sm:px-6">Judul Materi</th>
                        <th class="px-4 py-3.5 sm:px-6">Kategori</th>
                        <th class="px-4 py-3.5 sm:px-6">Urutan</th>
                        <th class="px-4 py-3.5 sm:px-6">Status</th>
                        <th class="px-4 py-3.5 text-right sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($materiList as $index => $materi)
                        @php
                            $colorIndex = ($materi->kategori_id ?? $index) % count($rowColorClasses);
                            $deskripsi = \Illuminate\Support\Str::limit(trim(strip_tags($materi->konten)), 70);
                        @endphp
                        <tr class="transition-colors duration-200 hover:bg-slate-50/60">
                            <td class="px-4 py-4 text-slate-500 sm:px-6">{{ $materiList->firstItem() + $index }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $rowColorClasses[$colorIndex] }}">
                                        <x-player.icon :name="$materi->kategori->icon ?? 'book'" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800">{{ $materi->judul }}</p>
                                        @if ($deskripsi !== '')
                                            <p class="truncate text-xs text-slate-400">{{ $deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColorClasses[$colorIndex] }}">
                                    {{ $materi->kategori->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-500 sm:px-6">{{ $materi->urutan }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                @if ($materi->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @elseif ($materi->is_active)
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
                                    @if ($materi->trashed())
                                        <form method="POST" action="{{ route('admin.management.materi.restore', $materi->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" aria-label="Pulihkan {{ $materi->judul }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 text-green-600 transition duration-200 ease-in-out hover:border-green-400 hover:bg-green-50">
                                                <x-player.icon name="refresh" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.management.materi.edit', $materi) }}" aria-label="Edit {{ $materi->judul }}"
                                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600">
                                            <x-player.icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.management.materi.destroy', $materi) }}"
                                              onsubmit="return confirm('Hapus materi {{ $materi->judul }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus {{ $materi->judul }}"
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
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">Tidak ada data materi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $materiList->firstItem() ?? 0 }} - {{ $materiList->lastItem() ?? 0 }} dari {{ $materiList->total() }} materi
        </p>
        {{ $materiList->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
