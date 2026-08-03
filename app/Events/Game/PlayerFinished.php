<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerFinished implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $player,
        public readonly int $rank,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->player->id,
            'player_nama' => $this->player->nama,
            'rank' => $this->rank,
        ];
    }
}
