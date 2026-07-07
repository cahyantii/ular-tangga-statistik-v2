<?php

namespace Tests\Feature\Player;

use App\Models\Certificate;
use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_checklist_when_not_yet_eligible(): void
    {
        $user = User::factory()->create();
        KategoriMateri::factory()->create();

        $response = $this->actingAs($user)->get(route('player.certificate'));

        $response->assertOk();
        $response->assertSee('Semua kategori materi selesai');
        $response->assertSee('0/1');
        $response->assertDontSee('Unduh PDF');
    }

    public function test_shows_certificate_and_download_link_when_already_issued(): void
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('player.certificate'));

        $response->assertOk();
        $response->assertSee($certificate->nomor_sertifikat);
        $response->assertSee('Unduh PDF');
        $response->assertSee(route('player.certificate.download', $certificate), false);
    }

    public function test_owner_can_download_their_certificate(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $certificate = Certificate::factory()->create([
            'user_id' => $user->id,
            'file_path' => 'certificates/test.pdf',
        ]);
        Storage::disk('local')->put('certificates/test.pdf', '%PDF-1.4 fake content');

        $response = $this->actingAs($user)->get(route('player.certificate.download', $certificate));

        $response->assertOk();
    }

    public function test_a_different_user_cannot_download_someone_elses_certificate(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $certificate = Certificate::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($outsider)->get(route('player.certificate.download', $certificate));

        $response->assertStatus(403);
    }
}
