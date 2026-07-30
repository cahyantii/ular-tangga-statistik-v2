<x-auth-message-layout title="Konfirmasi Password">
    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
        <x-player.icon name="lock" class="h-7 w-7" />
    </span>

    <h1 class="mt-5 text-2xl font-bold text-slate-900 dark:text-slate-100">Konfirmasi Password</h1>
    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
        Ini adalah area aman. Mohon konfirmasi password kamu sebelum melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5 text-left" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <x-player.form-password-input
            size="lg"
            name="password"
            label="Password"
            placeholder="Masukkan password kamu"
            autocomplete="current-password"
            required
            autofocus
        />

        <button
            type="submit"
            :disabled="loading"
            class="flex h-[50px] w-full items-center justify-center gap-2 rounded-[14px] bg-blue-700 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
        >
            <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
            </svg>
            <span x-text="loading ? 'Memproses...' : 'Konfirmasi'"></span>
        </button>
    </form>
</x-auth-message-layout>
