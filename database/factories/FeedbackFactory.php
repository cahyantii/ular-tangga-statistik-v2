<?php

namespace Database\Factories;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => FeedbackType::Feedback,
            'subject' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'status' => FeedbackStatus::Baru,
        ];
    }
}
