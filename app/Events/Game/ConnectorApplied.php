<?php

namespace App\Events\Game;

use App\Enums\ConnectorType;
use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConnectorApplied implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $gamePlayer,
        public readonly ConnectorType $jenis,
        public readonly int $posisiAwal,
        public readonly int $posisiAkhir,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->gamePlayer->id,
            'jenis' => $this->jenis->value,
            'posisi_awal' => $this->posisiAwal,
            'posisi_akhir' => $this->posisiAkhir,
        ];
    }
}
