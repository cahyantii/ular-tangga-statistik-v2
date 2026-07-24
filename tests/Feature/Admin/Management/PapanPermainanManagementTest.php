<?php

namespace Tests\Feature\Admin\Management;

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PapanPermainanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_papan_auto_generates_petak_rows(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/management/papan-permainan', [
            'nama' => 'Papan Uji Coba',
            'jumlah_petak' => 20,
            'jumlah_kolom' => 5,
            'is_active' => '1',
        ]);

        $papan = PapanPermainan::where('nama', 'Papan Uji Coba')->firstOrFail();
        $response->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $this->assertSame(20, $papan->petak()->count());
        $this->assertSame('start', $papan->petak()->where('posisi', 1)->first()->jenis_petak->value);
        $this->assertSame('finish', $papan->petak()->where('posisi', 20)->first()->jenis_petak->value);
        $this->assertSame('biasa', $papan->petak()->where('posisi', 10)->first()->jenis_petak->value);
    }

    public function test_updating_papan_cannot_change_jumlah_petak(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $version = (string) $papan->updated_at->timestamp;

        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}", [
            '_version' => $version,
            'nama' => 'Nama Baru',
            'jumlah_petak' => 100, // dicoba dikirim, harus diabaikan
            'jumlah_kolom' => 10,
        ])->assertRedirect(route('admin.management.papan-permainan.index'));

        $papan->refresh();
        $this->assertSame(50, $papan->jumlah_petak);
        $this->assertSame('Nama Baru', $papan->nama);
    }

    public function test_deleting_papan_in_active_use_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create();
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $response = $this->actingAs($admin)->delete("/admin/management/papan-permainan/{$papan->id}");

        $response->assertSessionHasErrors('papan');
        $this->assertDatabaseHas('papan_permainan', ['id' => $papan->id, 'deleted_at' => null]);
    }

    public function test_deactivating_last_active_board_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch("/admin/management/papan-permainan/{$papan->id}/toggle-active");

        $response->assertSessionHasErrors('papan');
        $this->assertDatabaseHas('papan_permainan', ['id' => $papan->id, 'is_active' => 1]);
    }

    public function test_can_deactivate_board_when_another_active_board_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['is_active' => true]);
        PapanPermainan::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->patch("/admin/management/papan-permainan/{$papan->id}/toggle-active")
            ->assertRedirect(route('admin.management.papan-permainan.index'));

        $this->assertDatabaseHas('papan_permainan', ['id' => $papan->id, 'is_active' => 0]);
    }

    public function test_start_and_finish_petak_cannot_be_edited(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 20]);
        $start = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 1, 'jenis_petak' => 'start']);

        $this->actingAs($admin)->get("/admin/management/papan-permainan/{$papan->id}/petak/{$start->id}/edit")
            ->assertForbidden();
    }

    public function test_admin_can_set_a_biasa_petak_to_soal_with_kategori(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 20]);
        $petak = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 5, 'jenis_petak' => 'biasa']);
        $version = (string) $petak->updated_at->timestamp;

        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}/petak/{$petak->id}", [
            '_version' => $version,
            'jenis_petak' => 'soal',
            'kategori_id' => $kategori->id,
        ])->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $petak->refresh();
        $this->assertSame('soal', $petak->jenis_petak->value);
        $this->assertSame($kategori->id, $petak->kategori_id);
    }

    public function test_konektor_index_renders_with_existing_connectors_and_their_jenis(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        PapanKonektor::factory()->create([
            'papan_id' => $papan->id,
            'jenis' => 'tangga',
            'posisi_awal' => 6,
            'posisi_akhir' => 20,
        ]);
        PapanKonektor::factory()->create([
            'papan_id' => $papan->id,
            'jenis' => 'ular',
            'posisi_awal' => 17,
            'posisi_akhir' => 4,
        ]);

        $response = $this->actingAs($admin)->get("/admin/management/papan-permainan/{$papan->id}/konektor");

        $response->assertOk();
        // Editor visual (konektor/index.blade.php) menggambar konektor yang
        // sudah ada sebagai overlay pudar di SVG (lihat renderExistingConnectors()
        // di JS-nya) - butuh field "jenis" per konektor di JSON board data
        // supaya tahu mana yang harus digambar sebagai tangga vs ular.
        $response->assertSee('&quot;jenis&quot;:&quot;tangga&quot;', false);
        $response->assertSee('&quot;jenis&quot;:&quot;ular&quot;', false);
    }

    public function test_konektor_cannot_chain_into_another_konektors_start(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        PapanKonektor::factory()->create(['papan_id' => $papan->id, 'posisi_awal' => 30, 'posisi_akhir' => 45]);

        $response = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'tangga',
            'posisi_awal' => 10,
            'posisi_akhir' => 30, // sama dengan posisi_awal konektor lain -> chaining, harus ditolak
        ]);

        $response->assertSessionHasErrors('konektor');
        $this->assertDatabaseMissing('papan_konektor', ['papan_id' => $papan->id, 'posisi_awal' => 10]);
    }

    public function test_creating_konektor_syncs_petak_jenis_and_deleting_resets_it(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 8, 'jenis_petak' => 'biasa']);

        $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'tangga',
            'posisi_awal' => 8,
            'posisi_akhir' => 22,
        ])->assertRedirect(route('admin.management.papan-permainan.konektor.index', $papan));

        $petak = Petak::where('papan_id', $papan->id)->where('posisi', 8)->firstOrFail();
        $this->assertSame('tangga', $petak->jenis_petak->value);

        $konektor = PapanKonektor::where('papan_id', $papan->id)->where('posisi_awal', 8)->firstOrFail();
        $this->actingAs($admin)->delete("/admin/management/papan-permainan/{$papan->id}/konektor/{$konektor->id}")
            ->assertRedirect(route('admin.management.papan-permainan.konektor.index', $papan));

        $petak->refresh();
        $this->assertSame('biasa', $petak->jenis_petak->value);
    }

    public function test_tangga_going_down_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);

        $response = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'tangga',
            'posisi_awal' => 30,
            'posisi_akhir' => 10,
        ]);

        $response->assertSessionHasErrors('konektor');
        $this->assertDatabaseMissing('papan_konektor', ['papan_id' => $papan->id, 'posisi_awal' => 30]);
    }

    public function test_ular_going_up_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);

        $response = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'ular',
            'posisi_awal' => 10,
            'posisi_akhir' => 30,
        ]);

        $response->assertSessionHasErrors('konektor');
        $this->assertDatabaseMissing('papan_konektor', ['papan_id' => $papan->id, 'posisi_awal' => 10]);
    }

    public function test_tangga_going_up_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);

        $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'tangga',
            'posisi_awal' => 10,
            'posisi_akhir' => 30,
        ])->assertRedirect(route('admin.management.papan-permainan.konektor.index', $papan));

        $this->assertDatabaseHas('papan_konektor', ['papan_id' => $papan->id, 'posisi_awal' => 10, 'posisi_akhir' => 30]);
    }

    public function test_ular_going_down_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);

        $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/konektor", [
            'jenis' => 'ular',
            'posisi_awal' => 30,
            'posisi_akhir' => 10,
        ])->assertRedirect(route('admin.management.papan-permainan.konektor.index', $papan));

        $this->assertDatabaseHas('papan_konektor', ['papan_id' => $papan->id, 'posisi_awal' => 30, 'posisi_akhir' => 10]);
    }

    public function test_papan_deskripsi_persists_on_create_and_update(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/management/papan-permainan', [
            'nama' => 'Papan Deskripsi',
            'deskripsi' => 'Deskripsi awal papan.',
            'jumlah_petak' => 20,
            'jumlah_kolom' => 5,
            'is_active' => '1',
        ]);

        $papan = PapanPermainan::where('nama', 'Papan Deskripsi')->firstOrFail();
        $this->assertSame('Deskripsi awal papan.', $papan->deskripsi);

        $version = (string) $papan->updated_at->timestamp;
        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}", [
            '_version' => $version,
            'nama' => $papan->nama,
            'deskripsi' => 'Deskripsi setelah diperbarui.',
            'jumlah_kolom' => $papan->jumlah_kolom,
        ])->assertRedirect(route('admin.management.papan-permainan.index'));

        $this->assertSame('Deskripsi setelah diperbarui.', $papan->fresh()->deskripsi);
    }

    public function test_petak_border_warna_persists_on_update(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 20]);
        $petak = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 5, 'jenis_petak' => 'biasa']);
        $version = (string) $petak->updated_at->timestamp;

        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}/petak/{$petak->id}", [
            '_version' => $version,
            'jenis_petak' => 'biasa',
            'border_warna' => '3px solid #f59e0b',
        ])->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $this->assertSame('3px solid #f59e0b', $petak->fresh()->border_warna);
    }

    public function test_petak_can_be_toggled_inactive_and_active_again(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 20]);
        $petak = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 5, 'jenis_petak' => 'bonus', 'is_active' => true]);
        $version = (string) $petak->updated_at->timestamp;

        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}/petak/{$petak->id}", [
            '_version' => $version,
            'jenis_petak' => 'bonus',
            'is_active' => '0',
        ])->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $petak->refresh();
        $this->assertFalse($petak->is_active);
        $this->assertSame('bonus', $petak->jenis_petak->value, 'jenis_petak tetap tersimpan meski nonaktif');

        $version = (string) $petak->updated_at->timestamp;
        $this->actingAs($admin)->put("/admin/management/papan-permainan/{$papan->id}/petak/{$petak->id}", [
            '_version' => $version,
            'jenis_petak' => 'bonus',
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $this->assertTrue($petak->fresh()->is_active);
    }

    public function test_preview_page_renders(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 20]);
        for ($posisi = 1; $posisi <= 20; $posisi++) {
            Petak::factory()->create([
                'papan_id' => $papan->id,
                'posisi' => $posisi,
                'jenis_petak' => $posisi === 1 ? 'start' : ($posisi === 20 ? 'finish' : 'biasa'),
            ]);
        }

        $this->actingAs($admin)->get("/admin/management/papan-permainan/{$papan->id}/preview")->assertOk();
    }
}
