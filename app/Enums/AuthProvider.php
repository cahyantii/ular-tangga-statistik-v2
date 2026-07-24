<?php

namespace App\Enums;

/**
 * Metode autentikasi yang dipakai user saat registrasi/login - dipakai
 * bersama oleh event UserRegistered/UserLoggedIn (app/Events/Auth) dan
 * seluruh email auth (app/Mail/Auth) supaya satu set label konsisten
 * dipakai di semua tempat, tidak ada string 'google'/'github' lepas
 * berulang-ulang di luar SocialiteController.
 */
enum AuthProvider: string
{
    case Email = 'email';
    case Google = 'google';
    case Github = 'github';

    /**
     * Label singkat - dipakai di email admin (Metode Registrasi/Login).
     */
    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Google => 'Google',
            self::Github => 'GitHub',
        };
    }

    /**
     * Label untuk baris "Metode Login" di email milik user sendiri - kasus
     * Email sengaja lebih deskriptif ("Email & Password") dibanding label()
     * yang dipakai admin, sesuai teks yang diminta di masing-masing desain.
     */
    public function loginMethodLabel(): string
    {
        return match ($this) {
            self::Email => 'Email & Password',
            self::Google => 'Google',
            self::Github => 'GitHub',
        };
    }
}
