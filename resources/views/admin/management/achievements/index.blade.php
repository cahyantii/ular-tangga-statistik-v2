<x-admin-layout>
    @php
        $palette = [
            'green' => ['badge' => 'bg-gradient-to-br from-green-400 to-green-600', 'shadow' => 'shadow-[0_8px_18px_rgba(34,197,94,.35)]', 'code' => 'text-green-600', 'pastelBg' => 'bg-green-50', 'pastelText' => 'text-green-700', 'pastelIcon' => 'text-green-500'],
            'blue' => ['badge' => 'bg-gradient-to-br from-blue-400 to-blue-600', 'shadow' => 'shadow-[0_8px_18px_rgba(37,99,235,.35)]', 'code' => 'text-blue-600', 'pastelBg' => 'bg-blue-50', 'pastelText' => 'text-blue-700', 'pastelIcon' => 'text-blue-500'],
            'orange' => ['badge' => 'bg-gradient-to-br from-orange-400 to-orange-600', 'shadow' => 'shadow-[0_8px_18px_rgba(249,115,22,.35)]', 'code' => 'text-orange-600', 'pastelBg' => 'bg-orange-50', 'pastelText' => 'text-orange-700', 'pastelIcon' => 'text-orange-500'],
            'purple' => ['badge' => 'bg-gradient-to-br from-purple-400 to-purple-600', 'shadow' => 'shadow-[0_8px_18px_rgba(147,51,234,.35)]', 'code' => 'text-purple-600', 'pastelBg' => 'bg-purple-50', 'pastelText' => 'text-purple-700', 'pastelIcon' => 'text-purple-500'],
            'pink' => ['badge' => 'bg-gradient-to-br from-pink-400 to-pink-600', 'shadow' => 'shadow-[0_8px_18px_rgba(236,72,153,.35)]', 'code' => 'text-pink-600', 'pastelBg' => 'bg-pink-50', 'pastelText' => 'text-pink-700', 'pastelIcon' => 'text-pink-500'],
            'amber' => ['badge' => 'bg-gradient-to-br from-amber-400 to-amber-600', 'shadow' => 'shadow-[0_8px_18px_rgba(217,119,6,.35)]', 'code' => 'text-amber-600', 'pastelBg' => 'bg-amber-50', 'pastelText' => 'text-amber-700', 'pastelIcon' => 'text-amber-500'],
            'turquoise' => ['badge' => 'bg-gradient-to-br from-teal-400 to-teal-600', 'shadow' => 'shadow-[0_8px_18px_rgba(20,184,166,.35)]', 'code' => 'text-teal-600', 'pastelBg' => 'bg-teal-50', 'pastelText' => 'text-teal-700', 'pastelIcon' => 'text-teal-500'],
        ];
        $fallback = $palette['blue'];

        $syaratMeta = [
            'total_menang' => 'flag',
            'total_permainan' => 'gamepad',
            'akurasi_keseluruhan' => 'target',
            'selisih_kemenangan_terbesar' => 'trend-up',
            'total_angka_enam' => 'dice',
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Kelola Achievement</h1>
            <p class="mt-2 text-slate-500">Kelola dan atur achievement yang dapat diperoleh pemain dalam permainan.</p>
        </div>

        <a href="{{ route('admin.management.achievements.create') }}"
           class="admin-nav-active inline-flex shrink-0 items-center gap-2 self-start rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-300 ease-in-out hover:-translate-y-0.5 sm:self-auto">
            <x-player.icon name="plus" class="h-4 w-4" />
            Tambah Achievement
        </a>
    </div>

    {{-- Filter pills --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['active' => 'Aktif', 'trashed' => 'Sampah', 'all' => 'Semua'] as $value => $label)
            <a href="{{ route('admin.management.achievements.index', ['filter' => $value]) }}"
               class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold shadow-sm transition duration-200 ease-in-out {{ $filter === $value ? 'admin-nav-active text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_10px_40px_rgba(0,0,0,.06)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50/70">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Achievement</th>
                        <th class="px-6 py-4">Syarat</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($achievements as $achievement)
                        @php
                            $colors = $palette[$achievement->warna_badge] ?? $fallback;
                            $syaratIcon = $syaratMeta[$achievement->syarat_type->value] ?? 'flag';
                            $isPercent = $achievement->syarat_type->value === 'akurasi_keseluruhan';
                        @endphp
                        <tr class="transition-colors duration-200 hover:bg-slate-50/60">
                            <td class="px-6 py-5">
                                <div class="flex items-start gap-4">
                                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $colors['badge'] }} {{ $colors['shadow'] }} text-white">
                                        <x-player.icon :name="$achievement->icon ?? 'trophy'" class="h-7 w-7" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wide {{ $colors['code'] }}">{{ $achievement->kode }}</p>
                                        <p class="mt-0.5 font-bold text-slate-800">{{ $achievement->nama }}</p>
                                        @if ($achievement->deskripsi)
                                            <p class="mt-0.5 text-sm text-slate-500">{{ $achievement->deskripsi }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center gap-3 rounded-2xl {{ $colors['pastelBg'] }} px-4 py-2.5">
                                    <x-player.icon :name="$syaratIcon" class="h-5 w-5 shrink-0 {{ $colors['pastelIcon'] }}" />
                                    <div class="leading-tight">
                                        <p class="text-sm font-semibold {{ $colors['pastelText'] }}">{{ $achievement->syarat_type->label() }}</p>
                                        <p class="text-xs font-medium {{ $colors['pastelText'] }} opacity-80">&ge; {{ $achievement->syarat_value }}{{ $isPercent ? '%' : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if ($achievement->trashed())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Dihapus
                                    </span>
                                @elseif ($achievement->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($achievement->trashed())
                                        <form method="POST" action="{{ route('admin.management.achievements.restore', $achievement->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-green-500 px-3.5 py-2 text-xs font-semibold text-green-600 transition duration-200 ease-in-out hover:bg-green-50">
                                                <x-player.icon name="refresh" class="h-3.5 w-3.5" />
                                                Pulihkan
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.management.achievements.edit', $achievement) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-blue-500 px-3.5 py-2 text-xs font-semibold text-blue-600 transition duration-200 ease-in-out hover:bg-blue-50">
                                            <x-player.icon name="pencil" class="h-3.5 w-3.5" />
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.management.achievements.destroy', $achievement) }}"
                                              onsubmit="return confirm('Hapus achievement {{ $achievement->nama }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-red-500 px-3.5 py-2 text-xs font-semibold text-red-600 transition duration-200 ease-in-out hover:bg-red-50">
                                                <x-player.icon name="trash" class="h-3.5 w-3.5" />
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">Tidak ada data achievement.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Count + Pagination --}}
    <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-sm text-slate-500">
            Menampilkan {{ $achievements->firstItem() ?? 0 }} - {{ $achievements->lastItem() ?? 0 }} dari {{ $achievements->total() }} achievement
        </p>
        {{ $achievements->links('vendor.pagination.admin-users') }}
    </div>
</x-admin-layout>
