@props(['gameSession', 'papan'])

<div class="flex items-start gap-3">
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-lg font-bold text-slate-800 sm:text-xl">Permainan #{{ $gameSession->id }}</h1>

            {{-- Bendera Indonesia --}}
            <svg viewBox="0 0 30 20" class="h-3.5 w-5 overflow-hidden rounded-sm shadow-sm" aria-hidden="true">
                <rect width="30" height="10" fill="#DC2626" />
                <rect y="10" width="30" height="10" fill="#FFFFFF" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-primary-600">{{ $papan->nama }}</p>
        <p class="mt-0.5 text-xs text-slate-400 sm:text-sm">Jawab soal, kumpulkan poin, dan jadilah yang terbaik!</p>
    </div>
</div>
