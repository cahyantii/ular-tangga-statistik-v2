<?php

namespace Database\Factories;

use App\Enums\TileType;
use App\Models\PapanPermainan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Petak>
 */
class PetakFactory extends Factory
{
    public function definition(): array
    {
        return [
            'papan_id' => PapanPermainan::factory(),
            'posisi' => fake()->unique()->numberBetween(1, 1000),
            'jenis_petak' => TileType::Biasa,
            'is_active' => true,
            'kategori_id' => null,
            'label' => null,
            'icon' => null,
            'warna' => null,
            'border_warna' => null,
            'deskripsi' => null,
        ];
    }
}
