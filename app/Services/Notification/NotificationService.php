<?php

namespace App\Services\Notification;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Room;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Collection;

/**
 * Titik pusat sistem notifikasi: membangun payload (title/message/icon/color/
 * category/url + field opsional game_id/room_id/achievement_id/sender_id) dan
 * mengirimnya lewat AppNotification (channel database + broadcast bawaan
 * Laravel). Metode payload*() murni/tanpa efek samping - aman dipanggil di
 * dalam DB::transaction() manapun (lihat pemakaiannya di GameSessionService,
 * yang MENUNDA send() sesungguhnya sampai setelah commit, sama seperti
 * dispatchEvents() menunda broadcast domain event).
 *
 * Kategori notifikasi (dipakai sebagai filter pill di Notification Center):
 * game, achievement, user, system, multiplayer, feedback.
 */
class NotificationService
{
    public function send(User $user, array $payload): void
    {
        $user->notify(new AppNotification($payload));
    }

    public function sendToAdmins(array $payload): void
    {
        $this->adminUsers()->each(fn (User $admin) => $this->send($admin, $payload));
    }

    public function adminUsers(): Collection
    {
        return User::query()->where('role', UserRole::Admin)->get();
    }

    public function payloadGameFinished(GamePlayer $player, GameSession $session, bool $won): array
    {
        $lawan = $session->players->first(fn (GamePlayer $p) => $p->id !== $player->id);
        $lawanNama = $lawan === null ? 'lawan' : ($lawan->is_robot ? 'Robot' : ($lawan->user->name ?? 'lawan'));
        $alasan = $session->win_reason?->label() ?? '';

        return [
            'title' => $won ? 'Anda Menang!' : 'Anda Kalah',
            'message' => $won
                ? "Anda memenangkan permainan melawan {$lawanNama} ({$alasan}) dengan skor {$player->skor}."
                : "Anda kalah melawan {$lawanNama} ({$alasan}). Skor akhir: {$player->skor}.",
            'icon' => $won ? 'trophy' : 'close',
            'color' => $won ? 'green' : 'red',
            'category' => 'game',
            'url' => null,
            'game_id' => $session->id,
        ];
    }

    public function payloadGameFinishedAdmin(GamePlayer $player, GameSession $session): array
    {
        $nama = $player->is_robot ? 'Robot' : ($player->user->name ?? 'Pemain');

        return [
            'title' => 'Permainan Selesai',
            'message' => "{$nama} menyelesaikan permainan ({$session->mode->label()}) dengan skor {$player->skor}.",
            'icon' => 'gamepad',
            'color' => 'blue',
            'category' => 'game',
            'url' => null,
            'game_id' => $session->id,
        ];
    }

    public function payloadAchievementUnlocked(array $achievement): array
    {
        return [
            'title' => 'Achievement Terbuka!',
            'message' => "Anda membuka achievement \"{$achievement['nama']}\": {$achievement['deskripsi']}",
            'icon' => $achievement['icon'] ?: 'trophy',
            'color' => 'purple',
            'category' => 'achievement',
            'url' => null,
            'achievement_id' => $achievement['kode'] ?? null,
        ];
    }

    public function payloadAchievementUnlockedAdmin(User $user, array $achievement): array
    {
        return [
            'title' => 'Achievement Diperoleh',
            'message' => "{$user->name} membuka achievement \"{$achievement['nama']}\".",
            'icon' => $achievement['icon'] ?: 'trophy',
            'color' => 'purple',
            'category' => 'achievement',
            'url' => null,
            'achievement_id' => $achievement['kode'] ?? null,
            'sender_id' => $user->id,
        ];
    }

    public function payloadPersonalBest(GameSession $session, int $skor): array
    {
        return [
            'title' => 'Rekor Skor Baru!',
            'message' => "Anda mencetak skor tertinggi baru: {$skor} poin.",
            'icon' => 'medal',
            'color' => 'amber',
            'category' => 'game',
            'url' => null,
            'game_id' => $session->id,
        ];
    }

    public function payloadPersonalBestAdmin(User $user, int $skor): array
    {
        return [
            'title' => 'Rekor Baru Pemain',
            'message' => "{$user->name} mencetak skor tertinggi baru: {$skor} poin.",
            'icon' => 'medal',
            'color' => 'amber',
            'category' => 'game',
            'url' => null,
            'sender_id' => $user->id,
        ];
    }

    public function payloadOpponentJoined(Room $room, User $joiner): array
    {
        return [
            'title' => 'Lawan Bergabung',
            'message' => "{$joiner->name} bergabung ke room Anda. Permainan dimulai!",
            'icon' => 'users',
            'color' => 'green',
            'category' => 'multiplayer',
            'url' => null,
            'room_id' => $room->id,
            'sender_id' => $joiner->id,
        ];
    }

    public function payloadRoomActivityAdmin(Room $room, string $message): array
    {
        return [
            'title' => 'Aktivitas Room Multiplayer',
            'message' => $message,
            'icon' => 'gamepad',
            'color' => 'blue',
            'category' => 'multiplayer',
            'url' => null,
            'room_id' => $room->id,
        ];
    }

    public function payloadOpponentDisconnected(GamePlayer $disconnected): array
    {
        $nama = $disconnected->user->name ?? 'Lawan';

        return [
            'title' => 'Lawan Terputus',
            'message' => "{$nama} terputus dari permainan. Menunggu koneksi kembali...",
            'icon' => 'wifi',
            'color' => 'amber',
            'category' => 'multiplayer',
            'url' => null,
        ];
    }

    public function payloadOpponentReconnected(GamePlayer $reconnected): array
    {
        $nama = $reconnected->user->name ?? 'Lawan';

        return [
            'title' => 'Lawan Kembali',
            'message' => "{$nama} sudah kembali terhubung. Permainan dilanjutkan.",
            'icon' => 'wifi',
            'color' => 'green',
            'category' => 'multiplayer',
            'url' => null,
        ];
    }

    public function payloadRoomExpired(Room $room): array
    {
        return [
            'title' => 'Room Kedaluwarsa',
            'message' => 'Room Anda dibatalkan karena tidak ada lawan yang bergabung.',
            'icon' => 'hourglass',
            'color' => 'red',
            'category' => 'multiplayer',
            'url' => null,
            'room_id' => $room->id,
        ];
    }

    public function payloadRoomExpiredAdmin(Room $room): array
    {
        $nama = $room->createdBy->name ?? 'seorang pengguna';

        return [
            'title' => 'Room Kedaluwarsa',
            'message' => "Room {$room->kode_room} milik {$nama} kedaluwarsa tanpa lawan.",
            'icon' => 'hourglass',
            'color' => 'amber',
            'category' => 'multiplayer',
            'url' => null,
            'room_id' => $room->id,
        ];
    }

    public function payloadUserRegistered(User $user): array
    {
        return [
            'title' => 'Pengguna Baru Mendaftar',
            'message' => "{$user->name} ({$user->email}) baru saja mendaftar.",
            'icon' => 'user-plus',
            'color' => 'blue',
            'category' => 'user',
            'url' => route('admin.management.users.index'),
            'sender_id' => $user->id,
        ];
    }

    public function payloadUserLoggedInAdmin(User $user, AuthProvider $provider, string $ipAddress): array
    {
        return [
            'title' => 'Aktivitas Login Pengguna',
            'message' => "{$user->name} ({$user->email}) baru saja login (Provider: {$provider->value}, IP: {$ipAddress}).",
            'icon' => 'log-in',
            'color' => 'blue',
            'category' => 'user',
            'url' => route('admin.management.users.index'),
            'sender_id' => $user->id,
        ];
    }


    public function payloadImportSuccess(string $label, int $count): array
    {
        return [
            'title' => 'Import Data Berhasil',
            'message' => "{$count} {$label} berhasil diimpor.",
            'icon' => 'upload-cloud',
            'color' => 'green',
            'category' => 'system',
            'url' => null,
        ];
    }

    public function payloadExportPerformed(string $label): array
    {
        return [
            'title' => 'Export Data Dilakukan',
            'message' => "Data {$label} diekspor oleh admin.",
            'icon' => 'download',
            'color' => 'blue',
            'category' => 'system',
            'url' => null,
        ];
    }

    public function payloadFeedbackReceived(Feedback $feedback): array
    {
        return [
            'title' => $feedback->type->label().' Baru',
            'message' => "{$feedback->user->name}: {$feedback->subject}",
            'icon' => 'mail',
            'color' => 'blue',
            'category' => 'feedback',
            'url' => route('admin.management.feedback.index'),
            'sender_id' => $feedback->user_id,
        ];
    }
}
