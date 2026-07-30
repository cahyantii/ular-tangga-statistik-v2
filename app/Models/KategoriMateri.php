<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nama
 * @property string $slug
 * @property string|null $icon
 * @property int $urutan
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LearningProgress> $learningProgress
 * @property-read int|null $learning_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Materi> $materi
 * @property-read int|null $materi_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Petak> $petak
 * @property-read int|null $petak_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Soal> $soal
 * @property-read int|null $soal_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri active()
 * @method static \Database\Factories\KategoriMateriFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri whereUrutan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KategoriMateri withoutTrashed()
 * @mixin \Eloquent
 */
class KategoriMateri extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kategori_materi';

    protected $fillable = [
        'nama',
        'slug',
        'icon',
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

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class, 'kategori_id');
    }

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'kategori_id');
    }

    public function petak(): HasMany
    {
        return $this->hasMany(Petak::class, 'kategori_id');
    }

    public function learningProgress(): HasMany
    {
        return $this->hasMany(LearningProgress::class, 'kategori_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
