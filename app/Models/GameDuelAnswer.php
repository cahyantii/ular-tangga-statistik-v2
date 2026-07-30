<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_duel_id
 * @property int $game_player_id
 * @property int $soal_id
 * @property bool $is_correct
 * @property int|null $time_taken_ms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GameDuel $duel
 * @property-read \App\Models\GamePlayer $player
 * @property-read \App\Models\Soal|null $soal
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereGameDuelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereGamePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereSoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereTimeTakenMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuelAnswer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GameDuelAnswer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function duel(): BelongsTo
    {
        return $this->belongsTo(GameDuel::class, 'game_duel_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
