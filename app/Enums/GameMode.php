<?php

namespace App\Enums;

enum GameMode: string
{
    case VsRobot = 'vs_robot';
    case Multiplayer = 'multiplayer';

    public function label(): string
    {
        return match ($this) {
            self::VsRobot => 'Vs Robot',
            self::Multiplayer => 'Multiplayer',
        };
    }
}
