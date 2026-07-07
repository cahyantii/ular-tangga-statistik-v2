<?php

namespace Tests\Feature\Public;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_verification_code_shows_the_certificate_details(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $certificate = Certificate::factory()->create([
            'user_id' => $user->id,
            'verification_code' => 'ABCDEF1234567890',
        ]);

        $response = $this->get(route('certificate.verify', 'ABCDEF1234567890'));

        $response->assertOk();
        $response->assertSee('Sertifikat Terverifikasi');
        $response->assertSee('Budi Santoso');
        $response->assertSee($certificate->nomor_sertifikat);
    }

    public function test_verification_is_case_insensitive(): void
    {
        Certificate::factory()->create(['verification_code' => 'ABCDEF1234567890']);

        $response = $this->get(route('certificate.verify', 'abcdef1234567890'));

        $response->assertOk();
        $response->assertSee('Sertifikat Terverifikasi');
    }

    public function test_an_unknown_verification_code_shows_not_found(): void
    {
        $response = $this->get(route('certificate.verify', 'TIDAKADA00000000'));

        $response->assertOk();
        $response->assertSee('Tidak Ditemukan');
    }

    public function test_verification_page_does_not_require_authentication(): void
    {
        $certificate = Certificate::factory()->create();

        $response = $this->get(route('certificate.verify', $certificate->verification_code));

        $response->assertOk();
    }
}
