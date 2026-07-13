{{--
    Hero "Main vs Robot" pada Dashboard. Satu container tunggal: logo-robot.png
    dipasang sebagai CSS background-image penuh (background-size: cover), bukan
    <img> terpisah. Gradient gelap di kiri menjaga kontras teks. Ukuran
    (min-height/padding/radius/shadow) identik dengan kartu "Main Multiplayer"
    (x-player.game-card) agar keduanya sejajar rapi dalam grid 2 kolom.
--}}
@props(['href', 'method' => 'post'])

<style>
    .robot-hero-bg {
        background-image:
            linear-gradient(90deg, rgba(4, 40, 26, .95) 0%, rgba(4, 40, 26, .85) 35%, rgba(4, 40, 26, .55) 60%, rgba(4, 40, 26, 0) 100%),
            url('{{ asset('images/brand/logo-robot.png') }}');
        background-repeat: no-repeat, no-repeat;
        background-position: center, center right;
        background-size: cover, cover;
    }
</style>

<div class="robot-hero-bg group relative flex min-h-[330px] flex-col justify-center overflow-hidden rounded-[32px] p-10 text-white shadow-[0_20px_50px_-15px_rgba(4,40,26,0.35)] transition-all duration-[350ms] ease-in-out hover:-translate-y-1 hover:shadow-[0_28px_60px_-15px_rgba(4,40,26,0.45)]">
    <div class="relative max-w-[320px]">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-bold text-white backdrop-blur-sm">
            <x-player.icon name="cpu" class="h-3.5 w-3.5" />
            Vs Robot
        </span>

        <h2 class="mt-4 text-2xl font-extrabold text-white sm:text-3xl">Main vs Robot</h2>
        <p class="mt-2 text-sm text-white/90 leading-[1.7]">
            Bermain sendiri melawan robot. Papan dipilih acak, giliran robot dijalankan otomatis.
        </p>

        <div class="mt-6">
            @if (strtolower($method) === 'post')
                <form method="POST" action="{{ $href }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-secondary-600 hover:shadow-soft hover:gap-3">
                        Mulai Bermain
                        <x-player.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                </form>
            @else
                <a href="{{ $href }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-secondary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-secondary-600 hover:shadow-soft hover:gap-3">
                    Mulai Bermain
                    <x-player.icon name="arrow-right" class="h-4 w-4" />
                </a>
            @endif
        </div>
    </div>
</div>
