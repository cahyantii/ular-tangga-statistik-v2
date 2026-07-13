<x-player-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Profil</h1>
        <p class="mt-0.5 text-sm text-slate-500">Kelola informasi akun dan keamananmu &#10024;</p>
    </x-slot>

    <div class="space-y-6">
        <x-player.profile-header-card :user="$user" :summary="$summary" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                <div class="animate-fade-in-up rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
                    @include('player.profile.partials.update-profile-information-form')
                </div>

                <div class="animate-fade-in-up rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
                    @include('player.profile.partials.update-password-form')
                </div>
            </div>

            <div class="space-y-6">
                <div class="animate-fade-in-up rounded-3xl bg-white p-5 shadow-sm transition-shadow duration-300 hover:shadow-soft sm:p-6">
                    @include('player.profile.partials.avatar-picker')
                </div>

                <x-player.additional-info-card :user="$user" :summary="$summary" />

                <x-player.security-tips-card :tip="$securityTip" />
            </div>
        </div>
    </div>
</x-player-layout>
