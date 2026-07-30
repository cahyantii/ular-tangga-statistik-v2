<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_duel_id
 * @property int $soal_id
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GameDuel $duel
 * @property-read \App\Models\Soal|null $soal
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereGameDuelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereSoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
