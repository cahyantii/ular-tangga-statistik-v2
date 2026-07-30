<section>
    <header>
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Informasi Profil</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Perbarui informasi akun dan email kamu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <x-player.form-input
            icon="user"
            name="name"
            label="Nama Lengkap"
            :value="$user->name"
            required
            autofocus
            autocomplete="name"
        />

        <div>
            <x-player.form-input
                icon="mail"
                type="email"
                name="email"
                label="Email"
                :value="$user->email"
                required
                autocomplete="username"
            >
                <x-slot:suffix>
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail())
                        <span class="inline-flex items-center gap-1 rounded-full bg-secondary-50 px-2.5 py-1 text-xs font-semibold text-secondary-600 dark:bg-secondary-500/10 dark:text-secondary-400">
                            <x-player.icon name="check" class="h-3 w-3" />
                            Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-accent-50 px-2.5 py-1 text-xs font-semibold text-accent-600 dark:bg-accent-500/10 dark:text-accent-400">
                            Belum diverifikasi
                        </span>
                    @endif
                </x-slot:suffix>
            </x-player.form-input>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Email kamu belum terverifikasi.
                    <button form="send-verification" class="font-semibold text-primary-600 underline hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        Klik di sini
                    </button>
                    untuk mengubah email.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-secondary-600 dark:text-secondary-400">
                        Tautan verifikasi baru sudah dikirim ke email kamu.
                    </p>
                @endif
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:from-primary-600 hover:to-primary-700 hover:shadow-soft active:translate-y-0"
            >
                <x-player.icon name="save" class="h-4 w-4" />
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-secondary-600 dark:text-secondary-400"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
