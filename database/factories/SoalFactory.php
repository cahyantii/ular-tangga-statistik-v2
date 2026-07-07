<?php

namespace Database\Factories;

use App\Models\KategoriMateri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Soal>
 */
class SoalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kategori_id' => KategoriMateri::factory(),
            'pertanyaan' => fake()->sentence(8) . '?',
            'opsi_jawaban' => [
                'A' => fake()->words(3, true),
                'B' => fake()->words(3, true),
                'C' => fake()->words(3, true),
                'D' => fake()->words(3, true),
            ],
            'kunci_jawaban' => 'A',
            'pembahasan' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
