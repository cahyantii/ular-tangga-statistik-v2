<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Main Multiplayer</h2>
    </x-slot>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-lg font-bold text-slate-800">Quick Match</p>
            <p class="mt-1 text-sm text-slate-500">Dipasangkan otomatis dengan pemain lain yang juga sedang mencari lawan.</p>
            <form method="POST" action="{{ route('game.multiplayer.quick-match') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Cari Lawan
                </button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <p class="text-lg font-bold text-slate-800">Private Room</p>
            <p class="mt-1 text-sm text-slate-500">Buat room dan bagikan kodenya ke teman, atau masukkan kode room teman.</p>

            <form method="POST" action="{{ route('game.multiplayer.room.store') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                    Buat Room
                </button>
            </form>

            <form method="POST" action="{{ route('game.multiplayer.room.join') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="text" name="kode_room" maxlength="6" placeholder="KODE ROOM"
                       value="{{ old('kode_room') }}"
                       class="w-full rounded-lg border-slate-300 text-sm uppercase tracking-widest focus:border-emerald-500 focus:ring-emerald-500">
                <button type="submit" class="shrink-0 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Gabung
                </button>
            </form>
            @error('kode_room')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</x-player-layout>
