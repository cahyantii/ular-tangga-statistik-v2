<x-player.modal name="feedback-modal">
    <div x-data="{ type: 'feedback' }">
        <h3 class="flex items-center gap-2 text-lg font-bold text-slate-800">
            <x-player.icon name="mail" class="h-5 w-5 text-primary-500" />
            Kirim Masukan
        </h3>
        <p class="mt-1 text-sm text-slate-500">Laporkan bug atau sampaikan masukan Anda untuk pengembangan aplikasi.</p>

        <form method="POST" action="{{ route('feedback.store') }}" class="mt-4 space-y-4">
            @csrf

            <div class="flex gap-2">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="feedback" x-model="type" class="peer sr-only">
                    <span class="flex items-center justify-center gap-2 rounded-xl border py-2.5 text-sm font-semibold transition peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-600 border-slate-200 text-slate-500">
                        <x-player.icon name="mail" class="h-4 w-4" /> Masukan
                    </span>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="bug" x-model="type" class="peer sr-only">
                    <span class="flex items-center justify-center gap-2 rounded-xl border py-2.5 text-sm font-semibold transition peer-checked:border-red-400 peer-checked:bg-red-50 peer-checked:text-red-600 border-slate-200 text-slate-500">
                        <x-player.icon name="alert" class="h-4 w-4" /> Laporkan Bug
                    </span>
                </label>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Subjek</label>
                <input type="text" name="subject" required maxlength="150"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Ringkasan singkat...">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-600">Detail</label>
                <textarea name="message" required maxlength="2000" rows="4"
                          class="w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                          placeholder="Jelaskan lebih lengkap..."></textarea>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-primary-600">
                Kirim
            </button>
        </form>
    </div>
</x-player.modal>
