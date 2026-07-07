<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event domain untuk giliran permainan (Tahap 2/5, keputusan final:
 * arsitektur event-driven). Dikumpulkan selama DB::transaction() satu giliran
 * lalu di-dispatch setelah transaksi commit (Tahap 17, koreksi kritis) — lihat
 * App\Services\Game\GameSessionService::playTurn().
 *
 * Broadcastable sejak Tahap 12b — lihat BroadcastsToGameRoom untuk aturan
 * channel (hanya Multiplayer yang benar-benar broadcast).
 */
class DiceRolled implements ShouldBroadcast
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
