<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Enums\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string|null $kode_room
 * @property RoomType $tipe
 * @property int $jumlah_pemain
 * @property GameStatus $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\GameSession|null $gameSession
 * @method static \Database\Factories\RoomFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereJumlahPemain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereKodeRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereTipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Room whereUuid($value)
 * @mixin \Eloquent
 */
class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'kode_room',
        'tipe',
        'jumlah_pemain',
        'status',
        'created_by',
        'expires_at',
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
            'tipe' => RoomType::class,
            'status' => GameStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * @return HasOne<GameSession, $this>
     */
    public function gameSession(): HasOne
    {
        return $this->hasOne(GameSession::class, 'room_id');
    }
}
