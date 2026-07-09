@props([
    'nama',
    'deskripsi',
    'earned' => false,
    'earnedAt' => null,
])

<div
    class="group relative overflow-hidden rounded-2xl border p-4 transition-all duration-300 hover:-translate-y-1 {{ $earned ? 'border-secondary-200 bg-secondary-50 hover:shadow-soft' : 'border-slate-200 bg-white grayscale hover:grayscale-0 hover:shadow-sm' }}"
>
    <div class="flex items-start justify-between gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $earned ? 'bg-accent-500 text-white' : 'bg-slate-100 text-slate-400' }}">
            <x-player.icon name="trophy" class="h-6 w-6" />
        </span>

        @if ($earned)
            <span class="rounded-full bg-secondary-500 px-2.5 py-0.5 text-xs font-semibold text-white">Diraih</span>
        @else
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">Belum</span>
        @endif
    </div>

    <p class="mt-3 text-base font-bold text-slate-800">{{ $nama }}</p>
    <p class="mt-1 text-sm text-slate-500">{{ $deskripsi }}</p>

    @if ($earned && $earnedAt)
        <p class="mt-2 text-xs font-medium text-secondary-600">Diraih pada {{ $earnedAt->translatedFormat('d F Y') }}</p>
    @endif
</div>
