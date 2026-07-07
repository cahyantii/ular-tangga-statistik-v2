<?php

namespace App\Enums;

enum AchievementCriteriaType: string
{
    case TotalMenang = 'total_menang';
    case AkurasiKeseluruhan = 'akurasi_keseluruhan';
    case TotalPermainan = 'total_permainan';

    public function label(): string
    {
        return match ($this) {
            self::TotalMenang => 'Total Kemenangan',
            self::AkurasiKeseluruhan => 'Akurasi Keseluruhan (%)',
            self::TotalPermainan => 'Total Permainan',
        };
    }
}
