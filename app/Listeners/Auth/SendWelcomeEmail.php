<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Auth\AuthMailService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public function __construct(private readonly AuthMailService $authMail)
    {
    }

    public function handle(UserRegistered $event): void
    {
        $this->authMail->sendWelcome($event->user, $event->provider, $event->occurredAt);
    }
}
