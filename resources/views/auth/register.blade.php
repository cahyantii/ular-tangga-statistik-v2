<x-auth-layout
    navPromptText="Sudah punya akun?"
    navLinkText="Masuk"
    :navLinkHref="route('login')"
>
    <x-slot:heroMobile>
        <h1 class="text-[32px] font-bold leading-tight text-slate-900 dark:text-slate-100">Ayo Bergabung!</h1>

        <p class="mx-auto mt-2 max-w-xs text-sm text-slate-500 dark:text-slate-400">
            Daftar gratis dan mulai belajar statistik dengan seru.
        </p>

        <img
            src="{{ asset('images/brand/logo.png') }}"
            alt="Ilustrasi Ular Tangga Statistik Indonesia"
            class="mx-auto mt-4 block h-auto max-h-[160px] w-auto object-contain"
            loading="lazy"
        >
    </x-slot:heroMobile>

    <x-slot:hero>
        <h1 class="text-[40px] font-bold leading-tight text-slate-900 lg:text-5xl dark:text-slate-100">
            Buat Akun Baru
        </h1>

        <p class="mt-5 max-w-md text-xl text-slate-500 dark:text-slate-400">
            Mulailah perjalananmu dan nikmati pengalaman belajar statistik yang interaktif, seru, dan penuh tantangan.
        </p>

        <img
            src="{{ asset('images/brand/logo.png') }}"
            alt="Ilustrasi Ular Tangga Statistik Indonesia"
            class="mx-auto mt-8 block h-auto w-[80%] max-w-[320px] object-contain lg:mt-12 lg:max-w-[620px]"
            loading="lazy"
        >
    </x-slot:hero>

    <div class="mx-auto w-full max-w-[520px] rounded-[28px] bg-white p-5 shadow-[0_20px_60px_-15px_rgba(15,23,42,0.15)] md:p-8 lg:p-12 dark:bg-slate-800 dark:shadow-none">
        <div class="text-center">
            <img
                src="{{ asset('images/brand/logo-baruuuu.png') }}"
                alt="Logo Ular Tangga Statistik"
                class="mx-auto h-20 w-20 object-contain"
                loading="lazy"
            >
            <h1 class="mt-5 text-2xl font-bold text-slate-900 dark:text-slate-100">Buat Akun Baru</h1>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Isi data di bawah untuk membuat akun baru</p>
        </div>

        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('register') }}"
            class="mt-8 space-y-5"
            x-data="{ loading: false, oauthLoading: null }"
            @submit="loading = true"
        >
            @csrf

            <x-player.form-input
                icon="user"
                type="text"
                name="name"
                label="Nama Lengkap"
                placeholder="Masukkan nama lengkap kamu"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                size="lg"
            />

            <x-player.form-input
                icon="mail"
                type="email"
                name="email"
                label="Email"
                placeholder="Masukkan email kamu"
                :value="old('email')"
                required
                autocomplete="username"
                size="lg"
            />

            <x-player.form-password-input
                size="lg"
                name="password"
                label="Password"
                placeholder="Masukkan password kamu"
                autocomplete="new-password"
                required
                aria-describedby="password-hint"
            />
            <p id="password-hint" class="-mt-3 text-xs text-slate-400 dark:text-slate-500">Minimal 8 karakter, kombinasi huruf besar/kecil &amp; angka.</p>

            <x-player.form-password-input
                size="lg"
                name="password_confirmation"
                label="Konfirmasi Password"
                placeholder="Ulangi password kamu"
                autocomplete="new-password"
                required
            />

            <div class="flex items-start gap-3 rounded-[14px] bg-blue-50 p-4 dark:bg-blue-500/10">
                <span class="mt-0.5 shrink-0 text-blue-700 dark:text-blue-400">
                    <x-player.icon name="shield" class="h-4 w-4" />
                </span>
                <label for="terms" class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-400">
                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        value="1"
                        class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-blue-700 focus:ring-blue-200 dark:border-slate-600 dark:bg-slate-800"
                        required
                        aria-required="true"
                    >
                    <span>
                        Dengan mendaftar, kamu setuju dengan
                        <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Syarat &amp; Ketentuan</a>
                        dan
                        <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="font-semibold text-blue-700 underline hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Kebijakan Privasi</a>
                    </span>
                </label>
            </div>
            @error('terms')
                <p class="-mt-3 text-xs font-medium text-rose-500">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                :disabled="loading"
                class="flex h-[54px] w-full items-center justify-center gap-2 rounded-[14px] bg-blue-700 text-base font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
            >
                <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                </svg>
                <x-player.icon name="user-plus" class="h-4 w-4" x-show="!loading" />
                <span x-text="loading ? 'Memproses...' : 'Daftar Sekarang'"></span>
            </button>

            <div class="relative py-1 text-center">
                <span class="relative z-10 bg-white px-3 text-xs text-slate-400 dark:bg-slate-800 dark:text-slate-500">atau daftar dengan</span>
                <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a
                    href="{{ route('social.redirect', 'google') }}"
                    @click="oauthLoading = 'google'"
                    :class="{ 'pointer-events-none opacity-60': oauthLoading }"
                    aria-label="Daftar dengan Google"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-[14px] border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    <svg x-show="oauthLoading !== 'google'" class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.52 12.27c0-.85-.08-1.67-.22-2.45H12v4.64h6.47c-.28 1.5-1.13 2.77-2.4 3.62v3h3.88c2.27-2.09 3.57-5.17 3.57-8.81Z" />
                        <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.92l-3.88-3c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.26v3.11C3.24 21.3 7.29 24 12 24Z" />
                        <path fill="#FBBC05" d="M5.27 14.27a7.2 7.2 0 0 1 0-4.54V6.62H1.26a12 12 0 0 0 0 10.76l4.01-3.11Z" />
                        <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.29 0 3.24 2.7 1.26 6.62l4.01 3.11C6.22 6.88 8.87 4.77 12 4.77Z" />
                    </svg>
                    <svg x-show="oauthLoading === 'google'" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                    </svg>
                    <span x-text="oauthLoading === 'google' ? 'Mengalihkan...' : 'Google'"></span>
                </a>

                <a
                    href="{{ route('social.redirect', 'github') }}"
                    @click="oauthLoading = 'github'"
                    :class="{ 'pointer-events-none opacity-60': oauthLoading }"
                    aria-label="Daftar dengan GitHub"
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-[14px] border border-slate-200 bg-white text-sm font-semibold text-slate-700 transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    <svg x-show="oauthLoading !== 'github'" class="h-4 w-4 dark:fill-slate-200" viewBox="0 0 24 24" fill="#1E293B" aria-hidden="true">
                        <path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.79-.25.79-.55v-1.94c-3.2.7-3.87-1.54-3.87-1.54-.53-1.33-1.28-1.68-1.28-1.68-1.05-.72.08-.7.08-.7 1.16.08 1.77 1.19 1.77 1.19 1.03 1.76 2.7 1.25 3.36.96.1-.75.4-1.25.73-1.54-2.55-.29-5.24-1.28-5.24-5.69 0-1.26.45-2.29 1.19-3.1-.12-.29-.51-1.46.11-3.05 0 0 .97-.31 3.18 1.18a11.1 11.1 0 0 1 5.79 0c2.21-1.49 3.18-1.18 3.18-1.18.63 1.59.23 2.76.11 3.05.74.81 1.18 1.84 1.18 3.1 0 4.42-2.69 5.4-5.25 5.68.41.36.78 1.06.78 2.14v3.17c0 .3.21.66.8.55A11.51 11.51 0 0 0 23.5 12C23.5 5.65 18.35.5 12 .5Z" />
                    </svg>
                    <svg x-show="oauthLoading === 'github'" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                    </svg>
                    <span x-text="oauthLoading === 'github' ? 'Mengalihkan...' : 'GitHub'"></span>
                </a>
            </div>
        </form>
    </div>
</x-auth-layout>
