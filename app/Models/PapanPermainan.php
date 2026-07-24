<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PapanPermainan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'papan_permainan';

    protected $fillable = [
        'nama',
        'deskripsi',
        'jumlah_petak',
        'jumlah_kolom',
        'thumbnail',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_petak' => 'integer',
            'jumlah_kolom' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function petak(): HasMany
    {
        return $this->hasMany(Petak::class, 'papan_id')->orderBy('posisi');
    }

    public function papanKonektor(): HasMany
    {
        return $this->hasMany(PapanKonektor::class, 'papan_id');
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'papan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
