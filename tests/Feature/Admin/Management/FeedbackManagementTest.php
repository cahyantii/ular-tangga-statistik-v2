<?php

namespace Tests\Feature\Admin\Management;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_feedback_list(): void
    {
        $admin = User::factory()->admin()->create();
        Feedback::factory()->create(['subject' => 'Bug di papan permainan']);

        $this->actingAs($admin)->get('/admin/management/feedback')
            ->assertOk()
            ->assertSee('Bug di papan permainan');
    }

    public function test_admin_can_update_feedback_status(): void
    {
        $admin = User::factory()->admin()->create();
        $feedback = Feedback::factory()->create(['status' => 'baru']);

        $this->actingAs($admin)
            ->patch("/admin/management/feedback/{$feedback->id}/status", ['status' => 'selesai'])
            ->assertRedirect(route('admin.management.feedback.index'));

        $this->assertDatabaseHas('feedbacks', ['id' => $feedback->id, 'status' => 'selesai']);
    }

    public function test_admin_can_delete_feedback(): void
    {
        $admin = User::factory()->admin()->create();
        $feedback = Feedback::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/management/feedback/{$feedback->id}")
            ->assertRedirect(route('admin.management.feedback.index'));

        $this->assertDatabaseMissing('feedbacks', ['id' => $feedback->id]);
    }

    public function test_player_cannot_access_admin_feedback_page(): void
    {
        $player = User::factory()->create();

        $this->actingAs($player)->get('/admin/management/feedback')->assertForbidden();
    }
}
