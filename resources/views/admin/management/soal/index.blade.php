<x-admin-layout>
    {{-- Header card --}}
    <div class="overflow-hidden rounded-[30px] p-8 shadow-[0_10px_40px_rgba(0,0,0,.06)]" style="background: linear-gradient(90deg, #ffffff 0%, #ECFDF5 50%, #ffffff 100%);">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[22px] bg-green-100 text-green-600">
                    <x-player.icon name="help" class="h-8 w-8" />
                </span>
                <div>
                    <h1 class="text-3xl font-bold leading-tight text-[#1E293B] sm:text-4xl">Kelola Soal</h1>
                    <p class="mt-1 text-base text-slate-500 sm:text-lg">Kelola semua pertanyaan yang tersedia dalam sistem</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.management.soal.export', ['format' => 'xlsx']) }}"
                   class="inline-flex h-12 items-center gap-2 rounded-2xl border border-green-500 bg-white px-4 text-sm font-semibold text-green-600 shadow-sm transition duration-200 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:hover:bg-slate-700">
                    <x-player.icon name="download" class="h-4 w-4" />
                    Export .xlsx
                </a>
                <a href="{{ route('admin.management.soal.export', ['format' => 'csv']) }}"
                   class="inline-flex h-12 items-center gap-2 rounded-2xl border border-blue-500 bg-white px-4 text-sm font-semibold text-blue-600 shadow-sm transition duration-200 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:hover:bg-slate-700">
                    <x-player.icon name="download" class="h-4 w-4" />
                    Export .csv
                </a>
                <a href="{{ route('admin.management.soal.import.create') }}"
                   class="inline-flex h-12 items-center gap-2 rounded-2xl border border-violet-500 bg-white px-4 text-sm font-semibold text-violet-600 shadow-sm transition duration-200 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:hover:bg-slate-700">
                    <x-player.icon name="upload-cloud" class="h-4 w-4" />
                    Import
                </a>
                <a href="{{ route('admin.management.soal.create') }}"
                   class="admin-nav-active inline-flex h-12 items-center gap-2 rounded-2xl px-5 text-sm font-semibold text-white transition duration-300 ease-in-out hover:-translate-y-0.5">
                    <x-player.icon name="plus" class="h-4 w-4" />
                    Tambah Soal
                </a>
            </div>
        </div>
    </div>

    {{-- Filter pills --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
        @foreach ([
            'active' => ['label' => 'Aktif', 'icon' => 'check-circle'],
            'trashed' => ['label' => 'Sampah', 'icon' => 'trash'],
            'all' => ['label' => 'Semua', 'icon' => 'grid'],
        ] as $value => $meta)
            <a href="{{ route('admin.management.soal.index', ['filter' => $value]) }}"
               class="inline-flex h-12 items-center gap-2 rounded-full border px-5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'border-green-200 bg-green-50 text-green-700 shadow-[0_6px_16px_rgba(34,197,94,.18)] dark:border-green-500/30 dark:bg-green-500/15 dark:text-green-400' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                <x-player.icon :name="$meta['icon']" class="h-4 w-4" />
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Search + kategori + filter --}}
    <form method="GET" class="mt-4 flex flex-col gap-3 lg:flex-row lg:items-center">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400 dark:text-slate-500">
                <x-player.icon name="search" class="h-5 w-5" />
            </span>
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari pertanyaan..."
                class="h-[58px] w-full rounded-2xl border-slate-200 pl-12 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500"
            >
        </div>

        <select name="kategori_id"
                class="h-[58px] rounded-2xl border-slate-200 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 lg:w-64">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriOptions as $id => $nama)
                <option value="{{ $id }}" @selected((string) request('kategori_id') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>

        <button type="submit"
                class="inline-flex h-[58px] shrink-0 items-center justify-center gap-2 rounded-2xl bg-[#1E293B] px-6 text-sm font-semibold text-white shadow-sm transition duration-200 ease-in-out hover:bg-slate-800">
            <x-player.icon name="filter" class="h-4 w-4" />
            Filter
        </button>
    </form>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-[26px] border border-slate-100 bg-white shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-700">
                <thead>
                    <tr class="h-16 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 dark:!bg-slate-900/50" style="background: linear-gradient(90deg, #ECFDF5, #F0FDF4);">
                        <th class="px-4 sm:px-6">#</th>
                        <th class="px-4 sm:px-6">Pertanyaan</th>
                        <th class="px-4 sm:px-6">Kategori</th>
                        <th class="px-4 sm:px-6">Kunci</th>
                        <th class="px-4 sm:px-6">Status</th>
                        <th class="px-4 text-right sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($soalList as $index => $soal)
                        <tr class="h-[72px] transition-colors duration-200 hover:bg-green-50/40 dark:hover:bg-green-500/10">
                            <td class="px-4 text-slate-500 dark:text-slate-400 sm:px-6">{{ $soalList->firstItem() + $index }}</td>
                            <td class="max-w-md px-4 sm:px-6">
                                <p class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $soal->pertanyaan }}</p>
                            </td>
                            <td class="px-4 sm:px-6">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 px-3 py-1.5 text-xs font-semibold text-violet-700 dark:bg-violet-500/15 dark:text-violet-400">
                                    <x-player.icon name="chart-bar" class="h-3.5 w-3.5" />
                                    {{ $soal->kategori->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6">
                                <span class="flex h-10 w-12 items-center justify-center rounded-lg bg-green-100 text-base font-bold text-green-700 dark:bg-green-500/15 dark:text-green-400">
                                    {{ $soal->kunci_jawaban }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6">
                                @if ($soal->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @elseif ($soal->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($soal->trashed())
                                        <form method="POST" action="{{ route('admin.management.soal.restore', $soal->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" aria-label="Pulihkan soal {{ $soal->id }}"
                                                    class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-green-200 text-green-600 transition duration-200 ease-in-out hover:-translate-y-0.5 hover:border-green-400 hover:bg-green-50 hover:shadow-md dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/15">
                                                <x-player.icon name="refresh" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.management.soal.edit', $soal) }}" aria-label="Edit soal {{ $soal->id }}"
                                           class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 hover:shadow-md dark:border-slate-700 dark:text-slate-400 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/15 dark:hover:text-blue-400">
                                            <x-player.icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.management.soal.destroy', $soal) }}"
                                              onsubmit="return confirm('Hapus soal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus soal {{ $soal->id }}"
                                                    class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-50 hover:text-red-600 hover:shadow-md dark:border-slate-700 dark:text-slate-400 dark:hover:border-red-500/50 dark:hover:bg-red-500/15 dark:hover:text-red-400">
                                                <x-player.icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">Tidak ada data soal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan {{ $soalList->firstItem() ?? 0 }} - {{ $soalList->lastItem() ?? 0 }} dari {{ $soalList->total() }} soal
        </p>
        {{ $soalList->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
