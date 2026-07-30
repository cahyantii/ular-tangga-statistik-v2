<x-admin-layout>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-5xl">Kelola Papan Permainan</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">Kelola papan, petak, dan konektor ular tangga yang digunakan dalam permainan</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.management.papan-permainan.import.create') }}"
               class="inline-flex h-11 shrink-0 items-center gap-2 rounded-xl border border-violet-500 bg-white px-4 text-sm font-semibold text-violet-600 shadow-sm transition duration-200 ease-in-out hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800 dark:hover:bg-slate-700">
                <x-player.icon name="upload-cloud" class="h-4 w-4" />
                Import
            </a>
            <a href="{{ route('admin.management.papan-permainan.create') }}"
               class="admin-nav-active inline-flex h-11 shrink-0 items-center gap-2 rounded-xl px-5 text-sm font-semibold text-white shadow-sm transition duration-300 ease-in-out hover:-translate-y-0.5">
                <x-player.icon name="plus" class="h-4 w-4" />
                Tambah Papan
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                <x-player.icon name="grid" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Total Papan</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['papan']) }}</p>
            <p class="mt-1 text-xs font-medium text-green-600 dark:text-green-400">Papan tersedia</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                <x-player.icon name="target" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Total Petak</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['petak']) }}</p>
            <p class="mt-1 text-xs font-medium text-blue-600 dark:text-blue-400">Seluruh papan</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500 dark:bg-violet-500/15 dark:text-violet-400">
                <x-player.icon name="ladder" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Total Konektor</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['konektor']) }}</p>
            <p class="mt-1 text-xs font-medium text-violet-500 dark:text-violet-400">Ular &amp; tangga</p>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-500 dark:bg-orange-500/15 dark:text-orange-400">
                <x-player.icon name="check-circle" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Status Aktif</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['aktif']) }}</p>
            <p class="mt-1 text-xs font-medium text-orange-500 dark:text-orange-400">{{ $stats['papan'] > 0 ? round($stats['aktif'] / $stats['papan'] * 100) : 0 }}% aktif</p>
        </div>
    </div>

    {{-- Filter pills --}}
    <div class="mt-6 flex flex-wrap items-center gap-2">
        @foreach ([
            'active' => ['label' => 'Aktif', 'icon' => 'check-circle'],
            'trashed' => ['label' => 'Sampah', 'icon' => 'trash'],
            'all' => ['label' => 'Semua', 'icon' => 'grid'],
        ] as $value => $meta)
            <a href="{{ route('admin.management.papan-permainan.index', ['filter' => $value]) }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-500/30 dark:bg-green-500/15 dark:text-green-400' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                <x-player.icon :name="$meta['icon']" class="h-4 w-4" />
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Card grid --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($papanList as $papan)
            <div class="flex flex-col rounded-[26px] border border-slate-100 bg-white p-6 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-start justify-between gap-3">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                        <x-player.icon name="gamepad" class="h-7 w-7" />
                    </span>

                    @if ($papan->trashed())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                            Dihapus
                        </span>
                    @elseif ($papan->is_active)
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
                </div>

                <h3 class="mt-4 text-lg font-bold text-slate-800 dark:text-slate-100">{{ $papan->nama }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $papan->deskripsi ? \Illuminate\Support\Str::limit($papan->deskripsi, 90) : 'Belum ada deskripsi.' }}
                </p>

                <div class="mt-4 grid grid-cols-3 gap-2 rounded-2xl bg-slate-50 p-3 text-center dark:bg-slate-900/50">
                    <div>
                        <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $papan->jumlah_petak }}</p>
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Petak</p>
                    </div>
                    <div>
                        <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $papan->jumlah_kolom }}</p>
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Kolom</p>
                    </div>
                    <div>
                        <p class="text-base font-bold text-slate-800 dark:text-slate-100">{{ $papan->papan_konektor_count }}</p>
                        <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Konektor</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
                    @if ($papan->trashed())
                        <form method="POST" action="{{ route('admin.management.papan-permainan.restore', $papan->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-green-200 px-3 py-2 text-xs font-semibold text-green-600 transition duration-200 ease-in-out hover:border-green-400 hover:bg-green-50 dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/15">
                                <x-player.icon name="refresh" class="h-3.5 w-3.5" />
                                Pulihkan
                            </button>
                        </form>
                    @else
                        <a href="{{ route('admin.management.papan-permainan.preview', $papan) }}" title="Preview"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/15 dark:hover:text-blue-400">
                            <x-player.icon name="eye" class="h-4 w-4" />
                        </a>
                        <a href="{{ route('admin.management.papan-permainan.petak.index', $papan) }}" title="Editor Petak"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-green-300 hover:bg-green-50 hover:text-green-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-green-500/50 dark:hover:bg-green-500/15 dark:hover:text-green-400">
                            <x-player.icon name="grid" class="h-4 w-4" />
                        </a>
                        <a href="{{ route('admin.management.papan-permainan.konektor.index', $papan) }}" title="Konektor"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-violet-300 hover:bg-violet-50 hover:text-violet-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-violet-500/50 dark:hover:bg-violet-500/15 dark:hover:text-violet-400">
                            <x-player.icon name="ladder" class="h-4 w-4" />
                        </a>

                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" @click.outside="open = false" title="Export"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-orange-500/50 dark:hover:bg-orange-500/15 dark:hover:text-orange-400">
                                <x-player.icon name="download" class="h-4 w-4" />
                            </button>
                            <div x-show="open" x-transition x-cloak
                                 class="absolute bottom-full left-0 z-10 mb-2 w-44 overflow-hidden rounded-xl border border-slate-100 bg-white py-1.5 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                                <a href="{{ route('admin.management.papan-permainan.export', ['papan_permainan' => $papan, 'format' => 'json']) }}"
                                   class="block px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-700">Export .json (papan penuh)</a>
                                <a href="{{ route('admin.management.papan-permainan.export', ['papan_permainan' => $papan, 'format' => 'xlsx']) }}"
                                   class="block px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-700">Export .xlsx (petak)</a>
                                <a href="{{ route('admin.management.papan-permainan.export', ['papan_permainan' => $papan, 'format' => 'csv']) }}"
                                   class="block px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-700">Export .csv (petak)</a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.management.papan-permainan.toggle-active', $papan) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="{{ $papan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-amber-300 hover:bg-amber-50 hover:text-amber-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-amber-500/50 dark:hover:bg-amber-500/15 dark:hover:text-amber-400">
                                <x-player.icon :name="$papan->is_active ? 'eye-off' : 'check-circle'" class="h-4 w-4" />
                            </button>
                        </form>

                        <a href="{{ route('admin.management.papan-permainan.edit', $papan) }}" title="Edit"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/15 dark:hover:text-blue-400">
                            <x-player.icon name="pencil" class="h-4 w-4" />
                        </a>
                        <form method="POST" action="{{ route('admin.management.papan-permainan.destroy', $papan) }}"
                              onsubmit="return confirm('Hapus papan {{ $papan->nama }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-red-500/50 dark:hover:bg-red-500/15 dark:hover:text-red-400">
                                <x-player.icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[26px] border border-slate-100 bg-white p-10 text-center text-slate-400 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                Tidak ada data papan permainan.
            </div>
        @endforelse
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan {{ $papanList->firstItem() ?? 0 }} - {{ $papanList->lastItem() ?? 0 }} dari {{ $papanList->total() }} papan
        </p>
        {{ $papanList->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
