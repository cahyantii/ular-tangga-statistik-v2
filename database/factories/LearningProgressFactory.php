<?php

namespace Database\Factories;

use App\Models\KategoriMateri;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\LearningProgress>
 */
class LearningProgressFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(5, 50);
        $benar = fake()->numberBetween(0, $total);

        return [
            'user_id' => User::factory(),
            'kategori_id' => KategoriMateri::factory(),
            'total_dijawab' => $total,
            'total_benar' => $benar,
            'total_salah' => $total - $benar,
            'accuracy' => $total > 0 ? round($benar / $total * 100, 2) : 0,
            'last_played_at' => now(),
        ];
    }
}
