@props([
    'index',
    'title',
    'valueLabel',
    'percent',
    'done',
    'tone',
    'actionIcon',
    'actionMessage',
    'isLast' => false,
])

@php
    $palette = [
        'green' => ['circle' => 'bg-secondary-500', 'bar' => 'bg-secondary-500', 'text' => 'text-secondary-600 dark:text-secondary-400', 'action' => 'bg-secondary-50 text-secondary-700 dark:bg-secondary-500/10 dark:text-secondary-400'],
        'purple' => ['circle' => 'bg-violet-500', 'bar' => 'bg-violet-500', 'text' => 'text-violet-600 dark:text-violet-400', 'action' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400'],
        'blue' => ['circle' => 'bg-primary-500', 'bar' => 'bg-primary-500', 'text' => 'text-primary-600 dark:text-primary-400', 'action' => 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400'],
    ];

    $tonePalette = $palette[$tone] ?? $palette['blue'];
@endphp

<div class="flex items-stretch gap-4">
    <div class="flex shrink-0 flex-col items-center">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $tonePalette['circle'] }} text-white shadow-md">
            @if ($done)
                <x-player.icon name="check" class="h-5 w-5" />
            @else
                <span class="text-base font-bold">{{ $index }}</span>
            @endif
        </span>

        @unless ($isLast)
            <span class="cert-timeline-line my-1 w-0.5 flex-1 border-l-2 border-dashed" aria-hidden="true"></span>
        @endunless
    </div>

    <div class="flex flex-1 flex-col gap-3 self-center pb-6 sm:flex-row sm:items-center sm:gap-5">
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800 dark:text-slate-100">{!! $title !!}</p>
            <div class="mt-2 flex items-center gap-3">
                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                    <div
                        class="cert-progress-bar h-2.5 rounded-full {{ $tonePalette['bar'] }} transition-all duration-700 ease-out"
                        style="width: {{ $percent }}%"
                    ></div>
                </div>
                <span class="shrink-0 text-sm font-bold {{ $tonePalette['text'] }}">{{ $valueLabel }}</span>
            </div>
        </div>

        <div class="shrink-0 rounded-xl {{ $tonePalette['action'] }} px-4 py-2.5 text-sm font-semibold">
            <span class="flex items-center gap-2">
                <x-player.icon :name="$actionIcon" class="h-4 w-4" />
                {{ $actionMessage }}
            </span>
        </div>
    </div>
</div>
