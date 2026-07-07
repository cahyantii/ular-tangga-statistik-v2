<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\Soal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GameQuestionUsed>
 */
class GameQuestionUsedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'soal_id' => Soal::factory(),
            'used_at' => now(),
        ];
    }
}
