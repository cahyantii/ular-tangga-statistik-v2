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
    // Circle and bar — these simple Tailwind classes ARE scanned because
    // they appear directly as strings here, not in a PHP array lookup
    $circleClass = match ($tone) {
        'green'  => 'bg-secondary-500',
        'purple' => 'bg-violet-500',
        default  => 'bg-primary-500',
    };

    $barClass = match ($tone) {
        'green'  => 'bg-secondary-500',
        'purple' => 'bg-violet-500',
        default  => 'bg-primary-500',
    };

    $valueTextClass = match ($tone) {
        'green'  => 'text-secondary-600 dark:text-secondary-400',
        'purple' => 'text-violet-600 dark:text-violet-400',
        default  => 'text-primary-600 dark:text-primary-400',
    };

    // Action pill uses CSS class (defined in app.css) to guarantee dark mode works
    $actionCssClass = match ($tone) {
        'green'  => 'cert-action-green',
        'purple' => 'cert-action-purple',
        default  => 'cert-action-blue',
    };
@endphp

<div class="flex items-stretch gap-4">
    <div class="flex shrink-0 flex-col items-center">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $circleClass }} text-white shadow-md">
            @if ($done)
                <x-player.icon name="check" class="h-5 w-5" />
            @else
                <span class="text-base font-bold">{{ $index }}</span>
            @endif
        </span>

        @unless ($isLast)
            <span class="cert-timeline-line my-1 w-0.5 flex-1 border-l-2 border-dashed border-slate-300 dark:border-slate-700" aria-hidden="true"></span>
        @endunless
    </div>

    <div class="flex flex-1 flex-col gap-3 self-center pb-6 sm:flex-row sm:items-center sm:gap-5">
        <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-800 dark:text-slate-100">{!! $title !!}</p>
            <div class="mt-2 flex items-center gap-3">
                <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700/80">
                    <div
                        class="cert-progress-bar h-2.5 rounded-full {{ $barClass }} transition-all duration-700 ease-out"
                        style="width: {{ $percent }}%"
                    ></div>
                </div>
                <span class="shrink-0 text-sm font-bold {{ $valueTextClass }}">{{ $valueLabel }}</span>
            </div>
        </div>

        {{-- Action pill: uses CSS class for guaranteed dark mode --}}
        <div class="shrink-0 rounded-xl {{ $actionCssClass }} px-4 py-2.5 text-sm font-semibold">
            <span class="flex items-center gap-2">
                <x-player.icon :name="$actionIcon" class="h-4 w-4" />
                {{ $actionMessage }}
            </span>
        </div>
    </div>
</div>
