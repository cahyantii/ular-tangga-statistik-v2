<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Enums\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'kode_room',
        'tipe',
        'status',
        'created_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => RoomType::class,
            'status' => GameStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function gameSession(): HasOne
    {
        return $this->hasOne(GameSession::class, 'room_id');
    }
}
