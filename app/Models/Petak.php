<?php

namespace App\Models;

use App\Enums\TileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $papan_id
 * @property int $posisi
 * @property TileType $jenis_petak
 * @property bool $is_active
 * @property int|null $kategori_id
 * @property string|null $label
 * @property string|null $icon
 * @property string|null $warna
 * @property string|null $border_warna
 * @property string|null $deskripsi
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\KategoriMateri|null $kategori
 * @property-read \App\Models\PapanPermainan|null $papan
 * @method static \Database\Factories\PetakFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereBorderWarna($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereJenisPetak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak wherePapanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak wherePosisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Petak whereWarna($value)
 * @mixin \Eloquent
 */
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
