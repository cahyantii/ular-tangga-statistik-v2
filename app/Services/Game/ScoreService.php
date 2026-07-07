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
            ScoreEventType::Bonus => $this->settings->getInt('bonus_point'),
            ScoreEventType::TilePenalty => -$this->settings->getInt('tile_penalty_point'),
            ScoreEventType::Win => $this->settings->getInt('win_point'),
        };

        $gamePlayer->skor += $delta;
        $gamePlayer->save();

        return $delta;
    }
}
