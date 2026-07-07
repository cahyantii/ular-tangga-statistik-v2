<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
