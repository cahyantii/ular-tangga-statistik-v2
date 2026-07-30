<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_session_id
 * @property int $soal_id
 * @property \Illuminate\Support\Carbon $used_at
 * @property-read \App\Models\GameSession $gameSession
 * @property-read \App\Models\Soal|null $soal
 * @method static \Database\Factories\GameQuestionUsedFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed whereGameSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed whereSoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameQuestionUsed whereUsedAt($value)
 * @mixin \Eloquent
 */
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
