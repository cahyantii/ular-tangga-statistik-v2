<?php

namespace App\Models;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\WinReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

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

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * withTrashed(): papan bisa saja di-soft-delete admin belakangan,
     * riwayat sesi lama tetap harus bisa resolve datanya.
     */
    public function papan(): BelongsTo
    {
        return $this->belongsTo(PapanPermainan::class, 'papan_id')->withTrashed();
    }

    /**
     * withTrashed(): soal aktif bisa saja di-soft-delete admin belakangan.
     */
    public function activeQuestion(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'active_question_id')->withTrashed();
    }

    public function currentTurnPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'current_turn_game_player_id');
    }

    public function winnerPlayer(): BelongsTo
    {
        return $this->belongsTo(GamePlayer::class, 'winner_game_player_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_session_id')->orderBy('turn_order');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GameLog::class, 'game_session_id');
    }

    public function questionsUsed(): HasMany
    {
        return $this->hasMany(GameQuestionUsed::class, 'game_session_id');
    }
}
