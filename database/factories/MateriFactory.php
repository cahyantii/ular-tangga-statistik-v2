<?php

namespace Database\Factories;

use App\Models\KategoriMateri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Materi>
 */
class MateriFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kategori_id' => KategoriMateri::factory(),
            'judul' => ucfirst(fake()->sentence(4)),
            'konten' => fake()->paragraphs(3, true),
            'urutan' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
