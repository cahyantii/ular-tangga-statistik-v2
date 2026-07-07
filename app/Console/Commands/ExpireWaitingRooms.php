<?php

namespace App\Console\Commands;

use App\Enums\GameStatus;
use App\Models\Room;
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

    public function handle(): int
    {
        $staleRooms = Room::query()
            ->where('status', GameStatus::Waiting)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('gameSession')
            ->get();

        foreach ($staleRooms as $room) {
            DB::transaction(function () use ($room) {
                $room->update(['status' => GameStatus::Abandoned]);
                $room->gameSession?->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);
            });
        }

        $this->info("Room Waiting yang dibatalkan karena kedaluwarsa: {$staleRooms->count()}");

        return self::SUCCESS;
    }
}
