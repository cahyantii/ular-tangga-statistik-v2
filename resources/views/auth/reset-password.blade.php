<x-auth-layout
    navPromptText="Sudah punya akun?"
    navLinkText="Masuk"
    :navLinkHref="route('login')"
>
    <x-slot:heroMobile>
        <h1 class="text-[32px] font-bold leading-tight text-slate-900">Buat Password Baru</h1>

        <p class="mx-auto mt-2 max-w-xs text-sm text-slate-500">
            Satu langkah lagi sebelum kamu bisa masuk kembali.
        </p>

        <img
            src="{{ asset('images/brand/logo.png') }}"
            alt="Ilustrasi Ular Tangga Statistik Indonesia"
            class="mx-auto mt-4 block h-auto max-h-[160px] w-auto object-contain"
            loading="lazy"
        >
    </x-slot:heroMobile>

    <x-slot:hero>
        <h1 class="text-[40px] font-bold leading-tight text-slate-900 lg:text-5xl">
            Buat Password Baru
        </h1>

        <p class="mt-5 max-w-md text-xl text-slate-500">
            Buat password baru yang kuat dan mudah kamu ingat untuk melanjutkan petualangan statistikmu.
        </p>

        <img
            src="{{ asset('images/brand/logo.png') }}"
            alt="Ilustrasi Ular Tangga Statistik Indonesia"
            class="mx-auto mt-8 block h-auto w-[80%] max-w-[320px] object-contain lg:mt-12 lg:max-w-[620px]"
            loading="lazy"
        >
    </x-slot:hero>

    <div class="mx-auto w-full max-w-[520px] rounded-[28px] bg-white p-5 shadow-[0_20px_60px_-15px_rgba(15,23,42,0.15)] md:p-8 lg:p-12">
        <div class="text-center">
            <img
                src="{{ asset('images/brand/logo-baruuuu.png') }}"
                alt="Logo Ular Tangga Statistik"
                class="mx-auto h-20 w-20 object-contain"
                loading="lazy"
            >
            <h1 class="mt-5 text-2xl font-bold text-slate-900">Reset Password</h1>
            <p class="mt-1.5 text-sm text-slate-500">Buat password baru untuk akunmu</p>
        </div>

        <form
            method="POST"
            action="{{ route('password.store') }}"
            class="mt-8 space-y-5"
            x-data="{ loading: false }"
            @submit="loading = true"
        >
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <x-player.form-input
                icon="mail"
                type="email"
                name="email"
                label="Email"
                placeholder="Masukkan email kamu"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
                size="lg"
            />

            <x-player.form-password-input
                size="lg"
                name="password"
                label="Password Baru"
                placeholder="Masukkan password baru"
                autocomplete="new-password"
                required
                aria-describedby="password-hint"
            />
            <p id="password-hint" class="-mt-3 text-xs text-slate-400">Minimal 8 karakter, kombinasi huruf besar/kecil &amp; angka.</p>

            <x-player.form-password-input
                size="lg"
                name="password_confirmation"
                label="Konfirmasi Password Baru"
                placeholder="Ulangi password baru"
                autocomplete="new-password"
                required
            />

            <button
                type="submit"
                :disabled="loading"
                class="flex h-[54px] w-full items-center justify-center gap-2 rounded-[14px] bg-blue-700 text-base font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-md active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0"
            >
                <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                </svg>
                <x-player.icon name="lock" class="h-4 w-4" x-show="!loading" />
                <span x-text="loading ? 'Memproses...' : 'Reset Password'"></span>
            </button>
        </form>
    </div>
</x-auth-layout>
