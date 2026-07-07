<?php

namespace Tests\Feature\Admin\Management;

use App\Models\KategoriMateri;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_materi(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();

        $this->actingAs($admin)->post('/admin/management/materi', [
            'kategori_id' => $kategori->id,
            'judul' => 'Pengenalan Mean',
            'konten' => 'Mean adalah rata-rata hitung dari sekumpulan data.',
            'urutan' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.materi.index'));

        $this->assertDatabaseHas('materi', ['judul' => 'Pengenalan Mean', 'kategori_id' => $kategori->id]);
    }

    public function test_admin_can_soft_delete_and_restore_materi(): void
    {
        $admin = User::factory()->admin()->create();
        $materi = Materi::factory()->create();

        $this->actingAs($admin)->delete("/admin/management/materi/{$materi->id}")
            ->assertRedirect(route('admin.management.materi.index'));
        $this->assertSoftDeleted('materi', ['id' => $materi->id]);

        $this->actingAs($admin)->patch("/admin/management/materi/{$materi->id}/restore")
            ->assertRedirect(route('admin.management.materi.index'));
        $this->assertDatabaseHas('materi', ['id' => $materi->id, 'deleted_at' => null]);
    }

    public function test_materi_index_can_be_filtered_by_kategori(): void
    {
        $admin = User::factory()->admin()->create();
        $kategoriA = KategoriMateri::factory()->create(['nama' => 'Kategori A']);
        $kategoriB = KategoriMateri::factory()->create(['nama' => 'Kategori B']);
        Materi::factory()->create(['kategori_id' => $kategoriA->id, 'judul' => 'Materi A1']);
        Materi::factory()->create(['kategori_id' => $kategoriB->id, 'judul' => 'Materi B1']);

        $response = $this->actingAs($admin)->get("/admin/management/materi?kategori_id={$kategoriA->id}");

        $response->assertOk();
        $response->assertSeeText('Materi A1');
        $response->assertDontSeeText('Materi B1');
    }
}
