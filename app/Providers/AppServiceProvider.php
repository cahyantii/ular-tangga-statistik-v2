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
    }
}
