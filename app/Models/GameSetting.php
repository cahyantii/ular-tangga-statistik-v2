<?php

namespace App\Models;

use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
