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

class AdminLoginActivityMail extends Mailable implements ShouldQueue
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
        return new Envelope(subject: 'Aktivitas Login Pengguna');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.admin-login-activity',
            with: [
                'providerLabel' => $this->provider->label(),
                'browserName' => BrowserDetector::name($this->userAgent),
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi admin (aktivitas login: {$this->user->email}): {$exception->getMessage()}");
    }
}
