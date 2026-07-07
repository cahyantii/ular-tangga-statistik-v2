<?php

namespace App\Events\Game;

use App\Enums\ScoreEventType;
use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $gamePlayer,
        public readonly ScoreEventType $eventType,
        public readonly int $delta,
        public readonly int $skorBaru,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->gamePlayer->id,
            'event_type' => $this->eventType->value,
            'delta' => $this->delta,
            'skor_baru' => $this->skorBaru,
        ];
    }
}
