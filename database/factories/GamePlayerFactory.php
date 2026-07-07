<?php

namespace Database\Factories;

use App\Enums\PlayerStatus;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GamePlayer>
 */
class GamePlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_session_id' => GameSession::factory(),
            'user_id' => User::factory(),
            'is_robot' => false,
            'turn_order' => 1,
            'pawn_color' => fake()->randomElement(['red', 'blue', 'green', 'yellow']),
            'pawn_icon' => null,
            'posisi_pion' => 0,
            'skor' => 0,
            'accuracy' => null,
            'status' => PlayerStatus::Active,
            'last_heartbeat_at' => now(),
        ];
    }

    public function robot(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_robot' => true,
            'turn_order' => 2,
            'pawn_color' => 'gray',
            'last_heartbeat_at' => null,
        ]);
    }
}
