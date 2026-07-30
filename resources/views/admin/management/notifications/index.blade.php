<x-admin-layout>
    @php
        $categoryMeta = [
            'semua' => ['label' => 'Semua', 'icon' => 'grid'],
            'game' => ['label' => 'Game', 'icon' => 'gamepad'],
            'user' => ['label' => 'User', 'icon' => 'user-plus'],
            'achievement' => ['label' => 'Achievement', 'icon' => 'trophy'],
            'system' => ['label' => 'System', 'icon' => 'settings'],
            'multiplayer' => ['label' => 'Multiplayer', 'icon' => 'users'],
            'feedback' => ['label' => 'Feedback', 'icon' => 'mail'],
        ];
        $colorClasses = [
            'green' => 'bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400',
            'red' => 'bg-red-100 text-red-500 dark:bg-red-500/15 dark:text-red-400',
            'blue' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
            'purple' => 'bg-violet-100 text-violet-500 dark:bg-violet-500/15 dark:text-violet-400',
            'amber' => 'bg-amber-100 text-amber-500 dark:bg-amber-500/15 dark:text-amber-400',
        ];
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 sm:text-3xl">Notification Center</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Seluruh aktivitas sistem &amp; pemain yang perlu diketahui admin</p>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            {{-- loading dicek supaya tidak bisa diklik berkali-kali sebelum
                 request pertama selesai (dulu bisa numpuk beberapa PATCH
                 sekaligus), dan .catch() memastikan kalau requestnya gagal
                 (koneksi putus/419/500), tombolnya kembali aktif dengan
                 pesan di console alih-alih diam-diam tidak melakukan apa-apa
                 tanpa penjelasan. --}}
            <button
                type="button"
                x-data="{ loading: false }"
                :disabled="loading"
                @click="
                    loading = true;
                    fetch('{{ route('notifications.read-all') }}', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
                        .then((res) => { if (!res.ok) throw new Error('HTTP ' + res.status); window.location.reload(); })
                        .catch((err) => { console.error('Gagal menandai semua notifikasi sebagai dibaca:', err); loading = false; })
                "
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
                <x-player.icon name="check-circle" class="h-4 w-4" />
                <span x-text="loading ? 'Memproses...' : 'Tandai Semua Dibaca'"></span>
            </button>

            <a href="{{ route('admin.management.notifications.export', request()->query()) }}"
               class="admin-nav-active inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white transition duration-300 ease-in-out hover:-translate-y-0.5">
                <x-player.icon name="download" class="h-4 w-4" />
                Export
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                <x-player.icon name="calendar" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Hari Ini</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['hari_ini']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                <x-player.icon name="chart-bar" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Minggu Ini</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['minggu_ini']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500 dark:bg-violet-500/15 dark:text-violet-400">
                <x-player.icon name="pie-chart" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Bulan Ini</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['bulan_ini']) }}</p>
        </div>
        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-500 dark:bg-amber-500/15 dark:text-amber-400">
                <x-player.icon name="bell" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Belum Dibaca</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['belum_dibaca']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mt-6 space-y-4 rounded-3xl border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800 sm:p-5">
        <div class="flex flex-wrap items-center gap-2">
            @foreach (['semua' => 'Semua', 'unread' => 'Belum Dibaca', 'read' => 'Sudah Dibaca'] as $value => $label)
                <a href="{{ route('admin.management.notifications.index', array_merge(request()->except('page'), ['status' => $value])) }}"
                   class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $status === $value ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-500/30 dark:bg-green-500/15 dark:text-green-400' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($categoryMeta as $value => $meta)
                <a href="{{ route('admin.management.notifications.index', array_merge(request()->except('page'), ['category' => $value])) }}"
                   class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 text-xs font-semibold shadow-sm transition duration-200 ease-in-out {{ $category === $value ? 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-400' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                    <x-player.icon :name="$meta['icon']" class="h-3.5 w-3.5" />
                    {{ $meta['label'] }}
                </a>
            @endforeach
        </div>

        <input type="hidden" name="status" value="{{ $status }}">
        <input type="hidden" name="category" value="{{ $category }}">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                    <x-player.icon name="search" class="h-4 w-4" />
                </span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau pesan notifikasi..."
                       class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
            </div>
            <input type="date" name="from" value="{{ $from }}" max="{{ now()->toDateString() }}"
                   class="rounded-xl border-slate-200 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            <input type="date" name="to" value="{{ $to }}" max="{{ now()->toDateString() }}"
                   class="rounded-xl border-slate-200 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
            <button type="submit" class="admin-nav-active inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                <x-player.icon name="filter" class="h-4 w-4" />
                Terapkan
            </button>
        </div>
    </form>

    {{-- List --}}
    <div class="mt-6 space-y-3">
        @forelse ($notifications as $notification)
            @php $color = $colorClasses[$notification->data['color'] ?? 'blue'] ?? $colorClasses['blue']; @endphp
            <div class="flex items-start gap-4 rounded-3xl border {{ $notification->read_at ? 'border-slate-100 dark:border-slate-700' : 'border-green-200 bg-green-50/30 dark:border-green-500/30 dark:bg-green-500/10' }} bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:bg-slate-800">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $color }}">
                    <x-player.icon :name="$notification->data['icon'] ?? 'bell'" class="h-5 w-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $notification->data['title'] ?? '' }}</p>
                        @unless ($notification->read_at)
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        @endunless
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $notification->data['message'] ?? '' }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if (!empty($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" aria-label="Lihat"
                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/15 dark:hover:text-blue-400">
                            <x-player.icon name="eye" class="h-4 w-4" />
                        </a>
                    @endif
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" aria-label="Tandai Dibaca"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 text-green-600 hover:border-green-400 hover:bg-green-50 dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/15">
                                <x-player.icon name="check" class="h-4 w-4" />
                            </button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('Hapus notifikasi ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Hapus"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-500 hover:border-red-400 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/15">
                            <x-player.icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-slate-100 bg-white p-10 text-center text-slate-400 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                Tidak ada notifikasi.
            </div>
        @endforelse
    </div>

    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} dari {{ $notifications->total() }} notifikasi
        </p>
        {{ $notifications->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
