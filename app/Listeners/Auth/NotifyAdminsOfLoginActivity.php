<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Services\Auth\AuthMailService;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfLoginActivity implements ShouldQueue
{
    public function __construct(
        private readonly AuthMailService $authMail,
        private readonly NotificationService $notifications,
    ) {
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

        $this->notifications->sendToAdmins(
            $this->notifications->payloadUserLoggedInAdmin($event->user, $event->provider, $event->ipAddress)
        );
    }
}

