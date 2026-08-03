<?php

namespace App\Events\Game;

use App\Enums\WinReason;
use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameFinished implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $pemenang,
        public readonly WinReason $winReason,
    ) {
    }

    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'winner_game_player_id' => $this->pemenang->id,
            'win_reason' => $this->winReason->value,
            'podium' => $this->gameSession->players()
                ->whereNotNull('finish_rank')
                ->orderBy('finish_rank')
                ->get()
                ->map(fn (GamePlayer $p) => [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'rank' => $p->finish_rank,
                    'skor' => $p->skor,
                    'pawn_color' => $p->pawn_color,
                ])->values()->toArray(),
        ];
    }
}
