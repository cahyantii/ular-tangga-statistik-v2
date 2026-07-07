<?php

namespace App\Enums;

enum GameStatus: string
{
    case Waiting = 'waiting';
    case Playing = 'playing';
    case Paused = 'paused';
    case Finished = 'finished';
    case Abandoned = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Menunggu',
            self::Playing => 'Berlangsung',
            self::Paused => 'Dijeda',
            self::Finished => 'Selesai',
            self::Abandoned => 'Dibatalkan',
        };
    }
}
