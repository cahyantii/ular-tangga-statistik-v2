<?php

namespace App\Models;

use App\Enums\GameLogEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_session_id
 * @property int|null $user_id
 * @property GameLogEventType $event_type
 * @property int|null $turn_number
 * @property array<array-key, mixed> $payload
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\GameSession $gameSession
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\GameLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereEventType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereGameSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereTurnNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameLog whereUserId($value)
 * @mixin \Eloquent
 */
class GameLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'game_logs';

    protected $fillable = [
        'game_session_id',
        'user_id',
        'event_type',
        'turn_number',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => GameLogEventType::class,
            'turn_number' => 'integer',
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    /**
     * withTrashed(): user pelaku aksi bisa saja di-soft-delete belakangan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
