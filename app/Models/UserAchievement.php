<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
