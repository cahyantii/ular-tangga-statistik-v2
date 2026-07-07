<?php

namespace App\Services\Game\TurnDecider;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use LogicException;

/**
 * Pemain manusia tidak pernah di-auto-play — dadu/jawaban selalu datang dari
 * request HTTP pemain itu sendiri (GameController), bukan dari orkestrator.
 */
class HumanTurnDecider implements TurnDeciderInterface
{
    public function shouldAutoPlay(GamePlayer $gamePlayer): bool
    {
        return false;
    }

    public function decideAnswer(GameSession $gameSession, GamePlayer $gamePlayer, Soal $soal): ?string
    {
        throw new LogicException('Pemain manusia menjawab lewat request HTTP, bukan lewat TurnDecider.');
    }
}
