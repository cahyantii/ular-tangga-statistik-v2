<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'jenis_sertifikat' => 'penguasaan_statistik_dasar',
            'judul' => 'Sertifikat Penguasaan Statistik Dasar',
            'nomor_sertifikat' => 'CERT-' . fake()->unique()->numerify('########'),
            'verification_code' => strtoupper(Str::random(16)),
            'template_path' => 'certificates.template',
            'file_path' => 'certificates/' . Str::random(20) . '.pdf',
            'issued_at' => now(),
        ];
    }
}
