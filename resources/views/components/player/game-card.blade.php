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
@endphp

@if ($isRobot)
    <div class="group relative h-full overflow-hidden rounded-3xl bg-gradient-to-br from-secondary-600 via-secondary-500 to-secondary-600 p-6 text-white shadow-soft transition-all duration-300 hover:-translate-y-1 hover:shadow-soft-lg sm:p-7">
        <div class="pointer-events-none absolute -right-8 -bottom-8 h-36 w-36 rounded-full bg-white/10 blur-2xl"></div>

        <div class="relative flex h-full flex-col justify-between gap-6 sm:flex-row sm:items-center">
            <div class="max-w-sm">
                <p class="text-lg font-bold sm:text-xl">{{ $title }}</p>
                <p class="mt-2 text-sm text-white/85">{{ $description }}</p>

                <div class="mt-5">
                    @if (strtolower($method) === 'post')
                        <form method="POST" action="{{ $href }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-secondary-600 shadow-sm transition-all duration-200 hover:gap-3 hover:bg-secondary-50">
                                {{ $ctaLabel }}
                                <x-player.icon name="arrow-right" class="h-4 w-4" />
                            </button>
                        </form>
                    @else
                        <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-secondary-600 shadow-sm transition-all duration-200 hover:gap-3 hover:bg-secondary-50">
                            {{ $ctaLabel }}
                            <x-player.icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>

            <div class="relative mx-auto shrink-0 sm:mx-0" aria-hidden="true">
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
            </div>
        </div>
    </div>
@else
    {{--
        Kartu "Main Multiplayer": logo-multiplayer.png sebagai CSS background-image
        penuh (background-size: cover), bukan <img>/kolom terpisah. Gradient gelap
        di kiri menjaga kontras teks. Ukuran (min-height/padding/radius/shadow)
        identik dengan x-player.robot-hero agar keduanya sejajar rapi dalam grid
        2 kolom di Dashboard.
    --}}
    <style>
        .multiplayer-hero-bg {
            background-image:
                linear-gradient(90deg, rgba(8, 20, 60, .95) 0%, rgba(8, 20, 60, .85) 35%, rgba(8, 20, 60, .55) 60%, rgba(8, 20, 60, 0) 100%),
                url('{{ asset('images/brand/logo-multiplayer.png') }}');
            background-repeat: no-repeat, no-repeat;
            background-position: center, center right;
            background-size: cover, cover;
        }
    </style>

    <div class="multiplayer-hero-bg group relative flex min-h-[330px] flex-col justify-center overflow-hidden rounded-[32px] p-10 text-white shadow-[0_20px_50px_-15px_rgba(8,20,60,0.35)] transition-all duration-[350ms] ease-in-out hover:-translate-y-1 hover:shadow-[0_28px_60px_-15px_rgba(8,20,60,0.45)]">
        <div class="relative max-w-[320px]">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
                <x-player.icon name="users" class="h-3.5 w-3.5" />
                Multiplayer
            </span>

            <p class="mt-4 text-2xl font-extrabold text-white sm:text-3xl">{{ $title }}</p>
            <p class="mt-2 text-sm text-white/90 leading-[1.7]">
                {{ $description }}
            </p>

            <div class="mt-6">
                @if (strtolower($method) === 'post')
                    <form method="POST" action="{{ $href }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:gap-3 hover:bg-green-600">
                            {{ $ctaLabel }}
                            <x-player.icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </form>
                @else
                    <a href="{{ $href }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-green-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:gap-3 hover:bg-green-600">
                        {{ $ctaLabel }}
                        <x-player.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
