<?php

namespace App\Models;

use App\Enums\ConnectorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $papan_id
 * @property ConnectorType $jenis
 * @property int $posisi_awal
 * @property int $posisi_akhir
 * @property string|null $label
 * @property string|null $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PapanPermainan|null $papan
 * @method static \Database\Factories\PapanKonektorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor wherePapanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor wherePosisiAkhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor wherePosisiAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanKonektor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PapanKonektor extends Model
{
    use HasFactory;

    protected $table = 'papan_konektor';

    protected $fillable = [
        'papan_id',
        'jenis',
        'posisi_awal',
        'posisi_akhir',
        'label',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => ConnectorType::class,
            'posisi_awal' => 'integer',
            'posisi_akhir' => 'integer',
        ];
    }

    public function papan(): BelongsTo
    {
        return $this->belongsTo(PapanPermainan::class, 'papan_id');
    }
}
