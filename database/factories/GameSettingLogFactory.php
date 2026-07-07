<?php

namespace Database\Factories;

use App\Models\GameSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GameSettingLog>
 */
class GameSettingLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'game_setting_id' => GameSetting::factory(),
            'changed_by' => User::factory(),
            'old_value' => (string) fake()->numberBetween(1, 50),
            'new_value' => (string) fake()->numberBetween(1, 50),
            'created_at' => now(),
        ];
    }
}
