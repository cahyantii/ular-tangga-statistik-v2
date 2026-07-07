<?php

namespace App\Models;

use App\Enums\GameLogEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
