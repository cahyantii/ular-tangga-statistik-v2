<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameDuelQuestion extends Model
{
    protected $guarded = ['id'];

    public function duel(): BelongsTo
    {
        return $this->belongsTo(GameDuel::class, 'game_duel_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
