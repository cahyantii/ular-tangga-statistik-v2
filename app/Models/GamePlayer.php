<?php

namespace App\Models;

use App\Enums\PlayerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayer extends Model
{
    use HasFactory;

    protected $table = 'game_players';

    protected $fillable = [
        'game_session_id',
        'user_id',
        'is_robot',
        'turn_order',
        'pawn_color',
        'pawn_icon',
        'posisi_pion',
        'skor',
        'accuracy',
        'status',
        'last_heartbeat_at',
    ];

    protected function casts(): array
    {
        return [
            'is_robot' => 'boolean',
            'turn_order' => 'integer',
            'posisi_pion' => 'integer',
            'skor' => 'integer',
            'accuracy' => 'decimal:2',
            'status' => PlayerStatus::class,
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    /**
     * withTrashed(): user bisa saja di-soft-delete belakangan, riwayat pertandingan
     * (game_players) harus tetap bisa resolve namanya untuk keperluan riwayat/statistik.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
}
