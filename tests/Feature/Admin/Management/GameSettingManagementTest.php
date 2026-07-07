<?php

namespace Tests\Feature\Admin\Management;

use App\Enums\SettingType;
use App\Models\GameSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSettingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_update_changed_settings_and_audit_log_is_written(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = GameSetting::factory()->create(['value' => '10', 'type' => SettingType::Integer]);
        $version = (string) $setting->updated_at->timestamp;

        $response = $this->actingAs($admin)->put('/admin/management/game-settings', [
            'settings' => [
                $setting->id => ['value' => '25', '_version' => $version],
            ],
        ]);

        $response->assertRedirect(route('admin.management.game-settings.index'));
        $this->assertDatabaseHas('game_settings', ['id' => $setting->id, 'value' => '25']);
        $this->assertDatabaseHas('game_setting_logs', [
            'game_setting_id' => $setting->id,
            'changed_by' => $admin->id,
            'old_value' => '10',
            'new_value' => '25',
        ]);
    }

    public function test_unchanged_settings_do_not_create_audit_log_entries(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = GameSetting::factory()->create(['value' => '10']);
        $version = (string) $setting->updated_at->timestamp;

        $this->actingAs($admin)->put('/admin/management/game-settings', [
            'settings' => [
                $setting->id => ['value' => '10', '_version' => $version],
            ],
        ]);

        $this->assertDatabaseMissing('game_setting_logs', ['game_setting_id' => $setting->id]);
    }

    public function test_stale_version_rejects_the_entire_bulk_update(): void
    {
        $admin = User::factory()->admin()->create();
        $settingA = GameSetting::factory()->create(['value' => '10']);
        $settingB = GameSetting::factory()->create(['value' => '20']);
        $staleVersion = (string) $settingA->updated_at->timestamp;

        sleep(1);
        $settingA->update(['value' => '99']); // diubah admin lain

        $response = $this->actingAs($admin)->put('/admin/management/game-settings', [
            'settings' => [
                $settingA->id => ['value' => '30', '_version' => $staleVersion],
                $settingB->id => ['value' => '40', '_version' => (string) $settingB->updated_at->timestamp],
            ],
        ]);

        $response->assertSessionHasErrors("settings.{$settingA->id}.value");
        // Karena satu transaksi, perubahan settingB yang valid pun tidak ikut tersimpan.
        $this->assertDatabaseHas('game_settings', ['id' => $settingB->id, 'value' => '20']);
    }

    public function test_boolean_setting_can_be_toggled_off(): void
    {
        $admin = User::factory()->admin()->create();
        $setting = GameSetting::factory()->create(['value' => '1', 'type' => SettingType::Boolean]);
        $version = (string) $setting->updated_at->timestamp;

        $this->actingAs($admin)->put('/admin/management/game-settings', [
            'settings' => [
                $setting->id => ['value' => '0', '_version' => $version],
            ],
        ])->assertRedirect(route('admin.management.game-settings.index'));

        $this->assertDatabaseHas('game_settings', ['id' => $setting->id, 'value' => '0']);
    }
}
