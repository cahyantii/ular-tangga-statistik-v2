<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $kategori_id
 * @property string $pertanyaan
 * @property array<array-key, mixed> $opsi_jawaban
 * @property string $kunci_jawaban
 * @property string $pembahasan
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameQuestionUsed> $gameQuestionsUsed
 * @property-read int|null $game_questions_used_count
 * @property-read \App\Models\KategoriMateri|null $kategori
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal active()
 * @method static \Database\Factories\SoalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereKunciJawaban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereOpsiJawaban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal wherePembahasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal wherePertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Soal withoutTrashed()
 * @mixin \Eloquent
 */
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
