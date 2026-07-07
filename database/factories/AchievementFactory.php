<?php

namespace Database\Factories;

use App\Enums\AchievementCriteriaType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => strtoupper(fake()->unique()->lexify('ACH_????')),
            'nama' => ucfirst(fake()->words(3, true)),
            'deskripsi' => fake()->sentence(),
            'icon' => null,
            'warna_badge' => null,
            'syarat_type' => AchievementCriteriaType::TotalMenang,
            'syarat_value' => fake()->numberBetween(1, 20),
            'urutan' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
