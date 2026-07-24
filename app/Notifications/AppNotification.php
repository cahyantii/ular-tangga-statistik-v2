<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Satu kelas Notification generik untuk seluruh sistem notifikasi (bukan satu
 * kelas per jenis event) - payload (title/message/icon/color/category/url dan
 * field opsional game_id/room_id/achievement_id/sender_id) dibangun oleh
 * NotificationService, kelas ini murni menyalurkannya lewat channel `database`
 * (tabel notifications standar Laravel) dan `broadcast` (private channel
 * bawaan Notifiable, App.Models.User.{id}, sudah diotorisasi di routes/channels.php).
 *
 * SENGAJA TIDAK implements ShouldQueue: pengiriman harus sinkron supaya baris
 * DB + broadcast realtime terjadi seketika tanpa bergantung pada queue worker
 * yang mungkin tidak berjalan (lihat NotificationService::send()/queue-and-flush
 * di GameSessionService untuk disiplin "jangan broadcast sebelum commit").
 */
class AppNotification extends Notification
{
    public function __construct(private readonly array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload;
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
