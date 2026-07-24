<?php

namespace App\Events\Auth;

use App\Enums\AuthProvider;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Dipicu tepat sekali per akun baru berhasil dibuat - lewat form registrasi
 * email (RegisteredUserController) maupun OAuth Google/GitHub
 * (SocialiteController, hanya saat SocialAuthService BENAR-BENAR membuat
 * baris user baru, ditandai $user->wasRecentlyCreated - lihat
 * SocialAuthService::findOrCreateUser()). Listener yang bereaksi (lihat
 * app/Listeners/Auth) mengirim email selamat datang ke user + notifikasi
 * "pengguna baru" ke semua admin lewat AuthMailService.
 */
class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly AuthProvider $provider,
        public readonly Carbon $occurredAt,
    ) {
    }
}
