@props([
    'tip',
    'title' => 'Tips Hari Ini',
])

<div class="animate-fade-in-up rounded-3xl bg-accent-50 p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-accent-500 text-white">
            <x-player.icon name="lightbulb" class="h-4 w-4" />
        </span>
        {{ $title }}
    </h3>

    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $tip }}</p>
</div>
