<?php

namespace App\Enums;

enum PlayerStatus: string
{
    case Active = 'active';
    case Disconnected = 'disconnected';
    /**
     * Keluar PERMANEN (voluntary leave, atau disconnect yang timeout tanpa
     * reconnect) - beda dari Disconnected yang masih bisa reconnect. Dipakai
     * supaya game 3-6 pemain bisa lanjut tanpa pemain ini (di-skip permanen
     * dari rotasi giliran, lihat TurnService::nextPlayer()) tanpa mengakhiri
     * sesi untuk pemain lain yang masih aktif.
     */
    case Forfeited = 'forfeited';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Disconnected => 'Terputus',
            self::Forfeited => 'Keluar',
        };
    }
}
