<?php

namespace App\Services\Game;

use App\Enums\PlayerStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;

/**
 * Pergantian giliran round-robin berdasarkan `turn_order`. Mengasumsikan
 * `current_turn_game_player_id` sudah diisi pemain pertama saat sesi dibuat
 * (tanggung jawab GameSessionService, bukan service ini).
 *
 * Untuk game 3-6 pemain: pemain berstatus Disconnected/Forfeited DI-SKIP
 * saat mencari giliran berikutnya (bukan cuma modulo polos) - inilah
 * mekanisme "lanjut tanpa dia" (lihat GameSessionService::pauseForDisconnect()
 * dkk). Kalau ternyata TIDAK ADA pemain Active sama sekali (seharusnya tidak
 * pernah terjadi - GameSessionService sudah men-finish sesi begitu pemain
 * aktif tersisa <= 1), fallback ke modulo polos supaya tidak infinite loop.
 */
class TurnService
{
    public function nextPlayer(GameSession $gameSession): GamePlayer
    {
        $players = $gameSession->players()->orderBy('turn_order')->get();
        $count = $players->count();
        $currentIndex = $players->search(fn (GamePlayer $p) => $p->id === $gameSession->current_turn_game_player_id);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;

        for ($i = 1; $i <= $count; $i++) {
            $candidate = $players[($currentIndex + $i) % $count];
            if ($candidate->status === PlayerStatus::Active) {
                return $candidate;
            }
        }

        return $players[($currentIndex + 1) % $count];
    }

    public function advance(GameSession $gameSession): GamePlayer
    {
        $next = $this->nextPlayer($gameSession);

        $gameSession->current_turn_game_player_id = $next->id;
        $gameSession->total_turn += 1;
        $gameSession->current_turn_started_at = now();
        $gameSession->save();

        return $next;
    }
}
