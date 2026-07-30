<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $achievement_id
 * @property \Illuminate\Support\Carbon $earned_at
 * @property-read \App\Models\Achievement|null $achievement
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\UserAchievementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereAchievementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereEarnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAchievement whereUserId($value)
 * @mixin \Eloquent
 */
class UserAchievement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_achievements';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    /**
     * withTrashed(): user bisa saja di-soft-delete belakangan, riwayat
     * achievement yang pernah diraih tetap harus bisa resolve datanya.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * withTrashed(): achievement bisa saja di-soft-delete admin belakangan,
     * riwayat achievement yang sudah diraih pemain tetap harus tampil.
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class)->withTrashed();
    }
}
