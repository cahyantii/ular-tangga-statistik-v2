<?php

namespace App\Enums;

enum WinReason: string
{
    case Finish = 'finish';
    case Forfeit = 'forfeit';

    public function label(): string
    {
        return match ($this) {
            self::Finish => 'Mencapai Finish',
            self::Forfeit => 'Menang WO (lawan terputus)',
        };
    }
}
