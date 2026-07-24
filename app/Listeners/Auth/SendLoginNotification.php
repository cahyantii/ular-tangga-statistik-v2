<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Services\Auth\AuthMailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLoginNotification implements ShouldQueue
{
    public function __construct(private readonly AuthMailService $authMail)
    {
    }

    public function handle(UserLoggedIn $event): void
    {
        $this->authMail->sendLoginNotification(
            $event->user,
            $event->provider,
            $event->ipAddress,
            $event->userAgent,
            $event->occurredAt,
        );
    }
}
