<?php

namespace App\Enums;

/**
 * Palet warna pion yang boleh dipilih pemain (Vs Robot, lihat
 * RobotSessionController) — nilai enum LANGSUNG kode hex karena
 * GamePlayer::pawn_color dipakai apa adanya sebagai CSS background-color
 * di frontend (lihat resources/js/game-play.js pawnColorStyle()), bukan
 * nama warna yang perlu di-lookup lagi.
 */
enum PawnColor: string
{
    case Biru = '#1d4ed8';
    case Merah = '#e11d48';
    case Hijau = '#059669';
    case Kuning = '#ca8a04';
    case Ungu = '#7c3aed';
    case Pink = '#db2777';

    public function label(): string
    {
        return match ($this) {
            self::Biru => 'Biru',
            self::Merah => 'Merah',
            self::Hijau => 'Hijau',
            self::Kuning => 'Kuning',
            self::Ungu => 'Ungu',
            self::Pink => 'Pink',
        };
    }
}
