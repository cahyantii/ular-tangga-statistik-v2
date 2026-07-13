<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Resize & center-crop otomatis ke persegi (Tahap Profil) — dijalankan di
 * server dengan GD, bukan cropper interaktif di klien, supaya tidak perlu
 * menambah dependency JS baru pada project ini.
 */
class AvatarUploadService
{
    private const SIZE = 320;

    private const JPEG_QUALITY = 85;

    public function store(UploadedFile $file, User $user): string
    {
        $this->forgetUploaded($user);

        $imageInfo = getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            throw ValidationException::withMessages(['avatar' => 'File yang diunggah bukan gambar yang valid.']);
        }

        [$width, $height, $type] = $imageInfo;

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => imagecreatefromwebp($file->getRealPath()),
            default => throw ValidationException::withMessages(['avatar' => 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.']),
        };

        $cropSize = min($width, $height);
        $srcX = intdiv($width - $cropSize, 2);
        $srcY = intdiv($height - $cropSize, 2);

        $target = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));
        imagecopyresampled($target, $source, 0, 0, $srcX, $srcY, self::SIZE, self::SIZE, $cropSize, $cropSize);

        ob_start();
        imagejpeg($target, null, self::JPEG_QUALITY);
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        $path = 'avatars/'.$user->id.'/'.Str::uuid()->toString().'.jpg';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    public function forgetUploaded(User $user): void
    {
        if ($user->avatar && ! in_array($user->avatar, User::PRESET_AVATARS, true)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
