<?php

namespace Tests\Feature\Admin\Management;

use App\Models\KategoriMateri;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PapanImportExportTest extends TestCase
{
    use RefreshDatabase;

    private function samplePetakArray(int $jumlahPetak, bool $includeMysteryTile = true): array
    {
        $rows = [];

        for ($posisi = 1; $posisi <= $jumlahPetak; $posisi++) {
            $jenis = match (true) {
                $posisi === 1 => 'start',
                $posisi === $jumlahPetak => 'finish',
                $posisi === 5 && $includeMysteryTile => 'mystery',
                default => 'biasa',
            };

            $rows[] = [
                'posisi' => $posisi,
                'jenis_petak' => $jenis,
                'label' => null,
                'icon' => null,
                'warna' => null,
                'border_warna' => null,
                'deskripsi' => null,
                // kategori_nama tidak lagi dipakai (jenis "soal" sudah dihapus),
                // dipertahankan di array supaya bentuk baris tetap konsisten dgn
                // format export/import lama.
                'kategori_nama' => null,
            ];
        }

        return $rows;
    }

    public function test_json_import_preview_reports_valid_board(): void
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'papan' => [
                'nama' => 'Papan Uji Import',
                'deskripsi' => 'Deskripsi uji',
                'jumlah_petak' => 10,
                'jumlah_kolom' => 5,
                'thumbnail' => null,
                'is_active' => true,
            ],
            'petak' => $this->samplePetakArray(10),
            'konektor' => [
                ['jenis' => 'tangga', 'posisi_awal' => 3, 'posisi_akhir' => 8, 'label' => null, 'icon' => null],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('papan.json', json_encode($data));

        $response = $this->actingAs($admin)->post('/admin/management/papan-permainan/import/preview', [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertSeeText('Papan Uji Import');
        $response->assertSeeText('Konfirmasi Import Papan');
    }

    public function test_json_import_confirm_creates_board_petak_and_konektor(): void
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'papan' => [
                'nama' => 'Papan Uji Import Confirm',
                'deskripsi' => null,
                'jumlah_petak' => 10,
                'jumlah_kolom' => 5,
                'thumbnail' => null,
                'is_active' => true,
            ],
            'petak' => $this->samplePetakArray(10),
            'konektor' => [
                ['jenis' => 'tangga', 'posisi_awal' => 3, 'posisi_akhir' => 8, 'label' => null, 'icon' => null],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('papan.json', json_encode($data));

        $previewResponse = $this->actingAs($admin)->post('/admin/management/papan-permainan/import/preview', [
            'file' => $file,
        ]);

        $token = $previewResponse->viewData('token');
        $extension = $previewResponse->viewData('extension');

        $confirmResponse = $this->actingAs($admin)->post('/admin/management/papan-permainan/import/confirm', [
            'token' => $token,
            'extension' => $extension,
        ]);

        $papan = PapanPermainan::where('nama', 'Papan Uji Import Confirm')->firstOrFail();
        $confirmResponse->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $this->assertSame(10, $papan->petak()->count());
        $this->assertSame(1, $papan->papanKonektor()->count());
        $this->assertSame('start', $papan->petak()->where('posisi', 1)->first()->jenis_petak->value);
        $this->assertSame('finish', $papan->petak()->where('posisi', 10)->first()->jenis_petak->value);
        $this->assertSame('tangga', $papan->petak()->where('posisi', 3)->first()->jenis_petak->value);
        $this->assertSame('mystery', $papan->petak()->where('posisi', 5)->first()->jenis_petak->value);
        $this->assertNull($papan->petak()->where('posisi', 5)->first()->kategori_id);
    }

    public function test_json_import_preview_rejects_the_removed_soal_jenis(): void
    {
        $admin = User::factory()->admin()->create();

        $petak = $this->samplePetakArray(10, includeMysteryTile: false);
        $petak[4]['jenis_petak'] = 'soal';

        $data = [
            'papan' => [
                'nama' => 'Papan Uji Soal Dihapus',
                'deskripsi' => null,
                'jumlah_petak' => 10,
                'jumlah_kolom' => 5,
                'thumbnail' => null,
                'is_active' => true,
            ],
            'petak' => $petak,
            'konektor' => [],
        ];

        $file = UploadedFile::fake()->createWithContent('papan.json', json_encode($data));

        $response = $this->actingAs($admin)->post('/admin/management/papan-permainan/import/preview', [
            'file' => $file,
        ]);

        $response->assertOk();
        $this->assertNotEmpty($response->viewData('petak_errors'));
        $this->assertDatabaseMissing('papan_permainan', ['nama' => 'Papan Uji Soal Dihapus']);
    }

    public function test_json_import_skips_konektor_with_wrong_direction_but_still_imports_board(): void
    {
        $admin = User::factory()->admin()->create();

        $data = [
            'papan' => [
                'nama' => 'Papan Uji Konektor Salah Arah',
                'deskripsi' => null,
                'jumlah_petak' => 10,
                'jumlah_kolom' => 5,
                'thumbnail' => null,
                'is_active' => true,
            ],
            'petak' => $this->samplePetakArray(10, includeMysteryTile: false),
            'konektor' => [
                ['jenis' => 'tangga', 'posisi_awal' => 8, 'posisi_akhir' => 3, 'label' => null, 'icon' => null],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('papan.json', json_encode($data));

        $previewResponse = $this->actingAs($admin)->post('/admin/management/papan-permainan/import/preview', ['file' => $file]);
        $previewResponse->assertOk();
        $this->assertCount(1, $previewResponse->viewData('konektor_invalid'));
        $this->assertCount(0, $previewResponse->viewData('konektor_valid'));

        $token = $previewResponse->viewData('token');
        $extension = $previewResponse->viewData('extension');

        $this->actingAs($admin)->post('/admin/management/papan-permainan/import/confirm', [
            'token' => $token,
            'extension' => $extension,
        ]);

        $papan = PapanPermainan::where('nama', 'Papan Uji Konektor Salah Arah')->firstOrFail();
        $this->assertSame(10, $papan->petak()->count());
        $this->assertSame(0, $papan->papanKonektor()->count());
    }

    public function test_json_export_downloads_full_board(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 10, 'jumlah_kolom' => 5]);
        for ($posisi = 1; $posisi <= 10; $posisi++) {
            Petak::factory()->create([
                'papan_id' => $papan->id,
                'posisi' => $posisi,
                'jenis_petak' => $posisi === 1 ? 'start' : ($posisi === 10 ? 'finish' : 'biasa'),
            ]);
        }

        $response = $this->actingAs($admin)->get("/admin/management/papan-permainan/{$papan->id}/export?format=json");

        $response->assertOk();
        $json = json_decode($response->streamedContent(), true);
        $this->assertSame($papan->nama, $json['papan']['nama']);
        $this->assertCount(10, $json['petak']);
    }

    public function test_petak_xlsx_export_returns_downloadable_file(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 10]);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 1, 'jenis_petak' => 'start']);

        $response = $this->actingAs($admin)->get("/admin/management/papan-permainan/{$papan->id}/petak/export?format=xlsx");

        $response->assertOk();
    }

    public function test_petak_csv_bulk_import_updates_existing_petak(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 10]);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 1, 'jenis_petak' => 'start']);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 10, 'jenis_petak' => 'finish']);
        $target = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 5, 'jenis_petak' => 'biasa']);

        $csv = "papan_id,papan_nama,posisi,jenis_petak,kategori,label,icon,warna,border_warna,deskripsi\n"
            ."{$papan->id},{$papan->nama},5,mystery,,Label Baru,,,,\n";

        $file = UploadedFile::fake()->createWithContent('petak.csv', $csv);

        $previewResponse = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/petak/import/preview", [
            'file' => $file,
        ]);
        $previewResponse->assertOk();
        $this->assertCount(1, $previewResponse->viewData('valid'));

        $token = $previewResponse->viewData('token');
        $extension = $previewResponse->viewData('extension');

        $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/petak/import/confirm", [
            'token' => $token,
            'extension' => $extension,
        ])->assertRedirect(route('admin.management.papan-permainan.petak.index', $papan));

        $target->refresh();
        $this->assertSame('mystery', $target->jenis_petak->value);
        $this->assertNull($target->kategori_id);
        $this->assertSame('Label Baru', $target->label);
    }

    public function test_petak_csv_bulk_import_rejects_the_removed_soal_jenis(): void
    {
        $admin = User::factory()->admin()->create();
        KategoriMateri::factory()->create(['nama' => 'Statistika Dasar']);
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 10]);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 1, 'jenis_petak' => 'start']);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 10, 'jenis_petak' => 'finish']);
        $target = Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 5, 'jenis_petak' => 'biasa']);

        $csv = "papan_id,papan_nama,posisi,jenis_petak,kategori,label,icon,warna,border_warna,deskripsi\n"
            ."{$papan->id},{$papan->nama},5,soal,Statistika Dasar,Label Baru,,,,\n";

        $file = UploadedFile::fake()->createWithContent('petak.csv', $csv);

        $previewResponse = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/petak/import/preview", [
            'file' => $file,
        ]);
        $previewResponse->assertOk();
        $this->assertCount(0, $previewResponse->viewData('valid'));
        $this->assertCount(1, $previewResponse->viewData('invalid'));

        $this->assertSame('biasa', $target->fresh()->jenis_petak->value);
    }

    public function test_petak_csv_import_rejects_start_finish_and_wrong_board_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 10]);
        Petak::factory()->create(['papan_id' => $papan->id, 'posisi' => 1, 'jenis_petak' => 'start']);
        $other = PapanPermainan::factory()->create();

        $csv = "papan_id,papan_nama,posisi,jenis_petak,kategori,label,icon,warna,border_warna,deskripsi\n"
            ."{$papan->id},{$papan->nama},1,biasa,,,,,,\n"
            ."{$other->id},{$other->nama},2,biasa,,,,,,\n";

        $file = UploadedFile::fake()->createWithContent('petak.csv', $csv);

        $previewResponse = $this->actingAs($admin)->post("/admin/management/papan-permainan/{$papan->id}/petak/import/preview", [
            'file' => $file,
        ]);

        $previewResponse->assertOk();
        $this->assertCount(0, $previewResponse->viewData('valid'));
        $this->assertCount(2, $previewResponse->viewData('invalid'));
    }
}
