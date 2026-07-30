<?php

namespace App\Models;

use App\Enums\AchievementCriteriaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property string|null $deskripsi
 * @property string|null $icon
 * @property string|null $warna_badge
 * @property AchievementCriteriaType $syarat_type
 * @property int $syarat_value
 * @property int $reward_poin
 * @property int $urutan
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserAchievement> $userAchievements
 * @property-read int|null $user_achievements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement active()
 * @method static \Database\Factories\AchievementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereRewardPoin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereSyaratType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereSyaratValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereUrutan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement whereWarnaBadge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Achievement withoutTrashed()
 * @mixin \Eloquent
 */
class Achievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'achievements';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'icon',
        'warna_badge',
        'syarat_type',
        'syarat_value',
        'reward_poin',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'syarat_type' => AchievementCriteriaType::class,
            'syarat_value' => 'integer',
            'reward_poin' => 'integer',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('earned_at');
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class, 'achievement_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
