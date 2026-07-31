<section>
    <header class="flex items-start gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
            <x-player.icon name="lock" class="h-5 w-5" />
        </span>
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Ubah Password</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pastikan akun kamu menggunakan password yang kuat dan tidak mudah ditebak.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <x-player.form-password-input
            name="current_password"
            label="Password Saat Ini"
            placeholder="Masukkan password saat ini"
            autocomplete="current-password"
            bag="updatePassword"
        />

        <x-player.form-password-input
            name="password"
            label="Password Baru"
            placeholder="Masukkan password baru"
            autocomplete="new-password"
            bag="updatePassword"
        />

        <x-player.form-password-input
            name="password_confirmation"
            label="Konfirmasi Password Baru"
            placeholder="Konfirmasi password baru"
            autocomplete="new-password"
            bag="updatePassword"
        />

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-violet-600 hover:to-violet-700 hover:shadow-soft active:translate-y-0"
            >
                <x-player.icon name="lock" class="h-4 w-4" />
                Perbarui Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-secondary-600"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
