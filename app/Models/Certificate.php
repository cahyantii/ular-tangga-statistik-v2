<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $jenis_sertifikat
 * @property string $judul
 * @property string $nomor_sertifikat
 * @property string $verification_code
 * @property string|null $template_path
 * @property string $file_path
 * @property \Illuminate\Support\Carbon $issued_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CertificateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereJenisSertifikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereJudul($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereNomorSertifikat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereTemplatePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Certificate whereVerificationCode($value)
 * @mixin \Eloquent
 */
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
