<?php

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \App\Enums\SettingType $type
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $label
 * @property string|null $deskripsi
 * @property string|null $group
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameSettingLog> $logs
 * @property-read int|null $logs_count
 * @method static \Database\Factories\GameSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSetting whereValue($value)
 * @mixin \Eloquent
 */
class GameSetting extends Model
{
    use HasFactory;

    protected $table = 'game_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'label',
        'deskripsi',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GameSettingLog::class, 'game_setting_id');
    }
}
