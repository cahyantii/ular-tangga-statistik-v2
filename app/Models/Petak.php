<?php

namespace App\Models;

use App\Enums\TileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Petak extends Model
{
    use HasFactory;

    protected $table = 'petak';

    protected $fillable = [
        'papan_id',
        'posisi',
        'jenis_petak',
        'is_active',
        'kategori_id',
        'label',
        'icon',
        'warna',
        'border_warna',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'posisi' => 'integer',
            'jenis_petak' => TileType::class,
            'is_active' => 'boolean',
        ];
    }

    public function papan(): BelongsTo
    {
        return $this->belongsTo(PapanPermainan::class, 'papan_id');
    }

    /**
     * Kategori materi hanya relevan untuk petak jenis "soal".
     * withTrashed() karena kategori bisa soft-deleted tapi petak lama tetap harus bisa resolve datanya.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMateri::class, 'kategori_id')->withTrashed();
    }
}
