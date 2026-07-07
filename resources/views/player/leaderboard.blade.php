<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Leaderboard</h2>
    </x-slot>

    <div class="mb-6 flex gap-2">
        @foreach (['global' => 'Global', 'robot' => 'Vs Robot', 'multiplayer' => 'Multiplayer'] as $key => $label)
            <a href="{{ route('leaderboard', ['tab' => $key]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium {{ $tab === $key ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
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
                    <tr class="{{ $row['user_id'] === $currentUserId ? 'bg-emerald-50' : '' }}">
                        <td class="px-4 py-3 font-semibold text-slate-500">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $row['nama'] }}{{ $row['user_id'] === $currentUserId ? ' (Anda)' : '' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ $row['total_skor'] }}</td>
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
