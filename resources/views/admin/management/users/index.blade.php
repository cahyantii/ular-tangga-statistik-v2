<x-admin-layout>
    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 sm:text-3xl">Kelola Pengguna</h1>
            <p class="mt-1 text-slate-500 dark:text-slate-400">Kelola semua akun pengguna pada sistem</p>
        </div>

        <a href="{{ route('admin.management.users.create') }}"
           class="admin-nav-active inline-flex shrink-0 items-center gap-2 self-start rounded-full px-5 py-3 text-sm font-semibold text-white transition duration-300 ease-in-out hover:-translate-y-0.5 sm:self-auto">
            <x-player.icon name="user-plus" class="h-4 w-4" />
            Tambah Pengguna
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-green-100 bg-green-50/60 p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-green-500/20 dark:bg-green-500/10">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400">
                <x-player.icon name="users" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Total Pengguna</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['total']) }}</p>
            <p class="mt-1 text-xs font-medium text-green-600 dark:text-green-400">Akun terdaftar</p>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-blue-50/60 p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-blue-500/20 dark:bg-blue-500/10">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400">
                <x-player.icon name="shield" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Pengguna Aktif</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['aktif']) }}</p>
            <p class="mt-1 text-xs font-medium text-blue-600 dark:text-blue-400">{{ $stats['total'] > 0 ? round($stats['aktif'] / $stats['total'] * 100) : 0 }}% dari total</p>
        </div>

        <div class="rounded-3xl border border-orange-100 bg-orange-50/60 p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-orange-500/20 dark:bg-orange-500/10">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-500 dark:bg-orange-500/15 dark:text-orange-400">
                <x-player.icon name="crown" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Admin</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['admin']) }}</p>
            <p class="mt-1 text-xs font-medium text-orange-500 dark:text-orange-400">{{ $stats['total'] > 0 ? round($stats['admin'] / $stats['total'] * 100) : 0 }}% dari total</p>
        </div>

        <div class="rounded-3xl border border-violet-100 bg-violet-50/60 p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)] transition duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-[0_16px_50px_rgba(0,0,0,.09)] dark:border-violet-500/20 dark:bg-violet-500/10">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-violet-500 dark:bg-violet-500/15 dark:text-violet-400">
                <x-player.icon name="user" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">Pemain</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['pemain']) }}</p>
            <p class="mt-1 text-xs font-medium text-violet-500 dark:text-violet-400">{{ $stats['total'] > 0 ? round($stats['pemain'] / $stats['total'] * 100) : 0 }}% dari total</p>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="mt-6 rounded-3xl border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-slate-500">
                    <x-player.icon name="search" class="h-4 w-4" />
                </span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama atau email pengguna..."
                    class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500"
                >
            </div>

            <select name="role" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 py-2.5 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                <option value="">Semua Role</option>
                <option value="player" @selected(request('role') === 'player')>Pemain</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            </select>

            <select name="filter" onchange="this.form.submit()"
                    class="rounded-xl border-slate-200 py-2.5 pl-4 pr-9 text-sm font-medium text-slate-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                <option value="active" @selected($filter === 'active')>Aktif</option>
                <option value="all" @selected($filter === 'all')>Semua Status</option>
                <option value="trashed" @selected($filter === 'trashed')>Dihapus</option>
            </select>

            <a href="{{ route('admin.management.users.index') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-green-500 px-4 py-2.5 text-sm font-semibold text-green-600 transition duration-200 ease-in-out hover:bg-green-50 dark:hover:bg-green-500/15">
                <x-player.icon name="refresh" class="h-4 w-4" />
                Reset Filter
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_40px_rgba(0,0,0,.06)] dark:border-slate-700 dark:bg-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3.5 sm:px-6">#</th>
                        <th class="px-4 py-3.5 sm:px-6">Nama</th>
                        <th class="px-4 py-3.5 sm:px-6">Email</th>
                        <th class="px-4 py-3.5 sm:px-6">Role</th>
                        <th class="px-4 py-3.5 sm:px-6">Status</th>
                        <th class="px-4 py-3.5 sm:px-6">Dibuat</th>
                        <th class="px-4 py-3.5 text-right sm:px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($users as $index => $user)
                        @php
                            $isAdmin = $user->role->value === 'admin';
                            $initial = mb_strtoupper(mb_substr($user->name, 0, 1));
                        @endphp
                        <tr class="transition-colors duration-200 hover:bg-slate-50/60 dark:hover:bg-slate-700/60">
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 sm:px-6">{{ $users->firstItem() + $index }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white {{ $isAdmin ? 'bg-blue-600' : 'bg-green-500' }}">
                                        {{ $initial }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800 dark:text-slate-100">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ $user->role->label() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 sm:px-6">{{ $user->email }}</td>
                            <td class="px-4 py-4 sm:px-6">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isAdmin ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                @if ($user->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600 dark:bg-red-500/15 dark:text-red-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @elseif ($isAdmin && ! $user->hasVerifiedEmail())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Belum verifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-500/15 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 sm:px-6">
                                <p>{{ $user->created_at->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $user->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($user->trashed())
                                        <form method="POST" action="{{ route('admin.management.users.restore', $user->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" aria-label="Pulihkan {{ $user->name }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-green-200 text-green-600 transition duration-200 ease-in-out hover:border-green-400 hover:bg-green-50 dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/15">
                                                <x-player.icon name="refresh" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.management.users.edit', $user) }}" aria-label="Edit {{ $user->name }}"
                                           class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-400 dark:hover:border-blue-500/50 dark:hover:bg-blue-500/15 dark:hover:text-blue-400">
                                            <x-player.icon name="pencil" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.management.users.destroy', $user) }}"
                                              onsubmit="return confirm('Hapus pengguna {{ $user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Hapus {{ $user->name }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-500 transition duration-200 ease-in-out hover:border-red-400 hover:bg-red-50 hover:text-red-700 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/15 dark:hover:text-red-300">
                                                <x-player.icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
        </p>
        {{ $users->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
