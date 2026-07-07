<?php

namespace Database\Factories;

use App\Enums\GameLogEventType;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GameLog>
 */
class GameLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'user_id' => User::factory(),
            'event_type' => GameLogEventType::DiceRolled,
            'turn_number' => fake()->numberBetween(1, 20),
            'payload' => ['value' => fake()->numberBetween(1, 6)],
            'created_at' => now(),
        ];
    }
}
