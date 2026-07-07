<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipicu saat nilai dadu melebihi sisa langkah ke Finish (Tahap 17, aturan
 * exact-landing) — pion tidak bergerak, giliran tetap dianggap sudah dipakai.
 */
class MovementBlocked implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $gamePlayer,
        public readonly int $nilaiDadu,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->gamePlayer->id,
            'nilai_dadu' => $this->nilaiDadu,
        ];
    }
}
