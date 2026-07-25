<?php

namespace App\Policies;

use App\Enums\GameStatus;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * User boleh join room jika room masih menunggu (Waiting) dan belum penuh
     * (kapasitas per-room dari `jumlah_pemain`, 2-6, bukan hardcode 2).
     */
    public function join(User $user, Room $room): bool
    {
        if ($room->status !== GameStatus::Waiting) {
            return false;
        }

        $jumlahBergabung = $room->gameSession?->players()->count() ?? 0;

        return $jumlahBergabung < $room->jumlah_pemain;
    }

    /**
     * Hanya pembuat room yang boleh membatalkan room privat.
     */
    public function cancel(User $user, Room $room): bool
    {
        return $room->created_by === $user->id
            && $room->status === GameStatus::Waiting;
    }

    public function view(User $user, Room $room): bool
    {
        if ($room->created_by === $user->id) {
            return true;
        }

        return $room->gameSession?->players()->where('user_id', $user->id)->exists() ?? false;
    }
}
