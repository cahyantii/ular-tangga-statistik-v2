<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Leaderboard</h2>
    </x-slot>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach (['global' => 'Global', 'robot' => 'Vs Robot', 'multiplayer' => 'Multiplayer'] as $key => $label)
            <a href="{{ route('leaderboard', ['tab' => $key]) }}"
               class="rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 {{ $tab === $key ? 'bg-primary-500 text-white shadow-soft' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-3xl bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3 text-right">Total Skor</th>
                    <th class="px-4 py-3 text-right">Menang</th>
                    <th class="px-4 py-3 text-right">Main</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($peringkat as $index => $row)
                    <tr class="transition-colors {{ $row['user_id'] === $currentUserId ? 'bg-primary-50' : 'hover:bg-slate-50' }}">
                        <td class="px-4 py-3 font-semibold text-slate-500">
                            @if ($index < 3)
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full {{ ['bg-accent-500 text-white', 'bg-slate-300 text-white', 'bg-accent-700 text-white'][$index] }}">{{ $index + 1 }}</span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $row['nama'] }}{{ $row['user_id'] === $currentUserId ? ' (Anda)' : '' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-primary-600">{{ $row['total_skor'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $row['total_menang'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ $row['total_main'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada data untuk kategori ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-player-layout>
