<?php

namespace Tests\Feature\Admin\Management;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_achievement(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/management/achievements', [
            'kode' => 'FIRST_WIN',
            'nama' => 'Kemenangan Pertama',
            'syarat_type' => 'total_menang',
            'syarat_value' => 1,
            'urutan' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.achievements.index'));

        $this->assertDatabaseHas('achievements', ['kode' => 'FIRST_WIN', 'nama' => 'Kemenangan Pertama']);
    }

    public function test_admin_can_set_reward_poin_on_create_and_update(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/management/achievements', [
            'kode' => 'REWARD_TEST',
            'nama' => 'Uji Reward',
            'syarat_type' => 'total_menang',
            'syarat_value' => 1,
            'reward_poin' => 75,
            'urutan' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.achievements.index'));

        $achievement = Achievement::where('kode', 'REWARD_TEST')->firstOrFail();
        $this->assertSame(75, $achievement->reward_poin);

        $this->actingAs($admin)->put("/admin/management/achievements/{$achievement->id}", [
            'kode' => 'REWARD_TEST',
            'nama' => 'Uji Reward',
            'syarat_type' => 'total_menang',
            'syarat_value' => 1,
            'reward_poin' => 150,
            'urutan' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.achievements.index'));

        $this->assertSame(150, $achievement->fresh()->reward_poin);
    }

    public function test_kode_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Achievement::factory()->create(['kode' => 'FIRST_WIN']);

        $response = $this->actingAs($admin)->post('/admin/management/achievements', [
            'kode' => 'FIRST_WIN',
            'nama' => 'Duplikat',
            'syarat_type' => 'total_menang',
            'syarat_value' => 1,
            'urutan' => 1,
        ]);

        $response->assertSessionHasErrors('kode');
    }

    public function test_admin_can_soft_delete_and_restore_achievement(): void
    {
        $admin = User::factory()->admin()->create();
        $achievement = Achievement::factory()->create();

        $this->actingAs($admin)->delete("/admin/management/achievements/{$achievement->id}")
            ->assertRedirect(route('admin.management.achievements.index'));
        $this->assertSoftDeleted('achievements', ['id' => $achievement->id]);

        $this->actingAs($admin)->patch("/admin/management/achievements/{$achievement->id}/restore")
            ->assertRedirect(route('admin.management.achievements.index'));
        $this->assertDatabaseHas('achievements', ['id' => $achievement->id, 'deleted_at' => null]);
    }
}
