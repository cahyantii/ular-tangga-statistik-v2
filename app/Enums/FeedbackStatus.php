<?php

namespace App\Enums;

enum FeedbackStatus: string
{
    case Baru = 'baru';
    case Dibaca = 'dibaca';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Dibaca => 'Dibaca',
            self::Selesai => 'Selesai',
        };
    }
}
