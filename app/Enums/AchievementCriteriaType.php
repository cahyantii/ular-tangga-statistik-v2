<?php

namespace App\Enums;

enum AchievementCriteriaType: string
{
    case TotalMenang = 'total_menang';
    case AkurasiKeseluruhan = 'akurasi_keseluruhan';
    case TotalPermainan = 'total_permainan';
    case SelisihKemenanganTerbesar = 'selisih_kemenangan_terbesar';
    case TotalAngkaEnam = 'total_angka_enam';

    public function label(): string
    {
        return match ($this) {
            self::TotalMenang => 'Total Kemenangan',
            self::AkurasiKeseluruhan => 'Akurasi Keseluruhan (%)',
            self::TotalPermainan => 'Total Permainan',
            self::SelisihKemenanganTerbesar => 'Selisih Kemenangan Terjauh (langkah)',
            self::TotalAngkaEnam => 'Total Dadu Bernilai 6',
        };
    }
}
