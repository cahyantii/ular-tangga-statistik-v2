<?php

namespace App\Services\Auth;

use App\Exceptions\OAuthProviderNotConfiguredException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Satu implementasi tunggal find-or-create untuk SEMUA provider OAuth
 * (Google & GitHub) dan SEMUA entry point (tombol Google/GitHub di halaman
 * Login maupun Register) - dipakai bersama oleh SocialiteController supaya
 * tidak ada dua alur berbeda untuk hal yang sama (lihat requirement:
 * "jangan membuat dua implementasi berbeda").
 *
 * Aturan pencocokan akun:
 * 1) Sudah pernah OAuth dengan provider ini sebelumnya -> cocokkan lewat
 *    kolom {provider}_id (mis. google_id), langsung pakai akun itu.
 * 2) Belum pernah, tapi email sudah terdaftar (mis. daftar manual atau lewat
 *    provider lain) -> tautkan provider_id ke akun yang sudah ada tsb, JANGAN
 *    buat akun duplikat.
 * 3) Email belum terdaftar sama sekali -> buat akun baru. Role sengaja TIDAK
 *    diisi di sini (lihat User::$fillable) - kolom `role` sudah default
 *    'player' di migrasi, konsisten dengan requirement "role = pemain".
 *    Password diisi acak (tidak pernah dipakai user, login OAuth tidak lewat
 *    password) dan email_verified_at langsung diisi karena provider OAuth
 *    sudah memverifikasi email tsb.
 */
class SocialAuthService
{
    /**
     * Dipanggil SEBELUM redirect ke provider maupun sebelum menukar callback
     * - tanpa ini, Socialite tetap mengarah ke Google/GitHub tanpa client_id
     * dan PROVIDER itu sendiri yang menampilkan error mentah ("Missing
     * required parameter: client_id"), bukan aplikasi kita. Dicek di sini
     * (bukan langsung di controller) supaya redirect() dan callback() di
     * SocialiteController pakai satu sumber kebenaran yang sama.
     *
     * @throws OAuthProviderNotConfiguredException
     */
    public function assertProviderConfigured(string $provider): void
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (blank($clientId) || blank($clientSecret)) {
            throw OAuthProviderNotConfiguredException::forProvider($provider);
        }
    }

    public function findOrCreateUser(string $provider, SocialiteUser $socialiteUser): User
    {
        $providerIdColumn = "{$provider}_id";

        return DB::transaction(function () use ($providerIdColumn, $socialiteUser) {
            $existing = User::query()
                ->where($providerIdColumn, $socialiteUser->getId())
                ->first();

            if ($existing) {
                return $existing;
            }

            $byEmail = User::query()
                ->where('email', $socialiteUser->getEmail())
                ->first();

            if ($byEmail) {
                $byEmail->update([$providerIdColumn => $socialiteUser->getId()]);

                return $byEmail;
            }

            $newUser = User::create([
                'name' => $socialiteUser->getName() ?: $socialiteUser->getNickname() ?: 'Pengguna',
                'email' => $socialiteUser->getEmail(),
                'avatar' => $socialiteUser->getAvatar(),
                'password' => Hash::make(Str::random(40)),
                $providerIdColumn => $socialiteUser->getId(),
                'email_verified_at' => now(),
            ]);

            // create() tidak menarik kembali nilai default kolom dari DB (mis.
            // `role` yang default 'player' di migrasi) ke instance in-memory -
            // fresh() memastikan pemanggil (SocialiteController, yang langsung
            // memakai $user->dashboardRouteName() pada request yang sama)
            // melihat state yang benar-benar sama dengan baris di database.
            //
            // fresh() mengembalikan instance BARU hasil query ulang, sehingga
            // wasRecentlyCreated (flag bawaan Eloquent) ikut ter-reset ke
            // false - flag itu di-set ulang manual di sini karena
            // SocialiteController memakainya untuk membedakan "akun baru"
            // (kirim UserRegistered -> email selamat datang) dari "akun lama
            // login lagi" (kirim UserLoggedIn -> notifikasi login).
            $freshUser = $newUser->fresh();
            $freshUser->wasRecentlyCreated = true;

            return $freshUser;
        });
    }
}
