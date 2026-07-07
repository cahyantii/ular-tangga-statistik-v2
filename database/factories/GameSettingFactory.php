<?php

namespace Database\Factories;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\GameSetting>
 */
class GameSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2, false),
            'value' => (string) fake()->numberBetween(1, 100),
            'type' => SettingType::Integer,
            'label' => ucfirst(fake()->words(3, true)),
            'deskripsi' => fake()->sentence(),
            'group' => 'Skor',
        ];
    }
}
