<x-admin-layout>
    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 sm:text-3xl">Feedback &amp; Laporan Bug</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Masukan dan laporan bug yang dikirim pemain</p>
        </div>
    </div>

    {{-- Filter pills --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach ([
            'semua' => ['label' => 'Semua', 'icon' => 'grid'],
            'baru' => ['label' => 'Baru', 'icon' => 'mail'],
            'dibaca' => ['label' => 'Dibaca', 'icon' => 'eye'],
            'selesai' => ['label' => 'Selesai', 'icon' => 'check-circle'],
        ] as $value => $meta)
            <a href="{{ route('admin.management.feedback.index', ['filter' => $value]) }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-500/30 dark:bg-green-500/15 dark:text-green-400' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                <x-player.icon :name="$meta['icon']" class="h-4 w-4" />
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                <x-player.icon name="mail" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Total Masukan</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-500 dark:bg-amber-500/15 dark:text-amber-400">
                <x-player.icon name="alert" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Baru</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['baru']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500 dark:bg-violet-500/15 dark:text-violet-400">
                <x-player.icon name="eye" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Dibaca</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['dibaca']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                <x-player.icon name="check-circle" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Selesai</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['selesai']) }}</p>
        </div>
    </div>

    {{-- List --}}
    <div class="mt-6 space-y-3">
        @forelse ($feedback as $item)
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $item->type->value === 'bug' ? 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400' }}">
                                <x-player.icon :name="$item->type->value === 'bug' ? 'alert' : 'mail'" class="h-3.5 w-3.5" />
                                {{ $item->type->label() }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ $item->status->label() }}
                            </span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 font-semibold text-slate-800 dark:text-slate-100">{{ $item->subject }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $item->message }}</p>
                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Dari: {{ $item->user->name ?? 'Pengguna dihapus' }} ({{ $item->user->email ?? '-' }})</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <form method="POST" action="{{ route('admin.management.feedback.update-status', $item) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                    class="rounded-xl border-slate-200 text-sm text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                <option value="baru" @selected($item->status->value === 'baru')>Baru</option>
                                <option value="dibaca" @selected($item->status->value === 'dibaca')>Dibaca</option>
                                <option value="selesai" @selected($item->status->value === 'selesai')>Selesai</option>
                            </select>
                        </form>
                        <form method="POST" action="{{ route('admin.management.feedback.destroy', $item) }}"
                              onsubmit="return confirm('Hapus masukan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Hapus"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-500 transition duration-200 ease-in-out hover:border-red-400 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/15">
                                <x-player.icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-slate-100 bg-white p-10 text-center text-slate-400 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                Belum ada masukan.
            </div>
        @endforelse
    </div>

    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan {{ $feedback->firstItem() ?? 0 }} - {{ $feedback->lastItem() ?? 0 }} dari {{ $feedback->total() }} masukan
        </p>
        {{ $feedback->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
