<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nama
 * @property string|null $deskripsi
 * @property int $jumlah_petak
 * @property int $jumlah_kolom
 * @property string|null $thumbnail
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameSession> $gameSessions
 * @property-read int|null $game_sessions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PapanKonektor> $papanKonektor
 * @property-read int|null $papan_konektor_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Petak> $petak
 * @property-read int|null $petak_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan active()
 * @method static \Database\Factories\PapanPermainanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereJumlahKolom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereJumlahPetak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PapanPermainan withoutTrashed()
 * @mixin \Eloquent
 */
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
