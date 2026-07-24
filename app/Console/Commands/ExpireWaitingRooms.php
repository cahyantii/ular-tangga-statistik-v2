<?php

namespace App\Console\Commands;

use App\Enums\GameStatus;
use App\Models\Room;
use App\Services\Notification\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Room Quick Match/Private yang belum terisi lawan sampai `expires_at`
 * lewat (Tahap 12a) otomatis dibatalkan, supaya tidak menyumbat pengecekan
 * "sesi aktif" pembuatnya selamanya jika lawan tak kunjung datang.
 */
class ExpireWaitingRooms extends Command
{
    protected $signature = 'game:expire-waiting-rooms';

    protected $description = 'Tandai Abandoned room Waiting yang sudah melewati batas waktu tunggu lawan';

    public function __construct(private readonly NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $staleRoomIds = Room::query()
            ->where('status', GameStatus::Waiting)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->pluck('id');

        $expiredCount = 0;

        foreach ($staleRoomIds as $roomId) {
            // lockForUpdate() + cek ulang status DI DALAM transaksi (pola sama
            // seperti MatchmakingService::cancelRoom()) - tanpa ini, room yang
            // baru saja terisi lawan (join mengunci baris Room yang sama lewat
            // lockForUpdate() di quickMatch()/joinPrivateRoom()) bisa balik
            // ditimpa jadi Abandoned oleh cron ini walau GameSession-nya sudah
            // Playing, karena SELECT awal di atas dilakukan TANPA lock.
            $room = DB::transaction(function () use ($roomId) {
                $room = Room::query()->lockForUpdate()->with(['gameSession', 'createdBy'])->find($roomId);

                if ($room === null || $room->status !== GameStatus::Waiting) {
                    return null;
                }

                $room->update(['status' => GameStatus::Abandoned]);
                $room->gameSession?->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);

                return $room;
            });

            if ($room === null) {
                continue;
            }

            $expiredCount++;

            // Tahap 19: Bell Notification - dikirim setelah transaksi commit.
            if ($room->createdBy !== null) {
                $this->notifications->send($room->createdBy, $this->notifications->payloadRoomExpired($room));
            }
            $this->notifications->sendToAdmins($this->notifications->payloadRoomExpiredAdmin($room));
        }

        $this->info("Room Waiting yang dibatalkan karena kedaluwarsa: {$expiredCount}");

        return self::SUCCESS;
    }
}
