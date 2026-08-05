<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800 dark:text-dark-text">Main Multiplayer</h2>
    </x-slot>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-sm text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ openNoRoomModal: {{ session('no_room_available') ? 'true' : 'false' }} }">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface p-6 shadow-sm">
                <p class="text-lg font-bold text-slate-800 dark:text-dark-text">Quick Match</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-dark-muted">Cari room yang sedang menunggu lawan secara otomatis untuk langsung bergabung.</p>
                <form method="POST" action="{{ route('game.multiplayer.quick-match') }}" class="mt-4">
                    @csrf
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-dark-muted">Warna pion kamu</p>
                    <div class="mb-4">
                        <x-player.pawn-color-picker :dark="false" :selected="session('selected_pawn_color')" />
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm">
                        Cari Lawan
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface p-6 shadow-sm">
                <p class="text-lg font-bold text-slate-800 dark:text-dark-text">Private Room</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-dark-muted">Buat room dan bagikan kodenya ke teman, atau masukkan kode room teman.</p>

                <form id="create-room-form" method="POST" action="{{ route('game.multiplayer.room.store') }}" class="mt-4">
                    @csrf
                    <label for="jumlah_pemain" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-dark-muted">Jumlah pemain</label>
                    <select name="jumlah_pemain" id="jumlah_pemain" class="mb-4 w-full rounded-lg border-slate-300 dark:border-dark-border bg-white dark:bg-dark-surface2 text-sm text-slate-800 dark:text-dark-text focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach (range(2, 6) as $n)
                            <option value="{{ $n }}" {{ old('jumlah_pemain', 2) == $n ? 'selected' : '' }}>{{ $n }} pemain</option>
                        @endforeach
                    </select>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-dark-muted">Warna pion kamu</p>
                    <div class="mb-4">
                        <x-player.pawn-color-picker :dark="false" :selected="session('selected_pawn_color')" />
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-slate-800 dark:bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900 dark:hover:bg-primary-700 transition-colors shadow-sm">
                        Buat Room
                    </button>
                </form>

                <form method="POST" action="{{ route('game.multiplayer.room.join') }}" class="mt-5 border-t border-slate-100 dark:border-dark-border pt-4">
                    @csrf
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-dark-muted">Warna pion kamu</p>
                    <div class="mb-3">
                        <x-player.pawn-color-picker :dark="false" :selected="session('selected_pawn_color')" />
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="kode_room" maxlength="6" placeholder="KODE ROOM"
                               value="{{ old('kode_room') }}"
                               class="w-full rounded-lg border-slate-300 dark:border-dark-border bg-white dark:bg-dark-surface2 text-sm text-slate-800 dark:text-dark-text uppercase tracking-widest placeholder:text-slate-400 dark:placeholder:text-dark-muted focus:border-emerald-500 focus:ring-emerald-500">
                        <button type="submit" class="shrink-0 rounded-lg border border-slate-300 dark:border-dark-border px-4 py-2 text-sm font-medium text-slate-700 dark:text-dark-text hover:bg-slate-50 dark:hover:bg-dark-surface2 transition-colors">
                            Gabung
                        </button>
                    </div>
                </form>
                @error('kode_room')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- MODAL ROOM TIDAK TERSEDIA -->
        <div x-show="openNoRoomModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-dark-border bg-white dark:bg-dark-surface p-6 shadow-2xl text-center"
                 @click.away="openNoRoomModal = false">
                <!-- Icon Search / Warning -->
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 4h.01" />
                    </svg>
                </div>

                <h3 class="mt-4 text-xl font-bold text-slate-800 dark:text-dark-text">Room Tidak Tersedia</h3>
                <p class="mt-2 text-sm text-slate-600 dark:text-dark-muted">
                    Saat ini belum ada pemain lain yang sedang menunggu room. Kamu dapat membuat room baru untuk menunggu pemain lain, atau bermain langsung melawan Robot.
                </p>

                <div class="mt-6 flex flex-col gap-3">
                    <!-- Tombol Buat Room -->
                    <button type="button"
                            @click="openNoRoomModal = false; document.getElementById('create-room-form').submit();"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors shadow-sm">
                        Buat Room Baru
                    </button>

                    <!-- Tombol Main dengan Robot -->
                    <form method="POST" action="{{ route('game.robot.store') }}">
                        @csrf
                        <input type="hidden" name="pawn_color" value="{{ session('selected_pawn_color', \App\Enums\PawnColor::Biru->value) }}">
                        <button type="submit"
                                class="w-full rounded-xl border border-slate-300 dark:border-dark-border bg-slate-100 dark:bg-dark-surface2 px-4 py-2.5 text-sm font-semibold text-slate-800 dark:text-dark-text hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            Main dengan Robot
                        </button>
                    </form>

                    <!-- Tombol Tutup -->
                    <button type="button"
                            @click="openNoRoomModal = false"
                            class="text-xs font-medium text-slate-400 dark:text-dark-muted hover:text-slate-600 dark:hover:text-slate-200 mt-1">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-player-layout>
