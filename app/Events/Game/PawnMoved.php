<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PawnMoved implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $gamePlayer,
        public readonly int $posisiSebelum,
        public readonly int $posisiSesudah,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->gamePlayer->id,
            'posisi_sebelum' => $this->posisiSebelum,
            'posisi_sesudah' => $this->posisiSesudah,
        ];
    }
}
