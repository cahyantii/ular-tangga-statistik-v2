<?php

namespace App\Services\Master;

use App\Models\GameSetting;
use App\Models\GameSettingLog;
use App\Models\User;
use App\Repositories\Game\GameSettingsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-update Game Settings (Tahap 16, keputusan final): satu form berisi
 * banyak baris, satu transaksi untuk semuanya, dengan audit log per baris
 * yang nilainya benar-benar berubah, dan optimistic locking per baris
 * (`_version` = updated_at, satu per baris karena ini form multi-baris).
 */
class GameSettingService
{
    /**
     * @param  array<int, array{value: string, _version: string}>  $rows  keyed by game_setting id
     */
    public function bulkUpdate(array $rows, User $admin): int
    {
        return DB::transaction(function () use ($rows, $admin) {
            $settings = GameSetting::query()->whereIn('id', array_keys($rows))->lockForUpdate()->get()->keyBy('id');

            foreach ($rows as $id => $row) {
                $setting = $settings->get((int) $id);

                if (! $setting) {
                    continue;
                }

                $currentVersion = (string) $setting->updated_at->timestamp;

                if ($row['_version'] !== $currentVersion) {
                    throw ValidationException::withMessages([
                        "settings.{$id}.value" => "Pengaturan \"{$setting->label}\" sudah diubah oleh admin lain sejak Anda membuka halaman ini. Silakan muat ulang dan coba lagi.",
                    ]);
                }
            }

            $totalDiubah = 0;

            foreach ($rows as $id => $row) {
                $setting = $settings->get((int) $id);

                if (! $setting) {
                    continue;
                }

                $newValue = (string) $row['value'];

                if ($newValue === $setting->value) {
                    continue;
                }

                GameSettingLog::create([
                    'game_setting_id' => $setting->id,
                    'changed_by' => $admin->id,
                    'old_value' => $setting->value,
                    'new_value' => $newValue,
                    'created_at' => now(),
                ]);

                $setting->update(['value' => $newValue]);
                $totalDiubah++;
            }

            if ($totalDiubah > 0) {
                GameSettingsRepository::forget();
            }

            return $totalDiubah;
        });
    }
}
