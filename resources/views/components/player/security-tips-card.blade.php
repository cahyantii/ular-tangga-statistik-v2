@props(['tip'])

<div class="flex items-center gap-4 overflow-hidden rounded-3xl border border-primary-100 bg-primary-50 p-5 dark:border-primary-500/30 dark:bg-primary-500/10 sm:p-6">
    <div class="min-w-0 flex-1">
        <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100">
            <x-player.icon name="lightbulb" class="h-4 w-4 text-accent-500" />
            Tips Keamanan
        </h3>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $tip }}</p>
    </div>

    <svg viewBox="0 0 80 80" class="h-16 w-16 shrink-0 select-none" aria-hidden="true">
        <rect x="10" y="12" width="56" height="56" rx="8" fill="#334155" />
        <circle cx="38" cy="40" r="16" fill="#475569" stroke="#94A3B8" stroke-width="2" />
        <circle cx="38" cy="40" r="4" fill="#CBD5E1" />
        <rect x="36" y="24" width="4" height="10" fill="#CBD5E1" />
        <circle cx="60" cy="20" r="10" fill="#00A65A" />
        <path d="m56 20 3 3 5-5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
    </svg>
</div>
