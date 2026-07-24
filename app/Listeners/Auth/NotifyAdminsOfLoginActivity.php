<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Services\Auth\AuthMailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfLoginActivity implements ShouldQueue
{
    public function __construct(private readonly AuthMailService $authMail)
    {
    }

    public function handle(UserLoggedIn $event): void
    {
        $this->authMail->notifyAdminsOfLoginActivity(
            $event->user,
            $event->provider,
            $event->ipAddress,
            $event->userAgent,
            $event->occurredAt,
        );
    }
}
