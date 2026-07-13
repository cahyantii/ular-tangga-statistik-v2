<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Sertifikat Digital</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            Lengkapi syarat berikut dan raih <span class="font-semibold text-primary-600">Sertifikat Penguasaan Statistik Dasar!</span>
        </p>
    </x-slot>

    @if ($certificate)
        <div class="mx-auto max-w-lg rounded-3xl border-2 border-secondary-400 bg-secondary-50 p-6 text-center shadow-sm sm:p-8">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-secondary-500 text-white">
                <x-player.icon name="certificate" class="h-7 w-7" />
            </span>
            <p class="mt-3 text-sm font-semibold uppercase tracking-wide text-secondary-600">Selamat!</p>
            <p class="mt-2 text-xl font-bold text-secondary-700">{{ $certificate->judul }}</p>
            <p class="mt-1 text-sm text-slate-600">Nomor: {{ $certificate->nomor_sertifikat }}</p>
            <p class="text-sm text-slate-600">Terbit: {{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
            <p class="mt-1 text-xs text-slate-500">Kode verifikasi: {{ $certificate->verification_code }}</p>

            <x-player.button variant="secondary" :href="route('player.certificate.download', $certificate)" class="mt-5">
                <x-player.icon name="download" class="h-4 w-4" />
                Unduh PDF
            </x-player.button>
        </div>
    @else
        <div class="space-y-6">
            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col items-center gap-6 text-center lg:flex-row lg:items-center lg:justify-between lg:text-left">
                    <svg viewBox="0 0 140 140" class="h-28 w-28 shrink-0 select-none sm:h-32 sm:w-32" aria-hidden="true">
                        <rect x="14" y="14" width="112" height="112" rx="28" fill="#0F4CBA" transform="rotate(-8 70 70)" />
                        <rect x="30" y="26" width="80" height="60" rx="8" fill="#FFFFFF" />
                        <rect x="40" y="38" width="60" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.3" />
                        <rect x="40" y="48" width="42" height="4" rx="2" fill="#0F4CBA" fill-opacity="0.2" />
                        <circle cx="70" cy="66" r="14" fill="#F68B1F" />
                        <path d="m64 66 4 4 8-8" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        <path d="m62 78-4 14 12-6 12 6-4-14" fill="#F68B1F" />
                        <circle cx="112" cy="20" r="4" fill="#FCD34D" />
                        <circle cx="120" cy="34" r="2.5" fill="#FCD34D" />
                        <path d="M18 100 22 108 30 110 22 112 18 120 14 112 6 110 14 108Z" fill="#FCD34D" />
                    </svg>

                    <div class="min-w-0 flex-1">
                        <p class="text-slate-700">Selesaikan tiga syarat berikut untuk mendapatkan</p>
                        <p class="text-lg font-bold text-primary-600">Sertifikat Penguasaan Statistik Dasar.</p>
                        <p class="mt-2 text-sm text-slate-500">Tingkatkan kemampuanmu dan buktikan pencapaianmu!</p>
                    </div>

                    <svg viewBox="0 0 120 130" class="h-28 w-28 shrink-0 select-none sm:h-32 sm:w-32" aria-hidden="true">
                        <line x1="60" y1="6" x2="60" y2="16" stroke="#0F4CBA" stroke-width="3" stroke-linecap="round" />
                        <circle cx="60" cy="5" r="4" fill="#F68B1F" />
                        <rect x="34" y="16" width="52" height="40" rx="16" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                        <circle cx="50" cy="36" r="4.5" fill="#0F4CBA" />
                        <circle cx="70" cy="36" r="4.5" fill="#0F4CBA" />
                        <path d="M50 45c3 3 9 3 12 0" stroke="#0F4CBA" stroke-width="2.5" stroke-linecap="round" fill="none" />
                        <rect x="30" y="58" width="60" height="46" rx="18" fill="#EAF1FC" stroke="#0F4CBA" stroke-width="3" />
                        <rect x="16" y="66" width="14" height="10" rx="5" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                        <rect x="24" y="70" width="20" height="26" rx="3" fill="#FFFFFF" stroke="#00A65A" stroke-width="2.5" transform="rotate(-8 34 83)" />
                        <path d="M90 70c8-2 14-10 14-18" stroke="#0F4CBA" stroke-width="6" stroke-linecap="round" fill="none" />
                        <circle cx="104" cy="50" r="6" fill="#FFFFFF" stroke="#0F4CBA" stroke-width="3" />
                    </svg>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Timeline syarat --}}
                <div class="rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6 lg:col-span-2">
                    <div>
                        @foreach ($checklist['steps'] as $step)
                            <x-player.certificate-step
                                :index="$loop->iteration"
                                :title="$step['title']"
                                :valueLabel="$step['value_label']"
                                :percent="$step['percent']"
                                :done="$step['done']"
                                :tone="$step['tone']"
                                :actionIcon="$step['action_icon']"
                                :actionMessage="$step['action_message']"
                                :isLast="$loop->last"
                            />
                        @endforeach
                    </div>

                    <div class="flex flex-col items-center gap-4 overflow-hidden rounded-2xl bg-accent-50 p-5 text-center sm:flex-row sm:justify-between sm:p-6 sm:text-left">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-500 text-white">
                                <x-player.icon name="trophy" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">Setiap langkah kecil adalah bagian dari pencapaian besar!</p>
                                <p class="mt-0.5 text-sm text-slate-500">Terus belajar, terus bermain, dan raih sertifikatmu sekarang!</p>
                            </div>
                        </div>

                        <svg viewBox="0 0 100 90" class="h-20 w-24 shrink-0 select-none sm:h-24 sm:w-28" aria-hidden="true">
                            <path d="M10 75c30-15 10-35 30-45s20 10 45-5" stroke="#00A65A" stroke-width="9" stroke-linecap="round" fill="none" />
                            <circle cx="80" cy="20" r="9" fill="#00A65A" />
                            <circle cx="83" cy="17" r="1.6" fill="#0A317A" />
                            <path d="M76 24c2 2 6 2 8 0" stroke="#0A317A" stroke-width="1.6" stroke-linecap="round" fill="none" />
                            <path d="M64 8 82 14 64 20 46 14Z" fill="#1E293B" />
                            <rect x="62" y="18" width="4" height="8" fill="#1E293B" />
                            <circle cx="64" cy="26" r="1.6" fill="#F68B1F" />
                        </svg>
                    </div>
                </div>

                {{-- Hadiah & tips --}}
                <div class="space-y-6">
                    <x-player.certificate-reward-card :rewardPoin="$checklist['reward_poin']" />
                    <x-player.tips-card :tip="$tip" />
                </div>
            </div>
        </div>
    @endif
</x-player-layout>
