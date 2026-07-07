<?php

namespace App\Actions;

use App\Models\Room;
use Illuminate\Support\Str;

class GenerateRoomCode
{
    /**
     * Generate kode room privat yang unik (6 karakter alfanumerik kapital).
     */
    public function __invoke(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (Room::where('kode_room', $code)->exists());

        return $code;
    }
}
