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

class AdminNewUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $newUser,
        public readonly AuthProvider $provider,
        public readonly int $totalUsers,
        public readonly Carbon $occurredAt,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Pengguna Baru Bergabung');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.admin-new-user',
            with: [
                'providerLabel' => $this->provider->label(),
                'dashboardUrl' => route('admin.dashboard'),
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi admin (pengguna baru: {$this->newUser->email}): {$exception->getMessage()}");
    }
}
