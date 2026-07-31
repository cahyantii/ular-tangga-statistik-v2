@props(['level'])

<div class="animate-fade-in-up rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft dark:bg-slate-800 sm:p-6">
    <h3 class="flex items-center gap-2 text-base font-bold text-slate-700 dark:text-slate-200">
        <x-player.icon name="medal" class="h-5 w-5 text-accent-500" />
        Level Kamu
    </h3>

    <div class="mt-4 flex items-center gap-4">
        <div class="relative flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-400 to-accent-600 text-white shadow-sm">
            <x-player.icon name="medal" class="h-8 w-8" />
            <span class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-primary-500 text-xs font-bold text-white dark:border-slate-800">
                {{ $level['level'] }}
            </span>
        </div>

        <div class="min-w-0">
            <p class="font-bold text-slate-800 dark:text-slate-100">{{ $level['level_name'] }}</p>
            <p class="text-xs text-slate-400 dark:text-slate-400">{{ $level['xp_into_level'] }} / {{ $level['xp_for_next_level'] }} XP</p>
        </div>
    </div>

    <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
        <div
            class="h-2.5 rounded-full bg-gradient-to-r from-accent-400 to-accent-600 transition-all duration-700 ease-out"
            style="width: {{ $level['percent'] }}%"
        ></div>
    </div>
</div>
