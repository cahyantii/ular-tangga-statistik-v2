<?php

use App\Exceptions\ActiveGameSessionExistsException;
use App\Exceptions\GameAlreadyFinishedException;
use App\Exceptions\NotYourTurnException;
use App\Exceptions\OAuthProviderNotConfiguredException;
use App\Exceptions\QuestionExpiredException;
use App\Exceptions\RoomFullException;
use App\Http\Middleware\EnsureAdminIsVerified;
use App\Http\Middleware\EnsureNoActiveGameSession;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Support\ThrottleWaitMessage;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'admin.verified' => EnsureAdminIsVerified::class,
            'no-active-session' => EnsureNoActiveGameSession::class,
        ]);

        // Dibutuhkan supaya Auth::logoutOtherDevices() (dipanggil saat ganti
        // password, lihat PasswordController) benar-benar memutus sesi
        // browser lain - tanpa middleware ini, sesi lama tetap valid sampai
        // logout manual meski password sudah berbeda.
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (GameAlreadyFinishedException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (NotYourTurnException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
        });

        $exceptions->render(function (QuestionExpiredException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }
        });

        $exceptions->render(function (RoomFullException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (ActiveGameSessionExistsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        });

        // Tombol Google/GitHub diklik tapi client_id/client_secret kosong di
        // .env - tanpa handler ini, exception akan lolos apa adanya (Whoops
        // page saat APP_DEBUG=true, 500 polos saat false). Sesuai keputusan
        // produk: TIDAK ada banner/alert di UI Login/Register untuk kasus
        // ini (lihat auth-layout.blade.php) - detail lengkap hanya ditulis
        // ke storage/logs/laravel.log untuk developer, dan pengguna cukup
        // diam-diam dikembalikan ke halaman asalnya (Login/Register, lewat
        // back()) tanpa tampilan rusak. session('oauth_error') tetap
        // di-flash untuk keperluan internal meski tidak dirender di mana pun.
        $exceptions->render(function (OAuthProviderNotConfiguredException $e, Request $request) {
            Log::error($e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            return back(fallback: route('login'))->with('oauth_error', $e->getMessage());
        });

        // Route forgot-password (`password.email`) dibatasi `throttle:6,1` di
        // routes/auth.php - saat batas itu terlampaui, middleware bawaan
        // Laravel melempar ThrottleRequestsException dan (tanpa handler ini)
        // akan menampilkan halaman error 429 polos ("Too Many Attempts."),
        // bukan pesan ramah di form. Sengaja dibatasi hanya untuk route ini
        // (bukan exception handler global) supaya throttle route lain tidak
        // ikut berubah perilakunya. Sisa waktu tunggu diambil dari header
        // Retry-After yang sudah dihitung RateLimiter (lihat
        // ThrottleRequests::buildException()), lalu diformat lewat
        // ThrottleWaitMessage yang sama dipakai PasswordResetLinkController
        // untuk throttle internal Password broker, supaya kedua sumber
        // throttle tampil konsisten.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! $request->routeIs('password.email')) {
                return null;
            }

            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 60);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => ThrottleWaitMessage::forgotPassword($retryAfter)]);
        });
    })->create();
