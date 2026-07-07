<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameQuestionUsed extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'game_questions_used';

    protected $fillable = [
        'game_session_id',
        'soal_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    /**
     * withTrashed(): soal bisa saja di-soft-delete admin setelah dipakai dalam sesi.
     */
    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'soal_id')->withTrashed();
    }
}
