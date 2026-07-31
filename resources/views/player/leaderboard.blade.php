@php
    $tabs = [
        'global' => ['label' => 'Global', 'icon' => null],
        'robot' => ['label' => 'Vs Robot', 'icon' => 'cpu'],
        'multiplayer' => ['label' => 'Multiplayer', 'icon' => 'users'],
    ];
@endphp

<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 sm:text-2xl">Leaderboard</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Bersaing dan raih posisi terbaikmu! &#127942;</p>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $item)
                @php $active = $tab === $key; @endphp
                <a
                    href="{{ route('leaderboard', ['tab' => $key]) }}"
                    aria-current="{{ $active ? 'page' : 'false' }}"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 {{ $active ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-soft' : 'leaderboard-tab-inactive bg-white text-slate-600 hover:-translate-y-0.5 hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}"
                >
                    @if ($item['icon'])
                        <x-player.icon :name="$item['icon']" class="h-4 w-4" />
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                @if ($board['top3']->isNotEmpty())
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-end lg:grid-cols-3">
                        @foreach ($board['top3'] as $row)
                            @php
                                $rank = $row['rank'];
                                $order = match ($rank) {
                                    1 => 'lg:order-2',
                                    2 => 'lg:order-1',
                                    default => 'lg:order-3',
                                };
                            @endphp
                            <div class="{{ $order }}">
                                <x-player.leaderboard-podium-card :rank="$rank" :row="$row" />
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="overflow-hidden rounded-3xl bg-white shadow-sm transition-shadow duration-300 hover:shadow-soft dark:bg-slate-800">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-700/60 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">#</th>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">Nama</th>
                                    <th scope="col" class="px-4 py-3.5 sm:px-6">Badge</th>
                                    <th scope="col" class="px-4 py-3.5 text-right sm:px-6">Total Skor</th>
                                    <th scope="col" class="px-4 py-3.5 text-right sm:px-6">Menang</th>
                                    <th scope="col" class="px-4 py-3.5 text-right sm:px-6">Main</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @if ($board['table']->isEmpty() && ! $board['current'])
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 sm:px-6">
                                            {{ $board['top3']->isEmpty() ? 'Belum ada data untuk kategori ini.' : 'Belum ada pemain lain di peringkat ini.' }}
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($board['table'] as $row)
                                        <x-player.leaderboard-row :row="$row" />
                                    @endforeach

                                    @if ($board['current'])
                                        <x-player.leaderboard-row :row="$board['current']" :highlight="true" />
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <x-player.leaderboard-promo-card />
                <x-player.leaderboard-stats-card :current="$board['current']" :totalPemain="$board['total_pemain']" />
                <x-player.tips-card :tip="$tip" title="Tips" />
            </div>
        </div>
    </div>
</x-player-layout>
