<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PapanPermainan>
 */
class PapanPermainanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => 'Papan ' . fake()->unique()->words(2, true),
            'jumlah_petak' => 50,
            'jumlah_kolom' => 10,
            'thumbnail' => null,
            'is_active' => true,
        ];
    }
}
