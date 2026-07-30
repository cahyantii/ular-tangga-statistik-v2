<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_setting_id
 * @property int|null $changed_by
 * @property string|null $old_value
 * @property string $new_value
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\User|null $changedBy
 * @property-read \App\Models\GameSetting $gameSetting
 * @method static \Database\Factories\GameSettingLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereGameSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSettingLog whereOldValue($value)
 * @mixin \Eloquent
 */
class GameSettingLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'game_setting_logs';

    protected $fillable = [
        'game_setting_id',
        'changed_by',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function gameSetting(): BelongsTo
    {
        return $this->belongsTo(GameSetting::class, 'game_setting_id');
    }

    /**
     * withTrashed() karena admin yang mengubah setting bisa saja sudah dihapus (soft delete) belakangan.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by')->withTrashed();
    }
}
