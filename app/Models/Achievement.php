<?php

namespace App\Models;

use App\Enums\AchievementCriteriaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'syarat_type' => AchievementCriteriaType::class,
            'syarat_value' => 'integer',
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
