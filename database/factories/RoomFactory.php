<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Enums\RoomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode_room' => strtoupper(Str::random(6)),
            'tipe' => RoomType::Private,
            'status' => GameStatus::Waiting,
            'created_by' => User::factory(),
            'expires_at' => now()->addMinutes(5),
        ];
    }

    public function quickMatch(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipe' => RoomType::QuickMatch,
            'kode_room' => null,
        ]);
    }
}
