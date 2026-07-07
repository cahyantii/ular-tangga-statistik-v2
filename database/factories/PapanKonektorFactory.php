<?php

namespace Database\Factories;

use App\Enums\ConnectorType;
use App\Models\PapanPermainan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PapanKonektor>
 */
class PapanKonektorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'papan_id' => PapanPermainan::factory(),
            'jenis' => ConnectorType::Tangga,
            'posisi_awal' => fake()->numberBetween(2, 20),
            'posisi_akhir' => fake()->numberBetween(21, 40),
            'label' => null,
            'icon' => null,
        ];
    }

    public function ular(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis' => ConnectorType::Ular,
            'posisi_awal' => fake()->numberBetween(21, 40),
            'posisi_akhir' => fake()->numberBetween(2, 20),
        ]);
    }
}
