<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $kategori_id
 * @property int $total_dijawab
 * @property int $total_benar
 * @property int $total_salah
 * @property numeric $accuracy
 * @property \Illuminate\Support\Carbon|null $last_played_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KategoriMateri|null $kategori
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\LearningProgressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereAccuracy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereLastPlayedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereTotalBenar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereTotalDijawab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereTotalSalah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LearningProgress whereUserId($value)
 * @mixin \Eloquent
 */
class LearningProgress extends Model
{
    use HasFactory;

    protected $table = 'learning_progress';

    protected $fillable = [
        'user_id',
        'kategori_id',
        'total_dijawab',
        'total_benar',
        'total_salah',
        'accuracy',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'total_dijawab' => 'integer',
            'total_benar' => 'integer',
            'total_salah' => 'integer',
            'accuracy' => 'decimal:2',
            'last_played_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * withTrashed(): kategori bisa saja di-soft-delete admin, progress historis
     * pemain tetap harus bisa menampilkan nama kategori tersebut.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMateri::class, 'kategori_id')->withTrashed();
    }
}
