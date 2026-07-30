<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100 sm:text-2xl">Sertifikat Digital</h1>
        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
            Lengkapi syarat berikut dan raih <span class="font-semibold text-primary-600 dark:text-primary-400">Sertifikat Penguasaan Statistik Dasar!</span>
        </p>
    </x-slot>

    @if ($certificate)
        <div class="mx-auto max-w-lg rounded-3xl border-2 border-secondary-400 bg-secondary-50 p-6 text-center shadow-sm dark:border-secondary-500/40 dark:bg-secondary-500/10 sm:p-8">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-secondary-500 text-white">
                <x-player.icon name="certificate" class="h-7 w-7" />
            </span>
            <p class="mt-3 text-sm font-semibold uppercase tracking-wide text-secondary-600 dark:text-secondary-400">Selamat!</p>
            <p class="mt-2 text-xl font-bold text-secondary-700 dark:text-secondary-400">{{ $certificate->judul }}</p>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Nomor: {{ $certificate->nomor_sertifikat }}</p>
            <p class="text-sm text-slate-600 dark:text-slate-400">Terbit: {{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-500">Kode verifikasi: {{ $certificate->verification_code }}</p>

            <x-player.button variant="secondary" :href="route('player.certificate.download', $certificate)" class="mt-5">
                <x-player.icon name="download" class="h-4 w-4" />
                Unduh PDF
            </x-player.button>
        </div>
    @else
        <div class="space-y-6">
            {{-- Hero --}}
            <div class="cert-card cert-card--white flex flex-col items-center gap-6 pl-4 pr-6 py-6 text-center sm:pl-6 sm:pr-10 sm:py-8 lg:flex-row lg:justify-between lg:text-left">
                <div class="flex w-full items-center gap-4 lg:w-[72%]">
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
                        <p class="text-slate-700 dark:text-slate-300">Selesaikan tiga syarat berikut untuk mendapatkan</p>
                        <p class="text-lg font-bold text-primary-600 dark:text-primary-400">Sertifikat Penguasaan Statistik Dasar.</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tingkatkan kemampuanmu dan buktikan pencapaianmu!</p>
                    </div>
                </div>

                <img
                    src="{{ asset('images/brand/logo-robo.png') }}"
                    alt=""
                    aria-hidden="true"
                    class="h-32 w-auto max-w-[200px] shrink-0 select-none object-contain sm:h-40 lg:h-48 lg:mr-[35px]"
                >
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Timeline syarat --}}
                <div class="cert-card cert-card--progress p-5 sm:p-6 lg:col-span-2 lg:p-7">
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

                    <div class="cert-banner--motivation flex flex-row flex-nowrap items-center justify-between gap-5 overflow-hidden rounded-2xl p-5 sm:p-6">
                        <img
                            src="{{ asset('images/brand/logo-piala.png') }}"
                            alt=""
                            aria-hidden="true"
                            class="mr-5 h-auto w-[70px] shrink-0 select-none object-contain transition duration-300 hover:scale-105 sm:w-[85px] lg:w-[100px]"
                        >

                        <div class="min-w-0 flex-1 text-center">
                            <p class="font-bold text-slate-800 dark:text-slate-100">Setiap langkah kecil adalah bagian dari pencapaian besar!</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Terus belajar, terus bermain, dan raih sertifikatmu sekarang!</p>
                        </div>

                        <img
                            src="{{ asset('images/brand/logo-ular.png') }}"
                            alt=""
                            aria-hidden="true"
                            class="mr-3 h-auto w-[90px] shrink-0 select-none object-contain transition duration-300 hover:scale-105 sm:w-[120px] lg:w-[150px]"
                        >
                    </div>
                </div>

                {{-- Hadiah & tips --}}
                <div class="space-y-6">
                    <x-player.certificate-reward-card :rewardPoin="$checklist['reward_poin']" />
                    <x-player.tips-card :tip="$tip" :illustration="asset('images/brand/logo-buku.png')" :premium="true" />
                </div>
            </div>
        </div>
    @endif
</x-player-layout>
