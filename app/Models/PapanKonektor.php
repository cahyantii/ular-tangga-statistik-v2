<?php

namespace App\Models;

use App\Enums\ConnectorType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
