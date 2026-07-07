<?php

namespace Database\Factories;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Models\PapanPermainan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\GameSession>
 */
class GameSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => null,
            'papan_id' => PapanPermainan::factory(),
            'mode' => GameMode::VsRobot,
            'status' => GameStatus::Playing,
            'active_question_id' => null,
            'active_question_expires_at' => null,
            'win_reason' => null,
            'winner_game_player_id' => null,
            'version' => 1,
            'random_seed' => Str::random(16),
            'duration_seconds' => null,
            'total_turn' => 0,
            'started_at' => now(),
            'finished_at' => null,
        ];
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Finished,
            'finished_at' => now(),
            'duration_seconds' => fake()->numberBetween(120, 1800),
        ]);
    }
}
