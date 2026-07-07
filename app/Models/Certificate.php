<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'user_id',
        'jenis_sertifikat',
        'judul',
        'nomor_sertifikat',
        'verification_code',
        'template_path',
        'file_path',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    /**
     * withTrashed(): user bisa saja di-soft-delete belakangan, sertifikat yang
     * sudah terbit (dan bisa jadi sudah dicetak/dibagikan) tetap harus bisa
     * menampilkan nama pemiliknya di halaman verifikasi publik.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
