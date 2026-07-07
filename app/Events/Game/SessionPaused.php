<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipicu saat heartbeat seorang pemain multiplayer terdeteksi basi oleh
 * command `game:check-heartbeats` (Tahap 12a) — sesi dijeda, pemain lain
 * diberi tahu lewat channel realtime (broadcastable sejak Tahap 12b).
 */
class SessionPaused implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $disconnectedPlayer,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'disconnected_game_player_id' => $this->disconnectedPlayer->id,
        ];
    }
}
