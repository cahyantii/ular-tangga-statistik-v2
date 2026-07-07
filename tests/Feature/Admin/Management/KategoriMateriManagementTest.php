<?php

namespace Tests\Feature\Admin\Management;

use App\Models\KategoriMateri;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriMateriManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_kategori_with_auto_generated_slug(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/management/kategori-materi', [
            'nama' => 'Statistika Lanjutan',
            'urutan' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.kategori-materi.index'));

        $this->assertDatabaseHas('kategori_materi', ['nama' => 'Statistika Lanjutan', 'slug' => 'statistika-lanjutan']);
    }

    public function test_deleting_kategori_with_active_soal_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();
        Soal::factory()->create(['kategori_id' => $kategori->id]);

        $response = $this->actingAs($admin)->delete("/admin/management/kategori-materi/{$kategori->id}");

        $response->assertSessionHasErrors('kategori');
        $this->assertDatabaseHas('kategori_materi', ['id' => $kategori->id, 'deleted_at' => null]);
    }

    public function test_deleting_kategori_with_active_materi_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();
        Materi::factory()->create(['kategori_id' => $kategori->id]);

        $response = $this->actingAs($admin)->delete("/admin/management/kategori-materi/{$kategori->id}");

        $response->assertSessionHasErrors('kategori');
        $this->assertDatabaseHas('kategori_materi', ['id' => $kategori->id, 'deleted_at' => null]);
    }

    public function test_admin_can_delete_kategori_without_dependents(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();

        $this->actingAs($admin)->delete("/admin/management/kategori-materi/{$kategori->id}")
            ->assertRedirect(route('admin.management.kategori-materi.index'));

        $this->assertSoftDeleted('kategori_materi', ['id' => $kategori->id]);
    }
}
