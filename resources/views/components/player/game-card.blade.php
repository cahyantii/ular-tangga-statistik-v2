@props([
    'variant' => 'robot',
    'title',
    'description',
    'ctaLabel',
    'href',
    'method' => 'get',
])

@php
    $isRobot = $variant === 'robot';

    $wrapperClasses = $isRobot
        ? 'from-secondary-600 via-secondary-500 to-secondary-600'
        : 'from-primary-700 via-primary-600 to-primary-500';

    $buttonClasses = $isRobot
        ? 'bg-white text-secondary-600 hover:bg-secondary-50'
        : 'bg-white text-primary-600 hover:bg-primary-50';
@endphp

<div class="group relative h-full overflow-hidden rounded-3xl bg-gradient-to-br {{ $wrapperClasses }} p-6 text-white shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-soft-lg sm:p-7">
    @if (! $isRobot)
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl transition-opacity duration-300 group-hover:opacity-80"></div>
        <div class="pointer-events-none absolute bottom-0 right-6 h-24 w-24 rounded-full bg-white/10 blur-xl"></div>
    @else
        <div class="pointer-events-none absolute -right-8 -bottom-8 h-36 w-36 rounded-full bg-white/10 blur-2xl"></div>
    @endif

    <div class="relative flex h-full flex-col justify-between gap-6 sm:flex-row sm:items-center">
        <div class="max-w-sm">
            <p class="text-lg font-bold sm:text-xl">{{ $title }}</p>
            <p class="mt-2 text-sm text-white/85">{{ $description }}</p>

            <div class="mt-5">
                @if (strtolower($method) === 'post')
                    <form method="POST" action="{{ $href }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 {{ $buttonClasses }} hover:gap-3">
                            {{ $ctaLabel }}
                            <x-player.icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </form>
                @else
                    <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 {{ $buttonClasses }} hover:gap-3">
                        {{ $ctaLabel }}
                        <x-player.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </div>

        <div class="relative mx-auto shrink-0 sm:mx-0" aria-hidden="true">
            @if ($isRobot)
                <svg viewBox="0 0 120 120" class="h-24 w-24 drop-shadow-lg sm:h-28 sm:w-28">
                    <rect x="18" y="6" width="8" height="16" rx="4" fill="#ffffff" fill-opacity="0.85" />
                    <circle cx="22" cy="6" r="5" fill="#ffffff" />
                    <rect x="24" y="26" width="72" height="60" rx="20" fill="#ffffff" />
                    <circle cx="46" cy="54" r="8" fill="#0A317A" />
                    <circle cx="74" cy="54" r="8" fill="#0A317A" />
                    <circle cx="43" cy="51" r="2.4" fill="#ffffff" />
                    <circle cx="71" cy="51" r="2.4" fill="#ffffff" />
                    <rect x="46" y="70" width="28" height="6" rx="3" fill="#00A65A" />
                    <rect x="10" y="52" width="14" height="26" rx="7" fill="#ffffff" />
                    <rect x="96" y="52" width="14" height="26" rx="7" fill="#ffffff" />
                    <rect x="34" y="88" width="52" height="20" rx="10" fill="#ffffff" fill-opacity="0.9" />
                </svg>
            @else
                <svg viewBox="0 0 140 100" class="h-20 w-32 drop-shadow-lg sm:h-24 sm:w-36">
                    <circle cx="38" cy="34" r="18" fill="#ffffff" />
                    <circle cx="33" cy="30" r="2.4" fill="#0A317A" />
                    <circle cx="43" cy="30" r="2.4" fill="#0A317A" />
                    <path d="M32 40c2.5 2.5 8.5 2.5 11 0" stroke="#0A317A" stroke-width="2" stroke-linecap="round" fill="none" />
                    <path d="M14 88c0-18 10-30 24-30s24 12 24 30" fill="#ffffff" fill-opacity="0.9" />

                    <circle cx="102" cy="34" r="18" fill="#F68B1F" />
                    <circle cx="97" cy="30" r="2.4" fill="#ffffff" />
                    <circle cx="107" cy="30" r="2.4" fill="#ffffff" />
                    <path d="M96 40c2.5 2.5 8.5 2.5 11 0" stroke="#ffffff" stroke-width="2" stroke-linecap="round" fill="none" />
                    <path d="M78 88c0-18 10-30 24-30s24 12 24 30" fill="#F68B1F" fill-opacity="0.9" />

                    <circle cx="70" cy="46" r="14" fill="#0F4CBA" stroke="#ffffff" stroke-width="3" />
                    <text x="70" y="51" text-anchor="middle" font-size="12" font-weight="700" fill="#ffffff">VS</text>
                </svg>
            @endif
        </div>
    </div>
</div>
