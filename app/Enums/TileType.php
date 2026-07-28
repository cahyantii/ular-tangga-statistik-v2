<?php

namespace App\Enums;

enum TileType: string
{
    case Start = 'start';
    case Finish = 'finish';
    case Biasa = 'biasa';
    case Tangga = 'tangga';
    case Ular = 'ular';
    case Mystery = 'mystery';

    public function label(): string
    {
        return match ($this) {
            self::Start => 'Start',
            self::Finish => 'Finish',
            self::Biasa => 'Petak Biasa',
            self::Tangga => 'Tangga',
            self::Ular => 'Ular',
            self::Mystery => 'Mystery',
        };
    }
}
