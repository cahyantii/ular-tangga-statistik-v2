<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\KategoriMateri>
 */
class KategoriMateriFactory extends Factory
{
    public function definition(): array
    {
        $nama = fake()->unique()->words(2, true);

        return [
            'nama' => ucwords($nama),
            'slug' => Str::slug($nama),
            'icon' => null,
            'urutan' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
