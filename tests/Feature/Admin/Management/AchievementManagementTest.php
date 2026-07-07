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
