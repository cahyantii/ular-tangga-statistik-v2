<?php

namespace App\Mail\Auth;

use App\Enums\AuthProvider;
use App\Models\User;
use App\Support\BrowserDetector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoginNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly AuthProvider $provider,
        public readonly string $ipAddress,
        public readonly ?string $userAgent,
        public readonly Carbon $occurredAt,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->provider) {
                AuthProvider::Email => 'Login Berhasil',
                AuthProvider::Google => 'Login Berhasil melalui Google',
                AuthProvider::Github => 'Login Berhasil melalui GitHub',
            },
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.login-notification',
            with: [
                'providerLabel' => $this->provider->loginMethodLabel(),
                'browserName' => BrowserDetector::name($this->userAgent),
                'securityNote' => match ($this->provider) {
                    AuthProvider::Email => 'Apabila kamu merasa tidak melakukan login tersebut, segera ubah password akunmu.',
                    AuthProvider::Google => 'Jika bukan kamu, segera amankan akun Google milikmu.',
                    AuthProvider::Github => 'Jika bukan kamu, segera amankan akun GitHub milikmu.',
                },
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi login ke {$this->user->email}: {$exception->getMessage()}");
    }
}
