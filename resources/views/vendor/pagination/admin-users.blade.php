@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-end gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                <x-player.icon name="arrow-right" class="h-4 w-4 rotate-180" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 hover:border-emerald-300 hover:text-emerald-600">
                <x-player.icon name="arrow-right" class="h-4 w-4 rotate-180" />
            </a>
        @endif

        @foreach ($paginator->linkCollection()->slice(1, -1) as $link)
            @if ($link['url'] === null)
                <span class="flex h-9 w-9 items-center justify-center text-sm text-slate-400">&hellip;</span>
            @elseif ($link['active'])
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 text-sm font-semibold text-white shadow-sm">{{ $link['label'] }}</span>
            @else
                <a href="{{ $link['url'] }}"
                   class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-sm font-medium text-slate-600 transition duration-200 hover:border-emerald-300 hover:text-emerald-600">{{ $link['label'] }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition duration-200 hover:border-emerald-300 hover:text-emerald-600">
                <x-player.icon name="arrow-right" class="h-4 w-4" />
            </a>
        @else
            <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-300">
                <x-player.icon name="arrow-right" class="h-4 w-4" />
            </span>
        @endif
    </nav>
@endif
