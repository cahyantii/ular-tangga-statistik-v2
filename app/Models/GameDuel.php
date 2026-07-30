<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $game_session_id
 * @property int $challenger_id
 * @property int $opponent_id
 * @property string $status
 * @property int|null $winner_id
 * @property int|null $loser_id
 * @property int|null $loser_penalty_roll
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameDuelAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\GamePlayer $challenger
 * @property-read \App\Models\GameSession $gameSession
 * @property-read \App\Models\GamePlayer|null $loser
 * @property-read \App\Models\GamePlayer $opponent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameDuelQuestion> $questions
 * @property-read int|null $questions_count
 * @property-read \App\Models\GamePlayer|null $winner
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereChallengerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereGameSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereLoserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereLoserPenaltyRoll($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereOpponentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameDuel whereWinnerId($value)
 * @mixin \Eloquent
 */
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
