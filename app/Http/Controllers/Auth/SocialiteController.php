<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuthProvider;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Satu-satunya controller OAuth untuk Google & GitHub, dipakai bersama oleh
 * tombol "Google"/"GitHub" di halaman Login MAUPUN Register (keduanya
 * mengarah ke route yang persis sama) - lihat requirement "jangan membuat
 * dua implementasi berbeda, gunakan service dan controller yang sama".
 * Parameter {provider} dibatasi lewat route (whereIn: google, github), lihat
 * routes/auth.php.
 */
class SocialiteController extends Controller
{
    public function __construct(private readonly SocialAuthService $socialAuth)
    {
    }

    /**
     * Alihkan ke halaman login provider (Google/GitHub).
     *
     * Google secara khusus dipaksa selalu menampilkan halaman "Pilih Akun"
     * (prompt=select_account) supaya pengguna bisa memilih akun Google mana
     * yang dipakai setiap klik tombol, alih-alih auto-login ke akun terakhir
     * yang dipakai browser. GitHub tidak mendukung parameter ini (tidak ada
     * efeknya di sisi GitHub), jadi sengaja tidak dikirim untuk provider itu.
     *
     * @throws \App\Exceptions\OAuthProviderNotConfiguredException
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->socialAuth->assertProviderConfigured($provider);

        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        if ($provider === 'google') {
            $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    /**
     * Tangani callback dari provider: cari/buat user, login, lalu redirect
     * sesuai role - persis logika yang sama dipakai login manual
     * (User::dashboardRouteName(), lihat AuthenticatedSessionController).
     *
     * @throws \App\Exceptions\OAuthProviderNotConfiguredException
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->socialAuth->assertProviderConfigured($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            Log::warning("OAuth {$provider} callback gagal", ['message' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('oauth_error', 'Gagal masuk dengan '.ucfirst($provider).'. Silakan coba lagi.');
        }

        $user = $this->socialAuth->findOrCreateUser($provider, $socialiteUser);
        $authProvider = AuthProvider::from($provider);
        $occurredAt = now();

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        // wasRecentlyCreated dipertahankan secara sengaja oleh
        // SocialAuthService::findOrCreateUser() (lihat komentar di sana) -
        // satu-satunya cara membedakan "akun OAuth baru dibuat" (kirim email
        // selamat datang) dari "akun lama login lagi lewat OAuth" (kirim
        // notifikasi login) tanpa dua alur terpisah.
        if ($user->wasRecentlyCreated) {
            UserRegistered::dispatch($user, $authProvider, $occurredAt);
        } else {
            UserLoggedIn::dispatch($user, $authProvider, $request->ip(), $request->userAgent(), $occurredAt);
        }

        return redirect()->intended(route($user->dashboardRouteName(), absolute: false));
    }
}
