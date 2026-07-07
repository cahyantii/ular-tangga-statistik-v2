<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Soal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'soal';

    protected $fillable = [
        'kategori_id',
        'pertanyaan',
        'opsi_jawaban',
        'kunci_jawaban',
        'pembahasan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opsi_jawaban' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMateri::class, 'kategori_id');
    }

    public function gameQuestionsUsed(): HasMany
    {
        return $this->hasMany(GameQuestionUsed::class, 'soal_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
