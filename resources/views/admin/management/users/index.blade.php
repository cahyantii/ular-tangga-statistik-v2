<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Kelola Pengguna</h1>
        <a href="{{ route('admin.management.users.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
            + Tambah Pengguna
        </a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        @foreach (['active' => 'Aktif', 'trashed' => 'Sampah', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('admin.management.users.index', ['filter' => $value]) }}"
               class="rounded-full px-3 py-1 font-medium {{ $filter === $value ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
               class="w-64 rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <select name="role" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua Role</option>
            <option value="player" @selected(request('role') === 'player')>Player</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
            Filter
        </button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $user->role->value === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            @if ($user->trashed())
                                <span class="text-red-500">Dihapus</span>
                            @elseif ($user->role->value === 'admin' && ! $user->hasVerifiedEmail())
                                <span class="text-amber-600">Belum verifikasi</span>
                            @else
                                <span class="text-emerald-600">Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($user->trashed())
                                <form method="POST" action="{{ route('admin.management.users.restore', $user->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800">Pulihkan</button>
                                </form>
                            @else
                                <a href="{{ route('admin.management.users.edit', $user) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                                <form method="POST" action="{{ route('admin.management.users.destroy', $user) }}" class="inline"
                                      onsubmit="return confirm('Hapus pengguna {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-admin-layout>
