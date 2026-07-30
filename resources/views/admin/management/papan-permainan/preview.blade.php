<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/board-visuals.js', 'resources/js/admin-board-preview.js'])
    @endpush

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Preview — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tampilan read-only, persis sama dengan yang dilihat pemain (termasuk pion contoh di petak Start).</p>
        </div>
        <a href="{{ route('admin.management.papan-permainan.index') }}" class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
            &larr; Kembali ke daftar papan
        </a>
    </div>

    <div class="mx-auto w-full max-w-2xl rounded-3xl bg-white p-3 shadow-sm dark:bg-slate-800 sm:p-4">
        <div class="mb-3 flex items-center justify-center gap-2">
            <div id="board-theme-toggle" class="flex gap-2">
                <button type="button" data-board-theme="ular" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150 dark:border-slate-600 dark:text-slate-400">
                    🐍 Ular
                </button>
                <button type="button" data-board-theme="perosotan" class="board-theme-btn rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors duration-150 dark:border-slate-600 dark:text-slate-400">
                    🛝 Perosotan
                </button>
            </div>
        </div>

        <x-game.board :papan="$papan" />
    </div>
</x-admin-layout>
