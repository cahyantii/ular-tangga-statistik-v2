<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Auth\AuthMailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfNewUser implements ShouldQueue
{
    public function __construct(private readonly AuthMailService $authMail)
    {
    }

    public function handle(UserRegistered $event): void
    {
        $this->authMail->notifyAdminsOfNewUser($event->user, $event->provider, $event->occurredAt);
    }
}
