<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Mail\ResetPasswordMail;
use App\Mail\VerifyEmailMail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Kunci avatar bawaan (Tahap Profil) — nilai kolom `avatar` yang BUKAN
     * salah satu dari daftar ini dianggap path file di disk `public`
     * (hasil unggahan sendiri), lihat accessor avatarUrl() di bawah.
     *
     * @var list<string>
     */
    public const PRESET_AVATARS = [
        'avatar1',
        'avatar2',
        'avatar3',
        'avatar4',
        'avatar5',
        'avatar6',
        'avatar7',
        'avatar8',
        'avatar9',
        'avatar10',
        'avatar11',
        'avatar12',
        'avatar13',
        'avatar14',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * Deliberately excludes "role" - role must never be settable via mass
     * assignment from user-facing input (see project auth decisions).
     * "email_verified_at" IS included on purpose (dibutuhkan agar
     * SocialAuthService bisa langsung memverifikasi akun OAuth baru saat
     * create()) - aman karena tidak ada controller yang mass-assign raw
     * request input ke User::create()/update(), semua eksplisit whitelist
     * per key (lihat RegisteredUserController, ProfileController).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'google_id',
        'github_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * URL avatar siap-pakai untuk `<img>` — null berarti UI harus jatuh
     * kembali ke inisial nama (lihat x-player.avatar).
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->avatar) {
                    return null;
                }

                // Akun OAuth (Google/GitHub) menyimpan URL avatar eksternal
                // penuh apa adanya, bukan path di disk "public" - lihat
                // SocialAuthService::findOrCreateUser().
                if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                    return $this->avatar;
                }

                if (in_array($this->avatar, self::PRESET_AVATARS, true)) {
                    return asset("images/avatars/{$this->avatar}.png");
                }

                return Storage::disk('public')->url($this->avatar);
            },
        );
    }

    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('earned_at');
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function learningProgress(): HasMany
    {
        return $this->hasMany(LearningProgress::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function roomsCreated(): HasMany
    {
        return $this->hasMany(Room::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Nama route dashboard tujuan setelah login/OAuth - satu sumber
     * kebenaran dipakai bersama oleh AuthenticatedSessionController &
     * SocialiteController supaya logika redirect-per-role tidak terduplikasi.
     */
    public function dashboardRouteName(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'dashboard';
    }

    /**
     * Override notifikasi verifikasi bawaan Laravel supaya terkirim lewat
     * Mailable branded (App\Mail\VerifyEmailMail) alih-alih notifikasi
     * default - tetap pakai signed URL bawaan framework, hanya tampilannya
     * yang diganti. Dikirim lewat queue (lihat VerifyEmailMail::envelope /
     * ShouldQueue) sesuai requirement "semua email di-queue".
     */
    public function sendEmailVerificationNotification(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())],
        );

        Mail::to($this->getEmailForVerification())->queue(new VerifyEmailMail($this, $url));
    }

    /**
     * Sama seperti sendEmailVerificationNotification() di atas, tapi untuk
     * link reset password (App\Mail\ResetPasswordMail).
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', ['token' => $token, 'email' => $this->getEmailForPasswordReset()], false));

        Mail::to($this->getEmailForPasswordReset())->queue(new ResetPasswordMail($this, $url));
    }
}
