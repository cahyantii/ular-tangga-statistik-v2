<?php

namespace App\Services\Game;

use App\Models\GamePlayer;
use App\Models\GameSession;

class PowerUpService
{
    /**
     * Dapatkan daftar semua power up yang tersedia beserta propertinya
     */
    public static function getAvailablePowerUps(): array
    {
        return [
            'teleport_forward' => [
                'id' => 'teleport_forward',
                'name' => 'Teleport Maju',
                'description' => 'Maju 3 petak.',
                'type' => 'immediate',
                'icon' => '🚀',
            ],
            'whirlwind' => [
                'id' => 'whirlwind',
                'name' => 'Angin Puyuh',
                'description' => 'Mundur 3 petak.',
                'type' => 'immediate',
                'icon' => '🌪️',
            ],
            'double_dice' => [
                'id' => 'double_dice',
                'name' => 'Dadu Ganda',
                'description' => 'Giliran berikutnya, hasil lemparan dadu Anda akan dikali 2!',
                'type' => 'immediate',
                'icon' => '🎲🎲',
            ],
        ];
    }
}
