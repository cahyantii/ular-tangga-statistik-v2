<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-slate-100">Main Multiplayer</h2>
    </x-slot>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-lg font-bold text-slate-800 dark:text-slate-100">Quick Match</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Dipasangkan otomatis dengan pemain lain yang juga sedang mencari lawan.</p>
            <form method="POST" action="{{ route('game.multiplayer.quick-match') }}" class="mt-4">
                @csrf
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Warna pion kamu</p>
                <div class="mb-4">
                    <x-player.pawn-color-picker :dark="false" />
                </div>
                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Cari Lawan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            <p class="text-lg font-bold text-slate-800 dark:text-slate-100">Private Room</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat room dan bagikan kodenya ke teman, atau masukkan kode room teman.</p>

            <form method="POST" action="{{ route('game.multiplayer.room.store') }}" class="mt-4">
                @csrf
                <label for="jumlah_pemain" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Jumlah pemain</label>
                <select name="jumlah_pemain" id="jumlah_pemain" class="mb-4 w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    @foreach (range(2, 6) as $n)
                        <option value="{{ $n }}" {{ old('jumlah_pemain', 2) == $n ? 'selected' : '' }}>{{ $n }} pemain</option>
                    @endforeach
                </select>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Warna pion kamu</p>
                <div class="mb-4">
                    <x-player.pawn-color-picker :dark="false" />
                </div>
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600">
                    Buat Room
                </button>
            </form>

            <form method="POST" action="{{ route('game.multiplayer.room.join') }}" class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-700">
                @csrf
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Warna pion kamu</p>
                <div class="mb-3">
                    <x-player.pawn-color-picker :dark="false" />
                </div>
                <div class="flex gap-2">
                    <input type="text" name="kode_room" maxlength="6" placeholder="KODE ROOM"
                           value="{{ old('kode_room') }}"
                           class="w-full rounded-lg border-slate-300 text-sm uppercase tracking-widest focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500">
                    <button type="submit" class="shrink-0 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                        Gabung
                    </button>
                </div>
            </form>
            @error('kode_room')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-player-layout>
