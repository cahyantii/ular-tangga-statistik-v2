<x-auth-message-layout title="Verifikasi Email">
    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
        <x-player.icon name="mail" class="h-7 w-7" />
    </span>

    <h1 class="mt-5 text-2xl font-bold text-slate-900">Verifikasi Email Kamu</h1>
    <p class="mt-2 text-sm leading-relaxed text-slate-500">
        Silakan verifikasi email Anda. Kami sudah mengirim link verifikasi ke
        <span class="font-semibold text-slate-700">{{ auth()->user()->email }}</span>.
        Buka email tersebut dan klik tombol verifikasi untuk mulai bermain.
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="mt-4 rounded-xl bg-secondary-50 px-4 py-3 text-sm font-medium text-secondary-700">
            Link verifikasi baru sudah dikirim ke email kamu.
        </p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <button
            type="submit"
            :disabled="loading"
            class="flex h-[50px] w-full items-center justify-center gap-2 rounded-[14px] bg-blue-700 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
        >
            <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
            </svg>
            <span x-text="loading ? 'Mengirim...' : 'Kirim Ulang Email Verifikasi'"></span>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf

        <button type="submit" class="text-sm font-semibold text-slate-500 underline hover:text-slate-700">
            Keluar
        </button>
    </form>
</x-auth-message-layout>
