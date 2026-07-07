<?php

namespace App\Events\Game\Concerns;

use App\Enums\GameMode;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Str;

/**
 * Channel selection bersama untuk semua event Game (Tahap 12b). Vs Robot
 * sengaja TIDAK broadcast — hanya ada satu manusia, dan hasil aksinya sendiri
 * sudah didapat langsung lewat respons HTTP (Tahap 10b/17). Broadcasting
 * hanya berguna di Multiplayer, supaya LAWAN di browser lain tahu tanpa
 * polling terus-menerus.
 *
 * Setiap event yang memakai trait ini WAJIB punya property
 * `public readonly GameSession $gameSession` dan mendefinisikan
 * `broadcastWith()` sendiri — sengaja tidak diseragamkan lewat trait ini,
 * karena payload tiap event berbeda dan harus dipilih secara eksplisit demi
 * menghindari kebocoran data sensitif (lihat QuestionPresented::broadcastWith(),
 * yang sengaja TIDAK menyertakan isi soal).
 */
trait BroadcastsToGameRoom
{
    public function broadcastOn(): array
    {
        if ($this->gameSession->mode !== GameMode::Multiplayer || $this->gameSession->room_id === null) {
            return [];
        }

        return [new PresenceChannel('room.'.$this->gameSession->room_id)];
    }

    public function broadcastAs(): string
    {
        return Str::kebab(class_basename(static::class));
    }
}
