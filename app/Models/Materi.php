<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $kategori_id
 * @property string $judul
 * @property string $konten
 * @property int $urutan
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\KategoriMateri|null $kategori
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi active()
 * @method static \Database\Factories\MateriFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereKonten($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi whereUrutan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Materi withoutTrashed()
 * @mixin \Eloquent
 */
class Materi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materi';

    protected $fillable = [
        'kategori_id',
        'judul',
        'konten',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMateri::class, 'kategori_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
