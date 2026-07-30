<section id="avatar-picker">
    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Avatar</h3>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih avatar yang kamu suka atau unggah foto sendiri.</p>

    <div class="mt-5 grid grid-cols-[repeat(auto-fill,minmax(90px,1fr))] justify-items-center gap-4">
        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
            @csrf
            @method('patch')
            <label class="group flex h-[90px] w-[90px] cursor-pointer flex-col items-center justify-center gap-1.5 rounded-[20px] border-2 border-dashed border-primary-200 bg-primary-50/50 text-primary-500 transition duration-200 hover:border-primary-400 hover:bg-primary-50 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-400 dark:hover:border-primary-400/60 dark:hover:bg-primary-500/15">
                <input
                    type="file"
                    name="avatar"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="sr-only"
                    aria-label="Unggah foto avatar"
                    @change="$el.closest('form').submit()"
                >
                <x-player.icon name="upload-cloud" class="h-6 w-6" />
                <span class="text-center text-xs font-semibold leading-tight">Unggah Foto</span>
            </label>
        </form>

        @foreach ($presetAvatars as $index => $preset)
            @php $active = $user->avatar === $preset; @endphp
            <form method="POST" action="{{ route('profile.avatar.update') }}">
                @csrf
                @method('patch')
                <input type="hidden" name="preset" value="{{ $preset }}">
                <button
                    type="submit"
                    aria-label="Pilih Avatar {{ $index + 1 }}"
                    aria-pressed="{{ $active ? 'true' : 'false' }}"
                    class="avatar-option relative flex h-[90px] w-[90px] items-center justify-center rounded-[20px] border-2 transition duration-200 {{ $active ? 'avatar-option--active border-[#2563EB]' : 'border-transparent' }}"
                >
                    <img
                        src="{{ asset('images/avatars/'.$preset.'.png') }}"
                        alt="Avatar {{ $index + 1 }}"
                        loading="lazy"
                        class="h-[70px] w-[70px] object-contain"
                    >

                    @if ($active)
                        <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-[#2563EB] text-white ring-2 ring-white dark:ring-slate-800">
                            <x-player.icon name="check" class="h-3 w-3" />
                        </span>
                    @endif
                </button>
            </form>
        @endforeach
    </div>

    @error('avatar', 'avatar')
        <p class="mt-3 text-xs font-medium text-rose-500 dark:text-rose-400">{{ $message }}</p>
    @enderror

    @if (session('status') === 'avatar-updated')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2500)"
            class="mt-3 text-xs font-medium text-secondary-600 dark:text-secondary-400"
        >Avatar berhasil diperbarui.</p>
    @endif
</section>
