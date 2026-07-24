<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar saat tombol Google/GitHub diklik tapi client_id/client_secret
 * provider tsb masih kosong di .env - tanpa pengecekan ini, Socialite tetap
 * redirect ke Google/GitHub TANPA client_id, dan providernya sendiri yang
 * menampilkan "Error 400: Missing required parameter: client_id" (membingungkan,
 * seolah bug di sisi Google). Exception ini ditangkap di bootstrap/app.php dan
 * diubah jadi redirect + toast yang jelas untuk developer (lihat pesan di
 * bawah), bukan error mentah dari provider.
 */
class OAuthProviderNotConfiguredException extends Exception
{
    public static function forProvider(string $provider): self
    {
        $envPrefix = strtoupper($provider);

        return new self(
            "Konfigurasi OAuth \"{$provider}\" belum lengkap. Isi {$envPrefix}_CLIENT_ID dan {$envPrefix}_CLIENT_SECRET di file .env (lihat .env.example), lalu jalankan `php artisan config:clear`."
        );
    }
}
