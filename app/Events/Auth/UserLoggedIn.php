<?php

namespace App\Events\Auth;

use App\Enums\AuthProvider;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Dipicu setiap kali user yang SUDAH punya akun berhasil login - Email,
 * Google, atau GitHub. Sengaja TIDAK dipicu untuk sesi login otomatis yang
 * menyertai registrasi baru (lihat UserRegistered) supaya user baru tidak
 * menerima dua email sekaligus ("selamat datang" + "login berhasil") untuk
 * satu tindakan yang sama.
 */
class UserLoggedIn
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly AuthProvider $provider,
        public readonly string $ipAddress,
        public readonly ?string $userAgent,
        public readonly Carbon $occurredAt,
    ) {
    }
}
