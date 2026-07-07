<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
