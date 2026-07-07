<?php

namespace App\Repositories\Game;

use App\Models\GameSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Sumber tunggal nilai konfigurasi permainan (Tahap 2/5, keputusan final:
 * "no hardcoding", semua aturan skor/timer harus berasal dari `game_settings`).
 * Di-cache tanpa batas waktu, dilupakan (`forget()`) oleh
 * App\Services\Master\GameSettingService::bulkUpdate() setiap kali admin
 * menyimpan perubahan.
 */
class GameSettingsRepository
{
    public const CACHE_KEY = 'game_settings.map';

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => GameSetting::query()->pluck('value', 'key')->all());
    }

    public function getInt(string $key): int
    {
        return (int) ($this->all()[$key] ?? 0);
    }

    public function getString(string $key): string
    {
        return (string) ($this->all()[$key] ?? '');
    }

    public function getBool(string $key): bool
    {
        return ($this->all()[$key] ?? '0') === '1';
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
