<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameDuel extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'challenger_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'opponent_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'winner_id');
    }

    public function loser(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'loser_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(GameDuelQuestion::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameDuelAnswer::class);
    }
}
