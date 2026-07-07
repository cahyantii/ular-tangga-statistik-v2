<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
