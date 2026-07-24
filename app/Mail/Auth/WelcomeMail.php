<?php

namespace App\Mail\Auth;

use App\Enums\AuthProvider;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly AuthProvider $provider,
        public readonly Carbon $occurredAt,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat Datang di Ular Tangga Statistik Indonesia 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.welcome',
            with: [
                'isOauth' => $this->provider !== AuthProvider::Email,
                'providerLabel' => $this->provider->label(),
                'dashboardUrl' => route($this->user->dashboardRouteName()),
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Gagal mengirim email selamat datang ke {$this->user->email}: {$exception->getMessage()}");
    }
}
