<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\GameSession;
use App\Models\Room;
use App\Models\User;
use App\Policies\CertificatePolicy;
use App\Policies\GameSessionPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(GameSession::class, GameSessionPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);

        // Admin selalu diizinkan lolos semua Policy di atas (Tahap 13).
        Gate::before(fn (User $user) => $user->role === UserRole::Admin ? true : null);

        // User yang sudah login & membuka halaman guest (login/register) diarahkan
        // sesuai role-nya, bukan selalu ke /dashboard (Tahap 13).
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = $request->user();

            return $user->role === UserRole::Admin
                ? route('admin.dashboard')
                : route('dashboard');
        });

        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        $this->warnIfAuthMailNotConfigured();
    }

    /**
     * Peringatan sekali-lihat di log untuk developer (bukan untuk end user)
     * kalau SMTP atau kredensial OAuth belum diisi - supaya kelupaan mengisi
     * .env tidak berakhir sebagai "kenapa email/OAuth tidak jalan" tanpa
     * petunjuk. Hanya dicek di lokal supaya tidak membanjiri log produksi
     * (dan otomatis tidak aktif saat testing karena phpunit.xml override
     * MAIL_MAILER ke "array").
     */
    private function warnIfAuthMailNotConfigured(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        if (config('mail.default') === 'smtp'
            && (blank(config('mail.mailers.smtp.username')) || blank(config('mail.mailers.smtp.password')))
        ) {
            Log::warning('MAIL belum dikonfigurasi lengkap: MAIL_USERNAME/MAIL_PASSWORD kosong di .env. Email verifikasi & reset password tidak akan benar-benar terkirim sampai SMTP diisi (lihat contoh Gmail/Mailtrap/Brevo di .env).');
        }

        if (config('mail.default') === 'resend' && blank(config('services.resend.key'))) {
            Log::warning('MAIL_MAILER=resend tapi RESEND_API_KEY kosong di .env. Email verifikasi & reset password tidak akan benar-benar terkirim sampai ini diisi.');
        }

        foreach (['google', 'github'] as $provider) {
            if (blank(config("services.{$provider}.client_id")) || blank(config("services.{$provider}.client_secret"))) {
                $envPrefix = strtoupper($provider);

                Log::warning("OAuth \"{$provider}\" belum dikonfigurasi: {$envPrefix}_CLIENT_ID/{$envPrefix}_CLIENT_SECRET kosong di .env. Tombol login {$provider} akan menampilkan pesan error sampai ini diisi.");
            }
        }
    }
}
