<?php

namespace App\Enums;

enum PlayerStatus: string
{
    case Active = 'active';
    case Disconnected = 'disconnected';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Disconnected => 'Terputus',
        };
    }
}
