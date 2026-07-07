<?php

namespace App\Services\Game\TurnDecider;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;

/**
 * Strategy Pattern (Tahap 11, anticipated sejak Tahap 5): membedakan perilaku
 * pemain manusia (aksi datang dari request HTTP) dan robot (aksi diputuskan
 * server secara sinkron, di transaksi yang sama). GameSessionService memilih
 * implementasi lewat is_robot, tidak pernah bercabang logic in-line.
 */
interface TurnDeciderInterface
{
    public function shouldAutoPlay(GamePlayer $gamePlayer): bool;

    public function decideAnswer(GameSession $gameSession, GamePlayer $gamePlayer, Soal $soal): ?string;
}
