<?php

namespace App\Enums;

enum ConnectorType: string
{
    case Tangga = 'tangga';
    case Ular = 'ular';

    public function label(): string
    {
        return match ($this) {
            self::Tangga => 'Tangga',
            self::Ular => 'Ular',
        };
    }
}
