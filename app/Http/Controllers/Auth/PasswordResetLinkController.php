<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ThrottleWaitMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => $this->throttledMessage($request->string('email')->value())]);
        }

        // Pesan SELALU sama persis baik email terdaftar (link betulan
        // terkirim, $status === RESET_LINK_SENT) maupun tidak ($status ===
        // INVALID_USER) - sengaja tidak pakai __($status) bawaan Laravel yang
        // membedakan dua kasus ini ("We can't find a user with that email
        // address." vs "We have emailed..."), karena perbedaan itu bisa
        // dipakai menebak email mana saja yang terdaftar di sistem (user
        // enumeration). Kasus RESET_THROTTLED di atas TETAP beda pesan
        // (sesuai permintaan sebelumnya, lihat throttledMessage()) - ini
        // celah kecil yang disadari: throttle hanya bisa terjadi untuk email
        // yang memang terdaftar, jadi teorinya masih bisa dipakai menebak
        // lewat dua kali submit cepat, tapi trade-off ini diterima demi pesan
        // "tunggu X detik" yang jelas untuk pengguna sungguhan.
        return back()->with(
            'status',
            'Jika email tersebut terdaftar di sistem kami, tautan reset password telah dikirim. Silakan periksa kotak masuk (dan folder spam) email kamu.'
        );
    }

    /**
     * Password::RESET_THROTTLED tidak membawa sisa waktu tunggu apa pun -
     * broker hanya mengembalikan string status ('passwords.throttled'), yang
     * secara bawaan diterjemahkan jadi "Please wait before retrying." tanpa
     * angka. Sisa waktu (retry_after) dihitung ulang di sini dari
     * `password_reset_tokens.created_at` milik email tsb + konfigurasi
     * auth.passwords.users.throttle (default 60 detik) - satu-satunya sumber
     * kebenaran yang juga dipakai DatabaseTokenRepository::tokenRecentlyCreated()
     * untuk memutuskan status throttled itu sendiri.
     */
    private function throttledMessage(string $email): string
    {
        $throttleSeconds = (int) config('auth.passwords.users.throttle', 60);

        $createdAt = DB::table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->where('email', $email)
            ->value('created_at');

        $retryAfter = $createdAt
            ? $throttleSeconds - now()->diffInSeconds(Carbon::parse($createdAt))
            : $throttleSeconds;

        return ThrottleWaitMessage::forgotPassword((int) $retryAfter);
    }
}
