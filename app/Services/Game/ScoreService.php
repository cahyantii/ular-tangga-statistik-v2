<?php

namespace App\Services\Game;

use App\Enums\ScoreEventType;
use App\Models\GamePlayer;
use App\Repositories\Game\GameSettingsRepository;

/**
 * Semua nilai skor berasal dari `game_settings` (Tahap 2/5, keputusan final:
 * "no hardcoding"). `wrong_answer_penalty`/`tile_penalty_point` disimpan
 * sebagai nilai positif di game_settings dan dikurangkan di sini.
 */
class ScoreService
{
    public function __construct(private readonly GameSettingsRepository $settings)
    {
    }

    public function apply(GamePlayer $gamePlayer, ScoreEventType $eventType): int
    {
        $delta = match ($eventType) {
            ScoreEventType::CorrectAnswer => $this->settings->getInt('correct_answer_point'),
            ScoreEventType::WrongAnswer => -$this->settings->getInt('wrong_answer_penalty'),
            ScoreEventType::Win => $this->settings->getInt('win_point'),
        };

        $gamePlayer->skor += $delta;
        $gamePlayer->save();

        return $delta;
    }

    public function applyRankBonus(GamePlayer $gamePlayer, int $rank): int
    {
        $delta = match ($rank) {
            1 => 100,
            2 => 50,
            3 => 25,
            default => 0,
        };

        if ($delta > 0) {
            $gamePlayer->skor += $delta;
            $gamePlayer->save();
        }

        return $delta;
    }
}
