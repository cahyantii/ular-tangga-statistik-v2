<?php

namespace App\Models;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\WinReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $room_id
 * @property int $papan_id
 * @property GameMode $mode
 * @property GameStatus $status
 * @property int|null $current_turn_game_player_id
 * @property int|null $active_question_id
 * @property \Illuminate\Support\Carbon|null $active_question_expires_at
 * @property WinReason|null $win_reason
 * @property int|null $winner_game_player_id
 * @property int $version
 * @property string|null $random_seed
 * @property int|null $duration_seconds
 * @property int $total_turn
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Soal|null $activeQuestion
 * @property-read \App\Models\GamePlayer|null $currentTurnPlayer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\PapanPermainan|null $papan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GamePlayer> $players
 * @property-read int|null $players_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameQuestionUsed> $questionsUsed
 * @property-read int|null $questions_used_count
 * @property-read \App\Models\Room|null $room
 * @property-read \App\Models\GamePlayer|null $winnerPlayer
 * @method static \Database\Factories\GameSessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereActiveQuestionExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereActiveQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereCurrentTurnGamePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession wherePapanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereRandomSeed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereRoomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereTotalTurn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereWinReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSession whereWinnerGamePlayerId($value)
 * @mixin \Eloquent
 */
class GameSession extends Model
{
    use HasFactory;

    protected $table = 'game_sessions';

    protected $fillable = [
        'room_id',
        'papan_id',
        'mode',
        'status',
        'current_turn_game_player_id',
        'active_question_id',
        'active_question_expires_at',
        'win_reason',
        'winner_game_player_id',
        'version',
        'random_seed',
        'duration_seconds',
        'total_turn',
        'started_at',
        'finished_at',
        'uuid',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'mode' => GameMode::class,
            'status' => GameStatus::class,
            'win_reason' => WinReason::class,
            'active_question_expires_at' => 'datetime',
            'version' => 'integer',
            'duration_seconds' => 'integer',
            'total_turn' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * withTrashed(): papan bisa saja di-soft-delete admin belakangan,
     * riwayat sesi lama tetap harus bisa resolve datanya.
     * @return BelongsTo<PapanPermainan, $this>
     */
    public function papan(): BelongsTo
    {
        return $this->belongsTo(PapanPermainan::class, 'papan_id')->withTrashed();
    }

    /**
     * withTrashed(): soal aktif bisa saja di-soft-delete admin belakangan.
     * @return BelongsTo<Soal, $this>
     */
    public function activeQuestion(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'active_question_id')->withTrashed();
    }

    /**
     * @return BelongsTo<GamePlayer, $this>
     */
    public function currentTurnPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'current_turn_game_player_id');
    }

    /**
     * @return BelongsTo<GamePlayer, $this>
     */
    public function winnerPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'winner_game_player_id');
    }

    /**
     * @return HasMany<GamePlayer, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_session_id')->orderBy('turn_order');
    }

    /**
     * @return HasMany<GameLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(GameLog::class, 'game_session_id');
    }

    /**
     * @return HasMany<GameQuestionUsed, $this>
     */
    public function questionsUsed(): HasMany
    {
        return $this->hasMany(GameQuestionUsed::class, 'game_session_id');
    }
}
