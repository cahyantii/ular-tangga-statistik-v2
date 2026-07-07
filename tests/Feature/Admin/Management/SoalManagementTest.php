<?php

namespace Tests\Feature\Admin\Management;

use App\Models\KategoriMateri;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_soal(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create();

        $this->actingAs($admin)->post('/admin/management/soal', [
            'kategori_id' => $kategori->id,
            'pertanyaan' => 'Berapa hasil dari 2 + 2?',
            'opsi_a' => '3',
            'opsi_b' => '4',
            'opsi_c' => '5',
            'opsi_d' => '6',
            'kunci_jawaban' => 'B',
            'pembahasan' => '2 + 2 = 4.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.management.soal.index'));

        $this->assertDatabaseHas('soal', ['pertanyaan' => 'Berapa hasil dari 2 + 2?', 'kunci_jawaban' => 'B']);

        $soal = Soal::where('pertanyaan', 'Berapa hasil dari 2 + 2?')->firstOrFail();
        $this->assertSame('4', $soal->opsi_jawaban['B']);
    }

    public function test_updating_soal_with_stale_version_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $soal = Soal::factory()->create();
        $staleVersion = (string) $soal->updated_at->timestamp;

        // Simulasikan admin lain mengubah data ini terlebih dahulu.
        sleep(1);
        $soal->update(['pertanyaan' => 'Sudah diubah admin lain']);

        $response = $this->actingAs($admin)->put("/admin/management/soal/{$soal->id}", [
            '_version' => $staleVersion,
            'kategori_id' => $soal->kategori_id,
            'pertanyaan' => 'Percobaan update basi',
            'opsi_a' => 'a', 'opsi_b' => 'b', 'opsi_c' => 'c', 'opsi_d' => 'd',
            'kunci_jawaban' => 'A',
            'pembahasan' => 'x',
            'is_active' => '1',
        ]);

        $response->assertSessionHasErrors('_version');
        $this->assertDatabaseHas('soal', ['id' => $soal->id, 'pertanyaan' => 'Sudah diubah admin lain']);
    }

    public function test_updating_soal_with_current_version_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $soal = Soal::factory()->create();
        $version = (string) $soal->updated_at->timestamp;

        $response = $this->actingAs($admin)->put("/admin/management/soal/{$soal->id}", [
            '_version' => $version,
            'kategori_id' => $soal->kategori_id,
            'pertanyaan' => 'Pertanyaan yang sudah diperbarui',
            'opsi_a' => 'a', 'opsi_b' => 'b', 'opsi_c' => 'c', 'opsi_d' => 'd',
            'kunci_jawaban' => 'C',
            'pembahasan' => 'pembahasan baru',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.management.soal.index'));
        $this->assertDatabaseHas('soal', ['id' => $soal->id, 'pertanyaan' => 'Pertanyaan yang sudah diperbarui']);
    }

    public function test_admin_can_soft_delete_and_restore_soal(): void
    {
        $admin = User::factory()->admin()->create();
        $soal = Soal::factory()->create();

        $this->actingAs($admin)->delete("/admin/management/soal/{$soal->id}")
            ->assertRedirect(route('admin.management.soal.index'));
        $this->assertSoftDeleted('soal', ['id' => $soal->id]);

        $this->actingAs($admin)->patch("/admin/management/soal/{$soal->id}/restore")
            ->assertRedirect(route('admin.management.soal.index'));
        $this->assertDatabaseHas('soal', ['id' => $soal->id, 'deleted_at' => null]);
    }

    public function test_import_preview_separates_valid_and_invalid_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create(['nama' => 'Statistika Dasar']);

        $csv = "kategori,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban,pembahasan,is_active\n"
            ."Statistika Dasar,Apa itu mean?,Rata-rata,Median,Modus,Range,A,Mean adalah rata-rata,1\n"
            ."Kategori Tidak Ada,Soal tanpa kategori valid,a,b,c,d,A,pembahasan,1\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('soal.csv', $csv);

        $response = $this->actingAs($admin)->post('/admin/management/soal/import/preview', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertSeeText('1'); // ringkasan baris valid tampil di halaman
        $response->assertSeeText('Kategori Tidak Ada');
    }

    public function test_import_confirm_creates_only_valid_rows(): void
    {
        $admin = User::factory()->admin()->create();
        KategoriMateri::factory()->create(['nama' => 'Statistika Dasar']);

        $csv = "kategori,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban,pembahasan,is_active\n"
            ."Statistika Dasar,Apa itu mean?,Rata-rata,Median,Modus,Range,A,Mean adalah rata-rata,1\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('soal.csv', $csv);

        $previewResponse = $this->actingAs($admin)->post('/admin/management/soal/import/preview', [
            'file' => $file,
        ]);

        $token = $previewResponse->viewData('token');
        $extension = $previewResponse->viewData('extension');

        $confirmResponse = $this->actingAs($admin)->post('/admin/management/soal/import/confirm', [
            'token' => $token,
            'extension' => $extension,
        ]);

        $confirmResponse->assertRedirect(route('admin.management.soal.index'));
        $this->assertDatabaseHas('soal', ['pertanyaan' => 'Apa itu mean?']);
    }

    public function test_export_returns_downloadable_file(): void
    {
        $admin = User::factory()->admin()->create();
        Soal::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/management/soal/export?format=xlsx');

        $response->assertOk();
    }
}
