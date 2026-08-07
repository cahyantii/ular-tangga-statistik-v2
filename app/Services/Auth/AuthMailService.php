<?php

namespace App\Services\Auth;

use App\Enums\AuthProvider;
use App\Mail\Auth\AdminLoginActivityMail;
use App\Mail\Auth\AdminNewUserMail;
use App\Mail\Auth\LoginNotificationMail;
use App\Mail\Auth\WelcomeMail;
use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

/**
 * Satu titik pusat pengiriman SEMUA email auth (welcome & login milik user,
 * plus salinannya ke admin) - dipakai oleh 4 listener di app/Listeners/Auth
 * supaya logika "siapa saja admin yang dikirimi" dan pemanggilan
 * Mail::to()->queue() tidak terduplikasi di tiap listener (lihat requirement
 * "reusable code, tidak ada duplikasi").
 */
class AuthMailService
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function sendWelcome(User $user, AuthProvider $provider, Carbon $occurredAt): void
    {
        if (! config('mail.send_welcome', false)) {
            return;
        }

        Mail::to($user->email)->queue(new WelcomeMail($user, $provider, $occurredAt));
    }

    public function sendLoginNotification(User $user, AuthProvider $provider, string $ipAddress, ?string $userAgent, Carbon $occurredAt): void
    {
        if (! config('mail.notify_login', false)) {
            return;
        }

        Mail::to($user->email)->queue(new LoginNotificationMail($user, $provider, $ipAddress, $userAgent, $occurredAt));
    }

    public function notifyAdminsOfNewUser(User $newUser, AuthProvider $provider, Carbon $occurredAt): void
    {
        if (! config('mail.notify_admin_new_user', false)) {
            return;
        }

        $totalUsers = User::count();

        $this->adminRecipients($newUser)->each(
            fn (User $admin) => Mail::to($admin->email)->queue(new AdminNewUserMail($newUser, $provider, $totalUsers, $occurredAt))
        );
    }

    public function notifyAdminsOfLoginActivity(User $user, AuthProvider $provider, string $ipAddress, ?string $userAgent, Carbon $occurredAt): void
    {
        if (! config('mail.notify_admin_login', false)) {
            return;
        }

        $this->adminRecipients($user)->each(
            fn (User $admin) => Mail::to($admin->email)->queue(new AdminLoginActivityMail($user, $provider, $ipAddress, $userAgent, $occurredAt))
        );
    }


    /**
     * Semua admin KECUALI pelaku event itu sendiri (kalau kebetulan admin
     * yang mendaftar/login) - supaya admin tidak menerima email duplikat
     * tentang aktivitasnya sendiri, karena dia sudah dapat email pribadinya
     * lewat sendWelcome()/sendLoginNotification() di atas.
     */
    private function adminRecipients(User $exclude): Collection
    {
        return $this->notifications->adminUsers()->reject(
            fn (User $admin) => $admin->is($exclude)
        );
    }
}
