<?php

namespace App\Models;

use App\Enums\PlayerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_session_id
 * @property int|null $user_id
 * @property bool $is_robot
 * @property int $turn_order
 * @property string $pawn_color
 * @property string|null $pawn_icon
 * @property int $posisi_pion
 * @property int $skor
 * @property numeric|null $accuracy
 * @property PlayerStatus $status
 * @property \Illuminate\Support\Carbon|null $last_heartbeat_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $active_buffs
 * @property-read string $nama
 * @property-read \App\Models\GameSession $gameSession
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\GamePlayerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereAccuracy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereActiveBuffs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereGameSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereIsRobot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereLastHeartbeatAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer wherePawnColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer wherePawnIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer wherePosisiPion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereSkor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereTurnOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GamePlayer whereUserId($value)
 * @mixin \Eloquent
 */
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
        'finish_rank',
        'finished_at_turn',
        'skor',
        'accuracy',
        'status',
        'last_heartbeat_at',
        'active_buffs',
    ];

    protected function casts(): array
    {
        return [
            'is_robot' => 'boolean',
            'turn_order' => 'integer',
            'posisi_pion' => 'integer',
            'finish_rank' => 'integer',
            'finished_at_turn' => 'integer',
            'skor' => 'integer',
            'accuracy' => 'decimal:2',
            'status' => PlayerStatus::class,
            'last_heartbeat_at' => 'datetime',
            'active_buffs' => 'array',
        ];
    }

    /**
     * @return BelongsTo<GameSession, $this>
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    /**
     * withTrashed(): user bisa saja di-soft-delete belakangan, riwayat pertandingan
     * (game_players) harus tetap bisa resolve namanya untuk keperluan riwayat/statistik.
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute<string, never>
     */
    protected function nama(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(
            fn () => $this->is_robot ? 'Robot' : ($this->user ? $this->user->name : 'Unknown')
        );
    }
}
