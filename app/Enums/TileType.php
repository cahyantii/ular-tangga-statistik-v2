<?php

namespace App\Enums;

enum TileType: string
{
    case Start = 'start';
    case Finish = 'finish';
    case Biasa = 'biasa';
    case Soal = 'soal';
    case Tangga = 'tangga';
    case Ular = 'ular';
    case Bonus = 'bonus';
    case Penalti = 'penalti';
    case Mystery = 'mystery';

    public function label(): string
    {
        return match ($this) {
            self::Start => 'Start',
            self::Finish => 'Finish',
            self::Biasa => 'Petak Biasa',
            self::Soal => 'Petak Soal',
            self::Tangga => 'Tangga',
            self::Ular => 'Ular',
            self::Bonus => 'Bonus',
            self::Penalti => 'Penalti',
            self::Mystery => 'Mystery',
        };
    }
}
