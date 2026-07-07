<?php

namespace App\Services\Game;

use App\Models\GamePlayer;
use App\Models\GameSession;

/**
 * Pergantian giliran round-robin berdasarkan `turn_order`. Mengasumsikan
 * `current_turn_game_player_id` sudah diisi pemain pertama saat sesi dibuat
 * (tanggung jawab GameSessionService, bukan service ini).
 */
class TurnService
{
    public function nextPlayer(GameSession $gameSession): GamePlayer
    {
        $players = $gameSession->players()->get();
        $currentIndex = $players->search(fn (GamePlayer $p) => $p->id === $gameSession->current_turn_game_player_id);
        $nextIndex = ($currentIndex + 1) % $players->count();

        return $players[$nextIndex];
    }

    public function advance(GameSession $gameSession): GamePlayer
    {
        $next = $this->nextPlayer($gameSession);

        $gameSession->current_turn_game_player_id = $next->id;
        $gameSession->total_turn += 1;
        $gameSession->save();

        return $next;
    }
}
