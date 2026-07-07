<x-player-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-8">
            <div class="max-w-xl">
                @include('player.profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-8">
            <div class="max-w-xl">
                @include('player.profile.partials.update-password-form')
            </div>
        </div>
    </div>
</x-player-layout>
